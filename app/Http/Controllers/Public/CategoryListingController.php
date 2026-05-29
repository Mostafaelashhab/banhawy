<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessType;
use Illuminate\View\View;

class CategoryListingController extends Controller
{
    public function shipping(): View
    {
        return $this->render('shipping');
    }

    public function services(): View
    {
        return $this->render('service');
    }

    private function render(string $typeSlug): View
    {
        $type = BusinessType::where('slug', $typeSlug)->firstOrFail();

        // Order by plan tier so paid plans get top placement
        $businesses = Business::with('type', 'plan')
            ->leftJoin('plans', 'businesses.plan_id', '=', 'plans.id')
            ->where('businesses.is_active', true)
            ->where('businesses.business_type_id', $type->id)
            ->orderByRaw("CASE plans.slug WHEN 'business' THEN 3 WHEN 'pro' THEN 2 ELSE 1 END DESC")
            ->orderByDesc('businesses.is_featured')
            ->orderByDesc('businesses.rating')
            ->select('businesses.*')
            ->get();

        // Extract unique sub-categories from the businesses for the chip filter
        $categories = $businesses
            ->pluck('category')
            ->filter()
            ->flatMap(fn ($c) => preg_split('/\s*·\s*/', $c))
            ->map(fn ($c) => trim($c))
            ->filter()
            ->unique()
            ->values()
            ->take(12);

        return view('public.' . ($typeSlug === 'shipping' ? 'shipping' : 'services'), compact('businesses', 'categories', 'type'));
    }
}
