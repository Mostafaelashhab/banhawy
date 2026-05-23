<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Support\Facades\Auth;

class QrController extends Controller
{
    public function __invoke()
    {
        $business = Auth::user()->businesses()->latest()->first();
        abort_unless($business, 404);

        $url = route('business.show', $business);
        return view('merchant.qr', compact('business', 'url'));
    }
}
