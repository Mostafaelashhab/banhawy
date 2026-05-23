<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // If the logged-in user is actually a business owner, send them to
        // their merchant dashboard instead of the visitor account page.
        if ($user->isOwner()) {
            return redirect()->route('merchant.dashboard');
        }

        $favorites = $user->favorites()->with('type')->latest('favorites.created_at')->limit(3)->get();
        $favoritesCount = $user->favorites()->count();

        return view('public.account', compact('user', 'favorites', 'favoritesCount'));
    }
}
