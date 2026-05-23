<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackController extends Controller
{
    /**
     * Shows either the lookup form or the result for ?code=... in one view.
     * - Guests: can track any order by code (codes are random; effectively secure).
     * - Logged-in users: only see orders that belong to them (linked by user_id).
     *   Looking up someone else's code returns "not found" — privacy by default.
     */
    public function __invoke(Request $request)
    {
        $code = strtoupper(trim((string) $request->get('code', '')));
        $order = null;
        $notFound = false;

        if ($code !== '') {
            $query = Order::with('business')->where('code', $code);

            if (Auth::check()) {
                // Restrict to orders owned by this user — both via user_id link
                // and by phone match, so pre-signup orders still appear after they log in.
                $userId    = Auth::id();
                $userPhone = Auth::user()->phone;
                $query->where(function ($q) use ($userId, $userPhone) {
                    $q->where('user_id', $userId);
                    if ($userPhone) {
                        $q->orWhere('customer_phone', $userPhone);
                    }
                });
            }

            $order = $query->first();
            $notFound = $order === null;
        }

        return view('public.track', compact('code', 'order', 'notFound'));
    }
}
