<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $business = $this->ownedBusiness();
        $date     = $request->get('date') ? Carbon::parse($request->get('date')) : today();

        $bookings = $business->bookings()
            ->whereDate('booked_at', $date)
            ->orderBy('booked_at')
            ->get();

        $weekStart = today()->copy()->startOfWeek();
        $weekDays = collect(range(0, 6))->map(function ($i) use ($weekStart, $business) {
            $d = $weekStart->copy()->addDays($i);
            return [
                'date'   => $d,
                'count'  => $business->bookings()->whereDate('booked_at', $d)->count(),
                'is_active' => $d->isSameDay(today()),
            ];
        });

        return view('merchant.bookings.index', compact('business', 'bookings', 'date', 'weekDays'));
    }

    public function update(Request $request, Booking $booking)
    {
        abort_unless($booking->business->owner_id === Auth::id(), 403);
        $data = $request->validate(['status' => 'required|in:new,confirmed,completed,cancelled']);
        $booking->update($data);
        return back();
    }

    private function ownedBusiness(): Business
    {
        $b = Auth::user()->businesses()->latest()->first();
        abort_unless($b, 404, 'لا يوجد نشاط.');
        return $b;
    }
}
