<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoadAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoadAlertController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status', 'active');
        $type   = $request->input('type');

        $alerts = RoadAlert::with('user')
            ->when($status === 'active',   fn ($q) => $q->where('status', 'active')->where('expires_at', '>', now()))
            ->when($status === 'expired',  fn ($q) => $q->where(fn ($w) => $w->where('status', 'expired')->orWhere('expires_at', '<=', now())))
            ->when($status === 'rejected', fn ($q) => $q->where('status', 'rejected'))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.alerts.index', compact('alerts', 'status', 'type'));
    }

    public function update(Request $request, RoadAlert $alert): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'expired', 'rejected'])],
        ]);
        $alert->update(['status' => $data['status']]);
        return back()->with('flash', 'تم تحديث حالة التنبيه ✓');
    }

    public function destroy(RoadAlert $alert): RedirectResponse
    {
        $alert->delete();
        return back()->with('flash', 'تم حذف التنبيه ✓');
    }

    /**
     * Metrics endpoint for the admin (last 7 days at a glance).
     * Returns aggregates suitable for a dashboard widget or external monitor.
     */
    public function metrics(): \Illuminate\Http\JsonResponse
    {
        $now = now();
        $week = $now->copy()->subDays(7);

        $byType = RoadAlert::where('created_at', '>=', $week)
            ->selectRaw('type, COUNT(*) as total, SUM(CASE WHEN status="active" THEN 1 ELSE 0 END) as active')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $byStatus = RoadAlert::where('created_at', '>=', $week)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $byDay = RoadAlert::where('created_at', '>=', $week)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $confirmedRate = RoadAlert::where('created_at', '>=', $week)->count();
        $confirmedHi   = RoadAlert::where('created_at', '>=', $week)
            ->where('confirmations_count', '>=', RoadAlert::CONFIRM_THRESHOLD)
            ->count();

        return response()->json([
            'window_days'      => 7,
            'now'              => $now->toIso8601String(),
            'totals' => [
                'active'   => RoadAlert::active()->count(),
                'created_7d' => $confirmedRate,
                'community_confirmed_7d' => $confirmedHi,
                'rejected_by_community_7d' => $byStatus['rejected'] ?? 0,
            ],
            'by_type'   => $byType,
            'by_status' => $byStatus,
            'by_day'    => $byDay,
        ]);
    }

    /**
     * Heatmap endpoint — returns lat/lng/weight tuples for the last N days.
     * Lightweight (no joins, no eager loading).
     */
    public function heatmap(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $days = max(1, min(30, (int) $request->input('days', 7)));

        $points = RoadAlert::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('lat, lng, (confirmations_count + 1) as weight')
            ->limit(5000)
            ->get();

        return response()->json([
            'window_days' => $days,
            'points'      => $points,
        ]);
    }
}
