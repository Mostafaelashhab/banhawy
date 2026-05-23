<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($data)) {
            $request->session()->regenerate();

            $landing = $this->landingForRole(Auth::user()->role);
            $intended = $request->session()->pull('url.intended');

            // Only honor 'intended' when it's safe for this user's role.
            // Stale intended URLs from a previous session can otherwise 404
            // (e.g. a visitor sent to /m/dashboard which requires a business).
            if ($intended && $this->intendedIsSafeForRole($intended, Auth::user()->role)) {
                return redirect($intended);
            }
            return redirect($landing);
        }

        return back()->withErrors(['phone' => 'بيانات الدخول غير صحيحة'])->onlyInput('phone');
    }

    private function landingForRole(?string $role): string
    {
        return match ($role) {
            'owner' => route('merchant.dashboard'),
            'admin' => route('home'),
            default => route('home'),
        };
    }

    private function intendedIsSafeForRole(string $url, ?string $role): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '/';
        // Visitors must not be bounced into the merchant area
        if ($role !== 'owner' && str_starts_with($path, '/m/')) {
            return false;
        }
        // Don't redirect to POST-only endpoints (they 404 on GET)
        if (preg_match('#^/(favorites/[^/]+|logout)$#', $path)) {
            return false;
        }
        return true;
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
