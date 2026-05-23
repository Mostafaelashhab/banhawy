<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Order;
use App\Models\Product;
use App\Models\WhatsappClick;
use App\Services\PushSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function summary(Request $request, Business $business)
    {
        $cart = $this->parseCart($request, $business);
        $subtotal = collect($cart)->sum('line_total');
        $delivery = $business->delivery ? 15 : 0;

        return view('public.business.order-summary', [
            'business' => $business,
            'cart'     => $cart,
            'subtotal' => $subtotal,
            'delivery' => $delivery,
            'total'    => $subtotal + $delivery,
        ]);
    }

    public function place(Request $request, Business $business)
    {
        $data = $request->validate([
            'customer_name'  => 'required|string|max:120',
            'customer_phone' => 'required|string|max:20',
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|integer|exists:products,id',
            'items.*.qty'    => 'required|integer|min:1|max:50',
            'notes'          => 'nullable|string|max:500',
            'channel'        => 'nullable|in:web,whatsapp', // for owners offering 'both', the customer picks
        ]);

        $products = Product::whereIn('id', collect($data['items'])->pluck('id'))->get()->keyBy('id');
        $lines = collect($data['items'])->map(function ($i) use ($products) {
            $p = $products[$i['id']];
            return [
                'product_id' => $p->id,
                'name'       => $p->name,
                'qty'        => (int) $i['qty'],
                'price'      => (int) $p->price,
                'line_total' => $p->price * $i['qty'],
            ];
        })->all();

        $subtotal = collect($lines)->sum('line_total');
        $delivery = $business->delivery ? 15 : 0;

        // Always persist the order — owner sees every order in the dashboard
        // regardless of delivery channel, for analytics + history.
        $order = Order::create([
            'business_id'    => $business->id,
            'user_id'        => auth()->id(), // null for guests — they still track via the code
            'customer_name'  => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'subtotal'       => $subtotal,
            'delivery_fee'   => $delivery,
            'total'          => $subtotal + $delivery,
            'status'         => 'new',
            'items'          => $lines,
            'notes'          => $data['notes'] ?? null,
            'placed_at'      => now(),
        ]);

        // Fire-and-forget push to the business owner about the new order
        $this->notifyOwnerOfOrder($business, $order);

        // Decide channel based on business setting + customer choice
        $channel = $this->resolveChannel($business, $data['channel'] ?? null);

        if ($channel === 'whatsapp') {
            WhatsappClick::create(['business_id' => $business->id, 'source' => 'order']);
            $message = $this->buildOrderMessage($business, $order);
            return redirect()->away($business->whatsappLink($message));
        }

        // Web channel: show success page in-app
        return redirect()->route('business.order.success', [
            'business' => $business,
            'order'    => $order->id,
        ]);
    }

    public function success(Business $business, Order $order)
    {
        abort_unless($order->business_id === $business->id, 404);
        return view('public.business.order-success', compact('business', 'order'));
    }

    private function resolveChannel(Business $business, ?string $requested): string
    {
        // 'whatsapp' setting → always WhatsApp
        if ($business->orders_via === 'whatsapp') return 'whatsapp';
        // 'web' setting → always web
        if ($business->orders_via === 'web')      return 'web';
        // 'both' → respect customer's choice, default to whatsapp
        return $requested === 'web' ? 'web' : 'whatsapp';
    }

    private function parseCart(Request $request, Business $business): array
    {
        $raw = (array) $request->get('items', []);

        // Filter out zero/negative quantities — the menu form posts every
        // product with value="0" by default, so without this every item
        // would be included in the order.
        $raw = array_filter($raw, fn ($qty) => (int) $qty > 0);

        $products = Product::whereIn('id', array_keys($raw))
            ->where('business_id', $business->id) // can't smuggle in items from another shop
            ->get()
            ->keyBy('id');

        return collect($raw)->map(function ($qty, $id) use ($products) {
            $p = $products[$id] ?? null;
            if (! $p) return null;
            $qty = (int) $qty;
            return [
                'product_id' => $p->id,
                'name'       => $p->name,
                'qty'        => $qty,
                'price'      => $p->price,
                'line_total' => $p->price * $qty,
            ];
        })->filter()->values()->all();
    }

    private function buildOrderMessage(Business $b, Order $o): string
    {
        $lines    = collect($o->items)->map(fn ($it) => "• {$it['name']} × {$it['qty']} — {$it['line_total']}ج")->implode("\n");
        $trackUrl = route('track', ['code' => $o->code]);
        return "طلب جديد من بنهاوي · {$b->name}\n\n"
             . "العميل: {$o->customer_name}\nالموبايل: {$o->customer_phone}\n\n"
             . "الأصناف:\n{$lines}\n\n"
             . "الإجمالي: {$o->total}ج\n"
             . "رقم الطلب: {$o->code}\n"
             . "تتبع الطلب: {$trackUrl}";
    }

    private function notifyOwnerOfOrder(Business $business, Order $order): void
    {
        $owner = $business->owner;
        if (! $owner || $owner->pushSubscriptions()->doesntExist()) {
            return; // no subscribed devices — skip silently
        }

        $itemCount = collect($order->items)->sum('qty');

        try {
            app(PushSender::class)->toUser($owner, [
                'title' => "طلب جديد · {$business->name}",
                'body'  => "{$order->code} · {$order->customer_name} · {$itemCount} أصناف · {$order->total}ج",
                'url'   => route('merchant.orders.index'),
                'tag'   => "order-{$order->code}",
            ]);
        } catch (\Throwable $e) {
            // Don't let a push failure break the order flow
            Log::warning('[Order push] failed', ['order_id' => $order->id, 'err' => $e->getMessage()]);
        }
    }
}
