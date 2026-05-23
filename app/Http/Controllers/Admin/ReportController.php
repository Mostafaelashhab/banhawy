<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status', 'pending');

        $reports = BusinessReport::with('business', 'user')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.index', compact('reports', 'status'));
    }

    public function show(BusinessReport $report): View
    {
        $report->load('business', 'user');
        return view('admin.reports.show', compact('report'));
    }

    public function update(Request $request, BusinessReport $report): RedirectResponse
    {
        $data = $request->validate([
            'status'     => ['required', Rule::in(['pending', 'reviewed', 'actioned', 'dismissed'])],
            'admin_note' => 'nullable|string|max:500',
        ]);

        $report->update([
            'status'      => $data['status'],
            'admin_note'  => $data['admin_note'] ?? $report->admin_note,
            'reviewed_at' => in_array($data['status'], ['reviewed','actioned','dismissed']) ? now() : null,
        ]);

        return back()->with('flash', 'تم تحديث حالة البلاغ ✓');
    }
}
