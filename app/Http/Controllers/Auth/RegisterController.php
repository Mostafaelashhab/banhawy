<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessType;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /* Step 1 — Account */
    public function step1()
    {
        // Already-logged-in users skip account creation and jump to step 2
        if (Auth::check()) {
            return redirect()->route('register.step2');
        }
        return view('auth.register.step1');
    }

    public function step1Store(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('register.step2');
        }

        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'phone'    => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => Str::slug($data['name']) . '-' . Str::random(6) . '@banhawy.local',
            'phone'    => $data['phone'],
            'role'     => 'owner',
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);

        return redirect()->route('register.step2');
    }

    /* Step 2 — Business type */
    public function step2()
    {
        $types = BusinessType::orderBy('sort')->get();
        return view('auth.register.step2', compact('types'));
    }

    public function step2Store(Request $request)
    {
        $data = $request->validate([
            'business_type_id' => 'required|exists:business_types,id',
        ]);

        session(['register.business_type_id' => $data['business_type_id']]);

        return redirect()->route('register.step3');
    }

    /* Step 3 — Business details */
    public function step3()
    {
        abort_unless(session('register.business_type_id'), 302, '', ['Location' => route('register.step2')]);
        return view('auth.register.step3');
    }

    public function step3Store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'category' => 'required|string|max:120',
            'whatsapp' => 'required|string|max:20',
            'address'  => 'required|string|max:255',
            'lat'      => 'required|numeric',
            'lng'      => 'required|numeric',
        ]);

        session()->put('register.business', $data);

        return redirect()->route('register.step4');
    }

    /* Step 4 — Plan */
    public function step4()
    {
        abort_unless(session('register.business'), 302, '', ['Location' => route('register.step3')]);
        $plans = Plan::orderBy('sort')->get();
        return view('auth.register.step4', compact('plans'));
    }

    public function step4Store(Request $request)
    {
        $data = $request->validate(['plan_id' => 'required|exists:plans,id']);
        $details = session('register.business');
        $typeId  = session('register.business_type_id');

        // Promote the user from visitor → owner now that they have a business
        if (Auth::user()->role !== 'owner') {
            Auth::user()->update(['role' => 'owner']);
        }

        $business = Business::create([
            'owner_id'         => Auth::id(),
            'business_type_id' => $typeId,
            'plan_id'          => $data['plan_id'],
            'name'             => $details['name'],
            'slug'             => Str::slug($details['name']) . '-' . Str::lower(Str::random(4)),
            'category'         => $details['category'],
            'whatsapp'         => $details['whatsapp'],
            'address'          => $details['address'],
            'lat'              => $details['lat'],
            'lng'              => $details['lng'],
            'hours'            => collect(range(0, 6))->mapWithKeys(fn ($d) => [
                $d => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
            ])->all(),
            'is_active'        => true,
            'setup_progress'   => 60,
        ]);

        $plan = Plan::find($data['plan_id']);
        Subscription::create([
            'business_id' => $business->id,
            'plan_id'     => $plan->id,
            'status'      => 'trial',
            'starts_at'   => now()->toDateString(),
            'ends_at'     => now()->addMonth()->toDateString(),
            'amount'      => $plan->price_monthly,
        ]);

        session()->forget(['register.business_type_id', 'register.business']);

        // Notify admins of the new business
        try {
            app(\App\Services\PushSender::class)->toAdmins([
                'title' => 'متجر جديد انضم · بنهاوي',
                'body'  => $business->name . ' (' . ($business->type?->name_ar ?? '—') . ')',
                'url'   => route('admin.businesses.edit', $business),
                'tag'   => 'admin-biz-' . $business->id,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('[push admins biz] '.$e->getMessage());
        }

        return redirect()->route('register.success', $business);
    }

    public function success(Business $business)
    {
        abort_unless($business->owner_id === Auth::id(), 403);
        return view('auth.register.success', compact('business'));
    }
}
