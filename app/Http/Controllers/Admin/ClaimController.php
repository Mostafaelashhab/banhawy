<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessClaim;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClaimController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status', 'pending');

        $claims = BusinessClaim::with('business', 'user')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.claims.index', compact('claims', 'status'));
    }

    public function show(BusinessClaim $claim): View
    {
        $claim->load('business', 'user');
        return view('admin.claims.show', compact('claim'));
    }

    public function approve(Request $request, BusinessClaim $claim): RedirectResponse
    {
        $request->validate(['admin_note' => 'nullable|string|max:500']);

        $business = $claim->business;
        if (! $business) return back()->with('flash_error', 'النشاط محذوف');

        if ($business->owner_id) {
            return back()->with('flash_error', 'النشاط له مالك بالفعل');
        }

        // Find or create user for the claimant
        $user = null;
        if ($claim->user_id) {
            $user = User::find($claim->user_id);
        }
        if (! $user && $claim->claimant_email) {
            $user = User::where('email', $claim->claimant_email)->first();
        }
        if (! $user) {
            // Create a new owner account; they'll need to reset password
            $tempPassword = Str::random(16);
            $user = User::create([
                'name'     => $claim->claimant_name,
                'email'    => $claim->claimant_email ?: ('owner_'.Str::lower(Str::random(8)).'@banhawy.local'),
                'phone'    => $claim->claimant_phone,
                'password' => Hash::make($tempPassword),
                'role'     => 'owner',
            ]);
            session()->flash('temp_password', $tempPassword);
        } else {
            if ($user->role === 'customer') $user->update(['role' => 'owner']);
        }

        $business->update(['owner_id' => $user->id]);

        $claim->update([
            'status'      => 'approved',
            'admin_note'  => $request->input('admin_note'),
            'reviewed_at' => now(),
        ]);

        // Reject any other pending claims for this business
        BusinessClaim::where('business_id', $business->id)
            ->where('id', '!=', $claim->id)
            ->where('status', 'pending')
            ->update([
                'status'      => 'rejected',
                'admin_note'  => 'تم اعتماد طلب آخر لنفس النشاط',
                'reviewed_at' => now(),
            ]);

        return redirect()->route('admin.claims.show', $claim)->with('flash', "تم اعتماد الطلب وإسناد النشاط لـ {$user->name} ✓");
    }

    public function reject(Request $request, BusinessClaim $claim): RedirectResponse
    {
        $request->validate(['admin_note' => 'nullable|string|max:500']);

        $claim->update([
            'status'      => 'rejected',
            'admin_note'  => $request->input('admin_note'),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.claims.show', $claim)->with('flash', 'تم رفض الطلب');
    }
}
