<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessClaim;
use App\Models\BusinessReport;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'businesses_total'    => Business::count(),
            'businesses_active'   => Business::where('is_active', true)->count(),
            'businesses_unclaimed'=> Business::whereNull('owner_id')->count(),
            'users_total'         => User::count(),
            'users_admins'        => User::where('role', 'admin')->count(),
            'users_owners'        => User::where('role', 'owner')->count(),
            'reviews_total'       => Review::count(),
            'claims_pending'      => BusinessClaim::where('status', 'pending')->count(),
            'reports_pending'     => BusinessReport::where('status', 'pending')->count(),
        ];

        $recentClaims  = BusinessClaim::with('business')->where('status', 'pending')->latest()->take(5)->get();
        $recentReports = BusinessReport::with('business')->where('status', 'pending')->latest()->take(5)->get();
        $recentBusinesses = Business::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentClaims', 'recentReports', 'recentBusinesses'));
    }
}
