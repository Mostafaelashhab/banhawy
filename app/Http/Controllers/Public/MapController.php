<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessType;
use App\Models\RoadAlert;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function __invoke(Request $request)
    {
        $q        = trim((string) $request->get('q', ''));
        $typeSlug = trim((string) $request->get('type', ''));
        $openOnly = $request->boolean('open');

        // Services-focused: only shipping + service businesses on the map
        $allowedTypeIds = BusinessType::whereIn('slug', ['shipping', 'service'])->pluck('id');

        $type = ($typeSlug !== '' && in_array($typeSlug, ['shipping', 'service'], true))
            ? BusinessType::where('slug', $typeSlug)->first()
            : null;

        // Server returns all matching businesses for the type — text search is
        // applied client-side in realtime so markers filter live as the user types.
        $businesses = Business::with('type')
            ->where('is_active', true)
            ->whereIn('business_type_id', $allowedTypeIds)
            ->when($type, fn ($b) => $b->where('business_type_id', $type->id))
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

        $types = BusinessType::whereIn('slug', ['shipping', 'service'])->orderBy('sort')->get();

        // ── Road & safety alerts (community-reported) ────────────────
        $voterHash = hash('sha256', ($request->ip() ?? '') . '|alert-vote');

        $alerts = RoadAlert::active()
            ->latest()
            ->get();

        $voterChoices = \App\Models\RoadAlertVote::whereIn('road_alert_id', $alerts->pluck('id'))
            ->where('ip_hash', $voterHash)
            ->pluck('kind', 'road_alert_id');

        $alertsJson = $alerts->map(fn ($a) => [
            'id'            => $a->id,
            'type'          => $a->type,
            'type_label'    => $a->typeLabel(),
            'type_color'    => $a->typeColor(),
            'type_icon'     => $a->typeIcon(),
            'description'   => $a->description,
            'lat'           => (float) $a->lat,
            'lng'           => (float) $a->lng,
            'status'        => $a->status,
            'confirmations' => $a->confirmations_count,
            'rejections'    => $a->rejections_count,
            'is_confirmed'  => $a->isCommunityConfirmed(),
            'expires_at'    => $a->expires_at?->toIso8601String(),
            'created_at'    => $a->created_at?->toIso8601String(),
            'age_minutes'   => $a->created_at?->diffInMinutes(now()),
            'voter_choice'  => $voterChoices[$a->id] ?? null,
        ])->values()->toJson(JSON_UNESCAPED_UNICODE);

        // Alert types for the filter chips (preserve order)
        $alertTypes = collect(RoadAlert::TYPES)->map(fn ($cfg, $slug) => [
            'slug'  => $slug,
            'label' => $cfg['chip_label'],
            'color' => $cfg['color'],
            'icon'  => $cfg['icon'],
        ])->values();

        return view('public.map', compact(
            'businesses', 'businessesJson', 'types', 'type', 'openOnly', 'q',
            'alertsJson', 'alertTypes'
        ));
    }
}
