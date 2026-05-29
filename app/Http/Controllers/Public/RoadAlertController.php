<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\RoadAlert;
use App\Models\RoadAlertVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoadAlertController extends Controller
{
    /**
     * Return all currently-active alerts as JSON.
     * Used by the map's navigation polling loop (every ~12s).
     */
    public function active(Request $request): JsonResponse
    {
        $ipHash = hash('sha256', ($request->ip() ?? '') . '|alert-vote');

        $alerts = RoadAlert::active()->latest()->get();

        return response()->json([
            'alerts' => $alerts->map(fn ($a) => $this->shape($a, $ipHash))->values(),
        ]);
    }

    /**
     * Submit a new alert.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'        => ['required', Rule::in(array_keys(RoadAlert::TYPES))],
            'lat'         => 'required|numeric|between:-90,90',
            'lng'         => 'required|numeric|between:-180,180',
            'description' => 'nullable|string|max:500',
        ]);

        // Light sanity: keep alerts inside Banha (~25km radius from centre 30.46, 31.18)
        $distKm = $this->distanceKm(30.4582, 31.1797, (float) $data['lat'], (float) $data['lng']);
        if ($distKm > 30) {
            return response()->json([
                'ok'     => false,
                'error'  => 'الموقع خارج نطاق بنها.',
            ], 422);
        }

        $ipHash = hash('sha256', ($request->ip() ?? '') . '|alert');

        // Rate-limit per IP — max 10 alerts/hour
        $recent = RoadAlert::where('ip_hash', $ipHash)
            ->where('created_at', '>=', now()->subHour())
            ->count();
        if ($recent >= 10) {
            return response()->json([
                'ok'    => false,
                'error' => 'تجاوزت الحد المسموح. استنّى ساعة وحاول تاني.',
            ], 429);
        }

        $cfg = RoadAlert::TYPES[$data['type']];

        $alert = RoadAlert::create([
            'user_id'     => Auth::id(),
            'type'        => $data['type'],
            'title'       => $cfg['label_ar'],
            'description' => $data['description'] ?? null,
            'lat'         => $data['lat'],
            'lng'         => $data['lng'],
            'status'      => 'active',
            'ip_hash'     => $ipHash,
            'expires_at'  => now()->addHours($cfg['ttl_hours']),
        ]);

        return response()->json([
            'ok'    => true,
            'alert' => $this->shape($alert, $ipHash),
        ]);
    }

    /**
     * "مازال موجود" → confirm the alert (extends life + adds confirmation).
     */
    public function confirm(Request $request, RoadAlert $alert): JsonResponse
    {
        return $this->vote($request, $alert, 'confirm');
    }

    /**
     * "غير موجود" → reject the alert.
     */
    public function reject(Request $request, RoadAlert $alert): JsonResponse
    {
        return $this->vote($request, $alert, 'reject');
    }

    private function vote(Request $request, RoadAlert $alert, string $kind): JsonResponse
    {
        if ($alert->status !== 'active' || $alert->isExpired()) {
            return response()->json(['ok' => false, 'error' => 'هذا التنبيه لم يعد نشطاً.'], 410);
        }

        $ipHash = hash('sha256', ($request->ip() ?? '') . '|alert-vote');

        // One vote per IP per alert (changes mind = updates kind)
        $existing = RoadAlertVote::where('road_alert_id', $alert->id)
            ->where('ip_hash', $ipHash)
            ->first();

        DB::transaction(function () use ($alert, $existing, $ipHash, $kind) {
            if ($existing && $existing->kind === $kind) {
                return;   // idempotent
            }

            if ($existing) {
                // Switch vote: undo the previous one
                if ($existing->kind === 'confirm') {
                    $alert->decrement('confirmations_count');
                } else {
                    $alert->decrement('rejections_count');
                }
                $existing->update(['kind' => $kind]);
            } else {
                RoadAlertVote::create([
                    'road_alert_id' => $alert->id,
                    'ip_hash'       => $ipHash,
                    'kind'          => $kind,
                ]);
            }

            // Apply the new vote
            if ($kind === 'confirm') {
                $alert->increment('confirmations_count');
                // Each confirmation extends life by 30 minutes (cap at original TTL)
                $maxExpires = now()->addHours($alert->ttlHoursForType());
                $extended = $alert->expires_at->copy()->addMinutes(30);
                $alert->update(['expires_at' => min($extended, $maxExpires)]);
            } else {
                $alert->increment('rejections_count');
                // Auto-hide if too many rejections
                if ($alert->fresh()->rejections_count >= RoadAlert::REJECT_THRESHOLD) {
                    $alert->update(['status' => 'rejected']);
                }
            }
        });

        $alert->refresh();

        return response()->json([
            'ok'    => true,
            'alert' => $this->shape($alert, $ipHash),
        ]);
    }

    /**
     * Lightweight JSON payload shape used by the map's JS layer.
     */
    private function shape(RoadAlert $a, ?string $voterHash = null): array
    {
        return [
            'id'              => $a->id,
            'type'            => $a->type,
            'type_label'      => $a->typeLabel(),
            'type_color'      => $a->typeColor(),
            'type_icon'       => $a->typeIcon(),
            'description'     => $a->description,
            'lat'             => (float) $a->lat,
            'lng'             => (float) $a->lng,
            'status'          => $a->status,
            'confirmations'   => $a->confirmations_count,
            'rejections'      => $a->rejections_count,
            'is_confirmed'    => $a->isCommunityConfirmed(),
            'expires_at'      => $a->expires_at?->toIso8601String(),
            'created_at'      => $a->created_at?->toIso8601String(),
            'age_minutes'     => $a->created_at?->diffInMinutes(now()),
            'voter_choice'    => $voterHash ? $this->voterChoice($a->id, $voterHash) : null,
        ];
    }

    private function voterChoice(int $alertId, string $ipHash): ?string
    {
        return RoadAlertVote::where('road_alert_id', $alertId)
            ->where('ip_hash', $ipHash)
            ->value('kind');
    }

    /** Great-circle distance in km. */
    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earth * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
