<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessType;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q        = trim((string) $request->get('q', ''));
        $typeSlug = trim((string) $request->get('type', ''));

        // Services-focused search: only shipping + service businesses are surfaced
        $allowedTypeIds = BusinessType::whereIn('slug', ['shipping', 'service'])->pluck('id');

        $type = ($typeSlug !== '' && in_array($typeSlug, ['shipping', 'service'], true))
            ? BusinessType::where('slug', $typeSlug)->first()
            : null;

        // Note: we no longer filter by `q` server-side. The client-side realtime
        // filter handles text matching, so the server always returns the full set
        // for the chosen type — keeps the UX snappy as the user types/clears.
        $results = Business::with('type', 'plan')
            ->leftJoin('plans', 'businesses.plan_id', '=', 'plans.id')
            ->where('businesses.is_active', true)
            ->whereIn('businesses.business_type_id', $allowedTypeIds)
            ->when($type, fn ($b) => $b->where('businesses.business_type_id', $type->id))
            ->orderByRaw("CASE plans.slug WHEN 'business' THEN 3 WHEN 'pro' THEN 2 ELSE 1 END DESC")
            ->orderByDesc('businesses.is_featured')
            ->orderByDesc('businesses.rating')
            ->select('businesses.*')
            ->get();

        $types = BusinessType::whereIn('slug', ['shipping', 'service'])->orderBy('sort')->get();

        return view('public.search', [
            'q'       => $q,
            'type'    => $type,
            'types'   => $types,
            'results' => $results,
        ]);
    }
}
