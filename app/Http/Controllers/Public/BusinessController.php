<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessView;
use App\Models\WhatsappClick;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function show(Business $business)
    {
        abort_unless($business->is_active, 404);

        BusinessView::create([
            'business_id' => $business->id,
            'ip_hash'     => hash('sha256', request()->ip() ?? ''),
        ]);
        $business->increment('views_count');

        $business->load('type', 'reviews');

        return view('public.business.show', compact('business'));
    }

    public function menu(Business $business)
    {
        abort_unless($business->is_active, 404);

        $business->load(['categories.products' => fn ($q) => $q->orderBy('sort')]);

        return view('public.business.menu', compact('business'));
    }

    public function whatsapp(Request $request, Business $business)
    {
        WhatsappClick::create([
            'business_id' => $business->id,
            'source'      => $request->get('source', 'profile'),
        ]);
        $business->increment('whatsapp_clicks');

        $message = $request->get('message', "أهلًا، شفت متجركم على بنهاوي.");
        return redirect()->away($business->whatsappLink($message));
    }
}
