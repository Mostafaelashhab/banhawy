<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoritesController extends Controller
{
    public function index()
    {
        $favorites = Auth::user()
            ->favorites()
            ->with('type')
            ->orderByPivot('created_at', 'desc')
            ->get();

        return view('public.favorites', compact('favorites'));
    }

    public function toggle(Request $request, Business $business)
    {
        $user = Auth::user();

        // syncWithoutDetaching prevents duplicate inserts even on race
        $existed = $user->favorites()->where('business_id', $business->id)->exists();

        if ($existed) {
            $user->favorites()->detach($business->id);
            $favorited = false;
            $msg = 'تم إزالته من المفضلة';
        } else {
            $user->favorites()->attach($business->id);
            $favorited = true;
            $msg = 'تمت إضافته للمفضلة ♥';
        }

        if ($request->wantsJson()) {
            return response()->json(['favorited' => $favorited, 'message' => $msg]);
        }

        return back()->with('flash', $msg);
    }
}
