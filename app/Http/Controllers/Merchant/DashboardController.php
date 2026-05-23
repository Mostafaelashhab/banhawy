<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessView;
use App\Models\WhatsappClick;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $business = $this->ownedBusiness();

        $weekStart = now()->startOfWeek();

        $stats = [
            'views_week'      => BusinessView::where('business_id', $business->id)->where('viewed_at', '>=', $weekStart)->count(),
            'wa_clicks_week'  => WhatsappClick::where('business_id', $business->id)->where('clicked_at', '>=', $weekStart)->count(),
            'orders_new'      => $business->orders()->where('status', 'new')->count(),
            'bookings_today'  => $business->bookings()->whereDate('booked_at', today())->count(),
        ];

        return view('merchant.dashboard', compact('business', 'stats'));
    }

    private function ownedBusiness(): Business
    {
        $b = Auth::user()->businesses()->latest()->first();
        abort_unless($b, 404, 'لا يوجد نشاط مرتبط بحسابك.');
        return $b;
    }
}
