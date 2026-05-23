<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\PushSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint'      => 'required|string|max:500',
            'keys.p256dh'   => 'required|string|max:255',
            'keys.auth'     => 'required|string|max:255',
        ]);

        PushSubscription::updateOrCreate(
            ['user_id' => Auth::id(), 'endpoint' => $data['endpoint']],
            [
                'p256dh'       => $data['keys']['p256dh'],
                'auth'         => $data['keys']['auth'],
                'user_agent'   => substr((string) $request->header('User-Agent'), 0, 255),
                'last_seen_at' => now(),
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request)
    {
        $endpoint = (string) $request->input('endpoint', '');

        Auth::user()->pushSubscriptions()
            ->when($endpoint !== '', fn ($q) => $q->where('endpoint', $endpoint))
            ->delete();

        return response()->json(['ok' => true]);
    }

    public function test(PushSender $sender)
    {
        $sent = $sender->toUser(Auth::user(), [
            'title' => 'بنهاوي · إشعار تجريبي ✓',
            'body'  => 'الإشعارات شغالة! هتوصلك تنبيهات الطلبات الجديدة هنا.',
            'url'   => Auth::user()->isOwner() ? route('merchant.dashboard') : route('home'),
            'tag'   => 'banhawy-test',
        ]);

        return response()->json(['ok' => true, 'sent_to_devices' => $sent]);
    }
}
