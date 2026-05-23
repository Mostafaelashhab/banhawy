<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushSender
{
    public function __construct(private ?WebPush $webPush = null)
    {
        $this->webPush ??= new WebPush([
            'VAPID' => [
                'subject'    => config('webpush.vapid.subject'),
                'publicKey'  => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ]);
    }

    /**
     * Send a push payload to all of $user's subscriptions.
     *
     * @param  array{title:string, body:string, url?:string, tag?:string, actions?:array}  $payload
     * @return int  Number of subscriptions the push was queued for.
     */
    public function toUser(User $user, array $payload): int
    {
        return $this->toSubscriptions($user->pushSubscriptions, $payload);
    }

    /**
     * Broadcast a payload to every admin user that has push subscriptions.
     * Returns the total number of subscriptions the push was queued for.
     */
    public function toAdmins(array $payload): int
    {
        $subs = PushSubscription::whereHas('user', fn ($q) => $q->where('role', 'admin'))->get();
        return $this->toSubscriptions($subs, $payload);
    }

    /**
     * Send to many users at once.
     */
    public function toUsers(Collection $users, array $payload): int
    {
        $subs = PushSubscription::whereIn('user_id', $users->pluck('id'))->get();
        return $this->toSubscriptions($subs, $payload);
    }

    public function toSubscriptions(Collection $subs, array $payload): int
    {
        $count = 0;
        foreach ($subs as $sub) {
            $this->webPush->queueNotification(
                Subscription::create($sub->toWebPush()),
                json_encode($payload, JSON_UNESCAPED_UNICODE)
            );
            $count++;
        }

        // Flush + handle expired/gone endpoints (clean DB so we don't keep pushing to them)
        foreach ($this->webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            if (! $report->isSuccess()) {
                $code = $report->getResponse()?->getStatusCode();
                Log::warning('[Push] failed', ['endpoint' => $endpoint, 'status' => $code, 'reason' => $report->getReason()]);

                // 404 / 410 → the subscription is gone, delete it
                if (in_array($code, [404, 410], true)) {
                    PushSubscription::where('endpoint', $endpoint)->delete();
                }
            }
        }

        return $count;
    }
}
