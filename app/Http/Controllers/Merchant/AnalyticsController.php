<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessView;
use App\Models\Order;
use App\Models\WhatsappClick;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function __invoke(Request $request)
    {
        $business = $this->ownedBusiness();
        $days     = (int) $request->get('days', 30);
        $days     = in_array($days, [7, 30, 90]) ? $days : 30;
        $since    = now()->subDays($days);

        // Views per day
        $viewsPerDay = BusinessView::where('business_id', $business->id)
            ->where('viewed_at', '>=', $since)
            ->select(DB::raw('DATE(viewed_at) as d'), DB::raw('COUNT(*) as c'))
            ->groupBy('d')->orderBy('d')->get();

        $series = [];
        for ($d = $days; $d >= 0; $d--) {
            $date = now()->subDays($d)->toDateString();
            $row = $viewsPerDay->firstWhere('d', $date);
            $series[] = ['date' => $date, 'value' => $row?->c ?? 0];
        }

        $totals = [
            'views'    => BusinessView::where('business_id', $business->id)->where('viewed_at', '>=', $since)->count(),
            'clicks'   => WhatsappClick::where('business_id', $business->id)->where('clicked_at', '>=', $since)->count(),
            'orders'   => $business->orders()->where('placed_at', '>=', $since)->count(),
            'bookings' => $business->bookings()->where('booked_at', '>=', $since)->count(),
        ];
        $totals['conversion'] = $totals['views'] > 0
            ? round(($totals['orders'] / $totals['views']) * 100, 1)
            : 0;

        // Top products from orders in range
        $topProducts = Order::where('business_id', $business->id)
            ->where('placed_at', '>=', $since)
            ->get()
            ->flatMap(fn ($o) => collect($o->items)->map(fn ($i) => [
                'name' => $i['name'],
                'qty'  => $i['qty'],
            ]))
            ->groupBy('name')
            ->map(fn ($g) => $g->sum('qty'))
            ->sortDesc()
            ->take(5);

        return view('merchant.analytics', compact('business', 'days', 'series', 'totals', 'topProducts'));
    }

    private function ownedBusiness(): Business
    {
        $b = Auth::user()->businesses()->latest()->first();
        abort_unless($b, 404, 'لا يوجد نشاط.');
        return $b;
    }
}
