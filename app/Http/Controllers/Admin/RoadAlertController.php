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
}
