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

        $type = $typeSlug !== ''
            ? BusinessType::where('slug', $typeSlug)->first()
            : null;

        $results = Business::with('type')
            ->where('is_active', true)
            ->when($type, fn ($b) => $b->where('business_type_id', $type->id))
            ->when($q !== '', fn ($builder) => $builder->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('category', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%");
            }))
            ->orderByDesc('is_featured')
            ->orderByDesc('rating')
            ->get();

        $types = BusinessType::orderBy('sort')->get();

        return view('public.search', [
            'q'       => $q,
            'type'    => $type,
            'types'   => $types,
            'results' => $results,
        ]);
    }
}
