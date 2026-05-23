<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Business;
use App\Models\WhatsappClick;
use App\Services\PushSender;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function form(Business $business)
    {
        abort_unless(
            $business->acceptsWebBookings() || $business->acceptsWhatsappBookings(),
            404
        );

        return view('public.business.book', [
            'business'  => $business,
            'bookLabel' => $this->bookLabel($business),
        ]);
    }

    public function store(Request $request, Business $business)
    {
        abort_unless(
            $business->acceptsWebBookings() || $business->acceptsWhatsappBookings(),
            404
        );

        $data = $request->validate([
            'customer_name'  => 'required|string|max:120',
            'customer_phone' => 'required|string|max:20',
            'booked_at'      => 'required|date|after:now',
            'party_size'     => 'nullable|integer|min:1|max:50',
            'service'        => 'nullable|string|max:120',
            'notes'          => 'nullable|string|max:500',
            'channel'        => 'nullable|in:web,whatsapp',
        ]);

        $booking = Booking::create([
            'business_id'    => $business->id,
            'customer_name'  => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'service'        => $data['service'] ?? null,
            'booked_at'      => Carbon::parse($data['booked_at']),
            'party_size'     => $data['party_size'] ?? 1,
            'status'         => 'new',
            'notes'          => $data['notes'] ?? null,
        ]);

        $this->notifyOwner($business, $booking);

        $channel = $this->resolveChannel($business, $data['channel'] ?? null);

        if ($channel === 'whatsapp') {
            WhatsappClick::create(['business_id' => $business->id, 'source' => 'booking']);
            return redirect()->away($business->whatsappLink($this->buildMessage($business, $booking)));
        }

        return redirect()->route('business.book.success', [
            'business' => $business,
            'booking'  => $booking->id,
        ]);
    }

    public function success(Business $business, Booking $booking)
    {
        abort_unless($booking->business_id === $business->id, 404);
        return view('public.business.book-success', compact('business', 'booking'));
    }

    private function resolveChannel(Business $business, ?string $requested): string
    {
        if ($business->bookings_via === 'whatsapp') return 'whatsapp';
        if ($business->bookings_via === 'web')      return 'web';
        return $requested === 'whatsapp' ? 'whatsapp' : 'web';
    }

    private function bookLabel(Business $business): string
    {
        return [
            'clinic'     => 'احجز كشف',
            'salon'      => 'احجز موعد',
            'education'  => 'احجز',
            'restaurant' => 'احجز طاولة',
            'service'    => 'اطلب خدمة',
        ][$business->type->slug] ?? 'احجز';
    }

    private function buildMessage(Business $b, Booking $bk): string
    {
        $when = $bk->booked_at->locale('ar')->isoFormat('dddd D MMMM · h:mm a');
        $extras = collect([
            $bk->service ? "الخدمة: {$bk->service}" : null,
            $bk->party_size > 1 ? "العدد: {$bk->party_size}" : null,
            $bk->notes ? "ملاحظات: {$bk->notes}" : null,
        ])->filter()->implode("\n");

        return "حجز جديد من بنهاوي · {$b->name}\n\n"
             . "العميل: {$bk->customer_name}\nالموبايل: {$bk->customer_phone}\n"
             . "الموعد: {$when}\n"
             . ($extras ? "\n{$extras}\n" : '')
             . "\nرقم الحجز: #{$bk->id}";
    }

    private function notifyOwner(Business $business, Booking $booking): void
    {
        $owner = $business->owner;
        if (! $owner || $owner->pushSubscriptions()->doesntExist()) {
            return;
        }

        $when = $booking->booked_at->locale('ar')->isoFormat('D MMM · h:mm a');

        try {
            app(PushSender::class)->toUser($owner, [
                'title' => "حجز جديد · {$business->name}",
                'body'  => "{$booking->customer_name} · {$when}",
                'url'   => route('merchant.bookings.index'),
                'tag'   => "booking-{$booking->id}",
            ]);
        } catch (\Throwable $e) {
            Log::warning('[Booking push] failed', ['booking_id' => $booking->id, 'err' => $e->getMessage()]);
        }
    }
}
