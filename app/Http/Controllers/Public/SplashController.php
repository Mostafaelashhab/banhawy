<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessType;

class SplashController extends Controller
{
    public function __invoke()
    {
        $featured = Business::with('type')
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderByDesc('rating')
            ->limit(6)
            ->get();

        $types = BusinessType::orderBy('sort')->get();

        $stats = [
            'businesses' => Business::where('is_active', true)->count(),
            'categories' => BusinessType::count(),
        ];

        return view('public.splash', compact('featured', 'types', 'stats'));
    }
}
