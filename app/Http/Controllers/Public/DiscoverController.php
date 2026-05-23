<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessType;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DiscoverController extends Controller
{
    public function __invoke()
    {
        // Active order banner — only shown to logged-in users with an in-flight order
        $activeOrder = Auth::check()
            ? Order::with('business')
                ->where('user_id', Auth::id())
                ->whereIn('status', ['new', 'preparing'])
                ->latest('placed_at')
                ->first()
            : null;

        $featured = Business::with('type')
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderByDesc('rating')
            ->limit(6)
            ->get();

        $latest = Business::with('type')
            ->where('is_active', true)
            ->latest()
            ->limit(6)
            ->get();

        $openNow = Business::with('type')
            ->where('is_active', true)
            ->orderByDesc('rating')
            ->get()
            ->filter(fn ($b) => $b->isOpenNow())
            ->take(6)
            ->values();

        $types = BusinessType::orderBy('sort')->get();

        return view('public.discover', compact('featured', 'latest', 'openNow', 'types', 'activeOrder'));
    }
}
