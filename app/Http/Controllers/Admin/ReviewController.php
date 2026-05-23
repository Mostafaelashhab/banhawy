<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $q       = $request->input('q');
        $minRate = $request->input('min_rate');
        $maxRate = $request->input('max_rate');

        $reviews = Review::with('business')
            ->when($q, fn ($qb) => $qb->where('body', 'like', "%$q%"))
            ->when($minRate, fn ($qb) => $qb->where('rating', '>=', (int) $minRate))
            ->when($maxRate, fn ($qb) => $qb->where('rating', '<=', (int) $maxRate))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews', 'q', 'minRate', 'maxRate'));
    }

    public function destroy(Review $review): RedirectResponse
    {
        $businessId = $review->business_id;
        $review->delete();

        // Recompute aggregate rating + count
        $business = Business::find($businessId);
        if ($business) {
            $count = $business->reviews()->count();
            $avg   = $count > 0 ? round($business->reviews()->avg('rating'), 2) : 0;
            $business->update(['reviews_count' => $count, 'rating' => $avg]);
        }

        return back()->with('flash', 'تم حذف التقييم ✓');
    }
}
