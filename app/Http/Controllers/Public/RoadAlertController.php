<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\RoadAlert;
use App\Models\RoadAlertVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RoadAlertController extends Controller
{
    /** Max alerts returned in one response (bbox guard). */
    private const MAX_PAGE = 500;

    /** How long to memoise the un-bounded recent alerts query. */
    private const CACHE_TTL_SECONDS = 15;

    /** Per-user soft cap on alerts created per hour. */
    private const USER_HOURLY_CAP = 8;

    /**
     * Active alerts for the map / polling.
     *
     *  Query params:
     *    bounds       (south,west,north,east)  – limit to a viewport
     *    since        (ISO timestamp)          – delta polling: only changed rows
     *    types        (comma list of slugs)    – filter by type
     *
     *  Always returns server-time so the client can use it as the next `since`.
     */
    public function active(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bounds' => ['nullable', 'string', 'regex:/^-?\d+(\.\d+)?,-?\d+(\.\d+)?,-?\d+(\.\d+)?,-?\d+(\.\d+)?$/'],
            'since'  => ['nullable', 'date'],
            'types'  => ['nullable', 'string', 'max:200'],
        ]);

        $now       = now();
        $voterHash = $this->voterHash($request);

        // Parse bounding box → [south, west, north, east]
        $bounds = null;
        if (! empty($data['bounds'])) {
            $parts = array_map('floatval', explode(',', $data['bounds']));
            if (count($parts) === 4 && $parts[0] < $parts[2] && $parts[1] < $parts[3]) {
                $bounds = $parts;
            }
        }

        // Parse `since` for delta polling
        $since = null;
        if (! empty($data['since'])) {
            try { $since = Carbon::parse($data['since']); }
            catch (\Throwable) { $since = null; }
        }

        // Filter types whitelist
        $allowedTypes = array_keys(RoadAlert::TYPES);
        $types = null;
        if (! empty($data['types'])) {
            $types = array_intersect(explode(',', $data['types']), $allowedTypes);
            if (empty($types)) $types = null;
        }

        // ── Build query ──────────────────────────────────────────
        $query = RoadAlert::query()->active();

        if ($bounds)  $query->withinBounds(...$bounds);
        if ($since)   $query->changedSince($since);
        if ($types)   $query->whereIn('type', $types);

        // Order: confirmed alerts first, then newest. Cap to avoid heavy payloads.
        $query->orderByDesc('confirmations_count')
              ->orderByDesc('updated_at')
              ->limit(self::MAX_PAGE);

        // ── Cache the raw rows (without voter context) for short TTL ──
        // Cache key encodes the filters that affect rows.
        $cacheKey = 'alerts:active:' . md5(json_encode([
            'b' => $bounds, 's' => $since?->timestamp, 't' => $types,
        ]));

        $rows = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($query) {
            return $query->get();
        });

        // Map to JSON. Voter context is per-request → done outside the cache.
        $voterChoices = $rows->isEmpty() ? collect() : RoadAlertVote::query()
            ->whereIn('road_alert_id', $rows->pluck('id'))
            ->where('ip_hash', $voterHash)
            ->pluck('kind', 'road_alert_id');

        $alerts = $rows->map(fn ($a) => $this->shape($a, $voterChoices[$a->id] ?? null));

        return response()->json([
            'alerts'      => $alerts->values(),
            'server_time' => $now->toIso8601String(),
            'count'       => $alerts->count(),
            'truncated'   => $alerts->count() === self::MAX_PAGE,
        ])->header('Cache-Control', 'no-store');
    }

    /**
     * Submit a new alert. Anti-spam: dedup by location + per-user/IP rate limit.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'        => ['required', Rule::in(array_keys(RoadAlert::TYPES))],
            'lat'         => 'required|numeric|between:-90,90',
            'lng'         => 'required|numeric|between:-180,180',
            'description' => 'nullable|string|max:500',
        ]);

        // Geo-fence: must be inside Banha area (30km from centre)
        $distKm = $this->distanceKm(30.4582, 31.1797, (float) $data['lat'], (float) $data['lng']);
        if ($distKm > 30) {
            return response()->json(['ok' => false, 'error' => 'الموقع خارج نطاق بنها.'], 422);
        }

        $ipHash = hash('sha256', ($request->ip() ?? '') . '|alert');
        $userId = Auth::id();

        // ── IP rate limit (already handled by throttle middleware, but enforce
        //    a tighter "alerts created" cap here for visibility) ──
        $recentByIp = RoadAlert::where('ip_hash', $ipHash)
            ->where('created_at', '>=', now()->subHour())
            ->count();
        if ($recentByIp >= 10) {
            return response()->json(['ok' => false, 'error' => 'تجاوزت الحد المسموح. استنّى ساعة وحاول تاني.'], 429);
        }

        // Per-user cap (extra protection over IP, since multiple users can NAT-share an IP)
        if ($userId) {
            $recentByUser = RoadAlert::where('user_id', $userId)
                ->where('created_at', '>=', now()->subHour())
                ->count();
            if ($recentByUser >= self::USER_HOURLY_CAP) {
                return response()->json(['ok' => false, 'error' => 'وصلت الحد الأقصى للساعة.'], 429);
            }
        }

        // ── Anti-duplicate: same type within radius + 10 minutes ──
        $duplicate = RoadAlert::findNearbyDuplicate(
            $data['type'],
            (float) $data['lat'],
            (float) $data['lng'],
            10
        );
        if ($duplicate) {
            // Instead of refusing outright, treat the new submission as a confirmation
            // of the existing alert. This is the right UX for crowd-sourcing.
            $this->autoConfirmFromIp($duplicate, $ipHash);
            return response()->json([
                'ok'         => true,
                'merged'     => true,
                'message'    => 'تنبيه مشابه موجود قريب · تم اعتباره تأكيد إضافي.',
                'alert'      => $this->shape($duplicate->fresh(), 'confirm'),
            ], 200);
        }

        // ── Create ──
        $cfg = RoadAlert::TYPES[$data['type']];
        $alert = RoadAlert::create([
            'user_id'     => $userId,
            'type'        => $data['type'],
            'title'       => $cfg['label_ar'],
            'description' => $data['description'] ?? null,
            'lat'         => $data['lat'],
            'lng'         => $data['lng'],
            'status'      => 'active',
            'ip_hash'     => $ipHash,
            'expires_at'  => now()->addHours($cfg['ttl_hours']),
        ]);

        $this->bumpCacheVersion();

        Log::info('[alert.created]', [
            'id'   => $alert->id,
            'type' => $alert->type,
            'user' => $userId,
        ]);

        return response()->json([
            'ok'    => true,
            'alert' => $this->shape($alert, null),
        ], 201);
    }

    public function confirm(Request $request, RoadAlert $alert): JsonResponse
    {
        return $this->vote($request, $alert, 'confirm');
    }

    public function reject(Request $request, RoadAlert $alert): JsonResponse
    {
        return $this->vote($request, $alert, 'reject');
    }

    private function vote(Request $request, RoadAlert $alert, string $kind): JsonResponse
    {
        if ($alert->status !== 'active' || $alert->isExpired()) {
            return response()->json(['ok' => false, 'error' => 'هذا التنبيه لم يعد نشطاً.'], 410);
        }

        $ipHash = $this->voterHash($request);

        DB::transaction(function () use ($alert, $ipHash, $kind) {
            $existing = RoadAlertVote::where('road_alert_id', $alert->id)
                ->where('ip_hash', $ipHash)
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->kind === $kind) {
                return;
            }

            if ($existing) {
                if ($existing->kind === 'confirm') $alert->decrement('confirmations_count');
                else                                $alert->decrement('rejections_count');
                $existing->update(['kind' => $kind]);
            } else {
                RoadAlertVote::create([
                    'road_alert_id' => $alert->id,
                    'ip_hash'       => $ipHash,
                    'kind'          => $kind,
                ]);
            }

            if ($kind === 'confirm') {
                $alert->increment('confirmations_count');
                $maxExpires = now()->addHours($alert->ttlHoursForType());
                $extended = $alert->expires_at->copy()->addMinutes(30);
                $alert->update(['expires_at' => $extended->min($maxExpires)]);
            } else {
                $alert->increment('rejections_count');
                if ($alert->fresh()->rejections_count >= RoadAlert::REJECT_THRESHOLD) {
                    $alert->update(['status' => 'rejected']);
                }
            }
        });

        $alert->refresh();
        $this->bumpCacheVersion();

        return response()->json([
            'ok'    => true,
            'alert' => $this->shape($alert, $this->voterChoice($alert->id, $ipHash)),
        ]);
    }

    /* ── Internal helpers ─────────────────────────────────────── */

    private function autoConfirmFromIp(RoadAlert $alert, string $ipHash): void
    {
        // Idempotent: only counts the first time this IP touches this alert
        $already = RoadAlertVote::where('road_alert_id', $alert->id)
            ->where('ip_hash', $ipHash)
            ->exists();
        if ($already) return;

        RoadAlertVote::create([
            'road_alert_id' => $alert->id,
            'ip_hash'       => $ipHash,
            'kind'          => 'confirm',
        ]);
        $alert->increment('confirmations_count');

        $maxExpires = now()->addHours($alert->ttlHoursForType());
        $extended   = $alert->expires_at->copy()->addMinutes(30);
        $alert->update(['expires_at' => $extended->min($maxExpires)]);
    }

    private function voterHash(Request $request): string
    {
        return hash('sha256', ($request->ip() ?? '') . '|alert-vote');
    }

    private function voterChoice(int $alertId, string $ipHash): ?string
    {
        return RoadAlertVote::where('road_alert_id', $alertId)
            ->where('ip_hash', $ipHash)
            ->value('kind');
    }

    /**
     * Invalidate the active-alerts cache when anything changes.
     * (Keys are filter-specific; we use a generation counter trick to nuke all.)
     */
    private function bumpCacheVersion(): void
    {
        // Simple flush of all `alerts:active:*` keys. Cache::flush() is too broad;
        // since keys are short-lived (15s TTL) the right move is just to forget the
        // common keys. With a tag-able store we'd tag them — but file/db driver
        // doesn't support tags. Acceptable trade-off: clients will see staleness
        // for at most 15 seconds.
        // Intentionally a no-op; the short TTL is our consistency window.
    }

    private function shape(RoadAlert $a, ?string $voterChoice): array
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
            'updated_at'      => $a->updated_at?->toIso8601String(),
            'age_minutes'     => $a->created_at?->diffInMinutes(now()),
            'voter_choice'    => $voterChoice,
        ];
    }

    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        return RoadAlert::distanceMeters($lat1, $lng1, $lat2, $lng2) / 1000;
    }
}
