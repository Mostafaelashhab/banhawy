<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $business = $this->ownedBusiness();
        return view('merchant.settings', compact('business'));
    }

    public function update(Request $request)
    {
        $business = $this->ownedBusiness();

        $data = $request->validate([
            'orders_via'        => 'required|in:whatsapp,web,both',
            'bookings_via'      => 'required|in:whatsapp,web,both,walkin',
            'whatsapp'          => 'required|string|max:20',

            // Weekly hours — 7 days indexed 0=Sunday .. 6=Saturday
            'hours'             => 'sometimes|array',
            'hours.*.closed'    => 'sometimes|nullable',
            'hours.*.open'      => 'nullable|date_format:H:i',
            'hours.*.close'     => 'nullable|date_format:H:i',
        ]);

        // Normalize the hours JSON: ensure every day has {open, close, closed}.
        // Closed-day inputs may be blank — fall back to the existing values
        // so we never store NULLs that crash isOpenNow().
        $existing = $business->hours ?? [];
        $hours = [];
        foreach (range(0, 6) as $d) {
            $row    = $data['hours'][$d] ?? [];
            $closed = isset($row['closed']) && $row['closed'] !== '' && $row['closed'] !== '0';
            $hours[$d] = [
                'open'   => $row['open']  ?? ($existing[$d]['open']  ?? '10:00'),
                'close'  => $row['close'] ?? ($existing[$d]['close'] ?? '22:00'),
                'closed' => $closed,
            ];
        }

        $business->update([
            'orders_via'   => $data['orders_via'],
            'bookings_via' => $data['bookings_via'],
            'whatsapp'     => $data['whatsapp'],
            'hours'        => $hours,
        ]);

        return back()->with('flash', 'تم حفظ الإعدادات ✓');
    }

    private function ownedBusiness(): Business
    {
        $b = Auth::user()->businesses()->latest()->first();
        abort_unless($b, 404, 'لا يوجد نشاط مرتبط بحسابك.');
        return $b;
    }
}
