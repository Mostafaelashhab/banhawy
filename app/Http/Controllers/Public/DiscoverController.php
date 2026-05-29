<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessType;
use App\Models\LostItem;
use App\Models\Task;

class DiscoverController extends Controller
{
    public function __invoke()
    {
        // The project is service-focused: only surface shipping + service businesses,
        // alongside the user-generated tasks + lost-items boards.
        $serviceTypeIds = BusinessType::whereIn('slug', ['shipping', 'service'])->pluck('id');

        $shippingType = BusinessType::where('slug', 'shipping')->first();
        $serviceType  = BusinessType::where('slug', 'service')->first();

        // Order by plan tier so Business plan businesses sit on top of Pro,
        // and Pro sits on top of Free. Featured + rating are secondary sorts.
        $planRank = \App\Models\Plan::query()
            ->selectRaw("plans.id, CASE plans.slug WHEN 'business' THEN 3 WHEN 'pro' THEN 2 ELSE 1 END as tier")
            ->pluck('tier', 'id');

        $orderByPlan = function ($q) {
            return $q->leftJoin('plans', 'businesses.plan_id', '=', 'plans.id')
                ->orderByRaw("CASE plans.slug WHEN 'business' THEN 3 WHEN 'pro' THEN 2 ELSE 1 END DESC")
                ->orderByDesc('businesses.is_featured')
                ->orderByDesc('businesses.rating')
                ->select('businesses.*');
        };

        $shipping = $shippingType
            ? $orderByPlan(Business::with('type', 'plan')
                ->where('businesses.is_active', true)
                ->where('businesses.business_type_id', $shippingType->id))
                ->limit(6)
                ->get()
            : collect();

        $services = $serviceType
            ? $orderByPlan(Business::with('type', 'plan')
                ->where('businesses.is_active', true)
                ->where('businesses.business_type_id', $serviceType->id))
                ->limit(6)
                ->get()
            : collect();

        $featured = $orderByPlan(Business::with('type', 'plan')
            ->where('businesses.is_active', true)
            ->where('businesses.is_featured', true)
            ->whereIn('businesses.business_type_id', $serviceTypeIds))
            ->limit(6)
            ->get();

        $latestTasks = Task::where('status', 'open')
            ->latest()
            ->limit(4)
            ->get();

        $latestLost = LostItem::where('status', 'open')
            ->latest()
            ->limit(4)
            ->get();

        $types = BusinessType::whereIn('slug', ['shipping', 'service'])
            ->orderBy('sort')
            ->get();

        return view('public.discover', compact(
            'shipping', 'services', 'featured', 'latestTasks', 'latestLost', 'types'
        ));
    }
}
