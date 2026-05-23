<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function store(Request $request, Business $business): RedirectResponse
    {
        $data = $request->validate([
            'reason'         => ['required', Rule::in(array_keys(BusinessReport::REASONS))],
            'details'        => ['nullable', 'string', 'max:2000'],
            'reporter_phone' => ['nullable', 'string', 'max:30'],
            'reporter_email' => ['nullable', 'email', 'max:120'],
        ]);

        $ipHash = hash('sha256', ($request->ip() ?? '') . '|' . $business->id);

        // Soft spam guard: same IP can't report the same business twice within 24h
        $recent = BusinessReport::where('business_id', $business->id)
            ->where('ip_hash', $ipHash)
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($recent) {
            return back()->with('report_status', 'duplicate');
        }

        BusinessReport::create([
            'business_id'    => $business->id,
            'user_id'        => $request->user()?->id,
            'reason'         => $data['reason'],
            'details'        => $data['details'] ?? null,
            'reporter_phone' => $data['reporter_phone'] ?? null,
            'reporter_email' => $data['reporter_email'] ?? null,
            'ip_hash'        => $ipHash,
            'status'         => 'pending',
        ]);

        return back()->with('report_status', 'submitted');
    }
}
