<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\PaymentReceipt;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UpgradeController extends Controller
{
    public function show(Request $request): View
    {
        $business = $this->ownedBusiness();

        $planSlug = $request->input('plan', 'pro');
        $cycle    = in_array($request->input('cycle'), ['monthly', 'yearly'], true)
            ? $request->input('cycle')
            : 'monthly';

        $plan = Plan::where('slug', $planSlug)->whereIn('slug', ['pro', 'business'])->firstOrFail();
        $amount = $this->amountFor($plan, $cycle);

        $pending = PaymentReceipt::where('business_id', $business->id)
            ->where('status', PaymentReceipt::STATUS_PENDING)
            ->latest()
            ->first();

        $history = PaymentReceipt::where('business_id', $business->id)
            ->with('plan')
            ->latest()
            ->limit(5)
            ->get();

        return view('merchant.upgrade', compact('business', 'plan', 'cycle', 'amount', 'pending', 'history'));
    }

    public function store(Request $request): RedirectResponse
    {
        $business = $this->ownedBusiness();

        $data = $request->validate([
            'plan_slug'        => ['required', 'in:pro,business'],
            'billing_cycle'    => ['required', 'in:monthly,yearly'],
            'method'           => ['required', 'in:instapay,vodafone_cash'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'receipt'          => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ], [], [
            'method'  => 'طريقة الدفع',
            'receipt' => 'صورة الإيصال',
        ]);

        $exists = PaymentReceipt::where('business_id', $business->id)
            ->where('status', PaymentReceipt::STATUS_PENDING)
            ->exists();

        if ($exists) {
            return back()->with('flash_error', 'عندك إيصال قيد المراجعة بالفعل، انتظر الرد قبل إرسال إيصال جديد.');
        }

        $plan   = Plan::where('slug', $data['plan_slug'])->firstOrFail();
        $amount = $this->amountFor($plan, $data['billing_cycle']);

        $file = $request->file('receipt');
        $name = Str::lower(Str::random(24)).'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('payment_receipts/'.$business->id, $name, 'public');

        $receipt = PaymentReceipt::create([
            'business_id'      => $business->id,
            'user_id'          => Auth::id(),
            'plan_id'          => $plan->id,
            'billing_cycle'    => $data['billing_cycle'],
            'amount'           => $amount,
            'method'           => $data['method'],
            'receipt_path'     => $path,
            'reference_number' => $data['reference_number'] ?? null,
        ]);

        try {
            app(\App\Services\PushSender::class)->toAdmins([
                'title' => '💳 إيصال دفع جديد',
                'body'  => $business->name.' بعت إيصال '.$receipt->methodLabel().' بـ '.number_format($amount).' ج لخطة '.$plan->name,
                'url'   => route('admin.receipts.index'),
                'tag'   => 'admin-receipt-'.$receipt->id,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[push admins receipt] '.$e->getMessage());
        }

        return redirect()
            ->route('merchant.upgrade', ['plan' => $plan->slug, 'cycle' => $data['billing_cycle']])
            ->with('flash', 'تم استلام إيصالك ✓ هنراجعه ونفعّل اشتراكك خلال 24 ساعة.');
    }

    private function amountFor(Plan $plan, string $cycle): float
    {
        $monthly = (int) $plan->price_monthly;
        return $cycle === 'yearly'
            ? round($monthly * 12 * 0.80, 2)
            : (float) $monthly;
    }

    private function ownedBusiness(): Business
    {
        $b = Auth::user()->businesses()->latest()->first();
        abort_unless($b, 404, 'لا يوجد نشاط مرتبط بحسابك.');
        return $b;
    }
}
