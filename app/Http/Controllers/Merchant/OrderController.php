<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $business = $this->ownedBusiness();
        $status   = $request->get('status', 'new');

        $orders = $business->orders()
            ->where('status', $status)
            ->latest('placed_at')
            ->get();

        $counts = [
            'new'        => $business->orders()->where('status', 'new')->count(),
            'preparing'  => $business->orders()->where('status', 'preparing')->count(),
            'completed'  => $business->orders()->where('status', 'completed')->count(),
            'cancelled'  => $business->orders()->where('status', 'cancelled')->count(),
        ];

        return view('merchant.orders.index', compact('business', 'orders', 'status', 'counts'));
    }

    public function update(Request $request, Order $order)
    {
        abort_unless($order->business->owner_id === Auth::id(), 403);
        $data = $request->validate(['status' => 'required|in:new,preparing,completed,cancelled']);
        $order->update($data);
        return back();
    }

    private function ownedBusiness(): Business
    {
        $b = Auth::user()->businesses()->latest()->first();
        abort_unless($b, 404, 'لا يوجد نشاط.');
        return $b;
    }
}
