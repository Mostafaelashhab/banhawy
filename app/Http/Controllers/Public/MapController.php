<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessType;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function __invoke(Request $request)
    {
        $q        = trim((string) $request->get('q', ''));
        $typeSlug = trim((string) $request->get('type', ''));
        $openOnly = $request->boolean('open');

        $type = $typeSlug !== ''
            ? BusinessType::where('slug', $typeSlug)->first()
            : null;

        $businesses = Business::with('type')
            ->where('is_active', true)
            ->when($type, fn ($b) => $b->where('business_type_id', $type->id))
            ->when($q !== '', fn ($b) => $b->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('category', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%");
            }))
            ->orderByDesc('is_featured')
            ->orderByDesc('rating')
            ->get();

        if ($openOnly) {
            $businesses = $businesses->filter(fn ($b) => $b->isOpenNow())->values();
        }

        // Shape Leaflet needs (lightweight; no Eloquent serialization overhead)
        $businessesJson = $businesses->map(fn ($b) => [
            'id'       => $b->id,
            'name'     => $b->name,
            'slug'     => $b->slug,
            'cat'      => $b->category,
            'type'     => $b->type->slug,
            'rating'   => (float) $b->rating,
            'lat'      => (float) $b->lat,
            'lng'      => (float) $b->lng,
            'open'     => $b->isOpenNow(),
            'featured' => (bool) $b->is_featured,
            'url'      => route('business.show', $b),
        ])->values()->toJson(JSON_UNESCAPED_UNICODE);

        $types = BusinessType::orderBy('sort')->get();

        return view('public.map', compact('businesses', 'businessesJson', 'types', 'type', 'openOnly', 'q'));
    }
}
