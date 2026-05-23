<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessClaim;
use App\Services\PushSender;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function store(Request $request, Business $business): RedirectResponse
    {
        // Already claimed → nothing to do
        if ($business->owner_id) {
            return back()->with('claim_status', 'already_owned');
        }

        $data = $request->validate([
            'claimant_name'  => ['required', 'string', 'min:3', 'max:120'],
            'claimant_phone' => ['required', 'string', 'min:8', 'max:20'],
            'claimant_email' => ['nullable', 'email', 'max:120'],
            'message'        => ['nullable', 'string', 'max:1000'],
        ]);

        $data['claimant_phone'] = preg_replace('/[^\d+]/', '', $data['claimant_phone']);

        $claim = BusinessClaim::create([
            'business_id'    => $business->id,
            'user_id'        => $request->user()?->id,
            'claimant_name'  => $data['claimant_name'],
            'claimant_phone' => $data['claimant_phone'],
            'claimant_email' => $data['claimant_email'] ?? null,
            'message'        => $data['message'] ?? null,
            'status'         => 'pending',
        ]);

        // Notify all admins
        try {
            app(PushSender::class)->toAdmins([
                'title' => 'طلب ملكية جديد · بنهاوي',
                'body'  => $data['claimant_name'] . ' يطلب ملكية: ' . $business->name,
                'url'   => route('admin.claims.show', $claim),
                'tag'   => 'admin-claim-' . $claim->id,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('[push admins claim] '.$e->getMessage());
        }

        return back()->with('claim_status', 'submitted');
    }
}
