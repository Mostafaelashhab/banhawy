<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessClaim;
use App\Models\BusinessReport;
use App\Models\LostItem;
use App\Models\Review;
use App\Models\Task;
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
            'tasks_total'         => Task::count(),
            'tasks_open'          => Task::where('status', 'open')->count(),
            'lost_total'          => LostItem::count(),
            'lost_open'           => LostItem::where('status', 'open')->count(),
        ];

        $recentClaims     = BusinessClaim::with('business')->where('status', 'pending')->latest()->take(5)->get();
        $recentReports    = BusinessReport::with('business')->where('status', 'pending')->latest()->take(5)->get();
        $recentBusinesses = Business::latest()->take(5)->get();
        $recentTasks      = Task::latest()->take(5)->get();
        $recentLost       = LostItem::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentClaims',
            'recentReports',
            'recentBusinesses',
            'recentTasks',
            'recentLost'
        ));
    }
}
