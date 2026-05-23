<?php

return [
    'vapid' => [
        'subject'     => env('VAPID_SUBJECT', 'mailto:noreply@banhawy.local'),
        'public_key'  => env('VAPID_PUBLIC_KEY', ''),
        'private_key' => env('VAPID_PRIVATE_KEY', ''),
    ],

    // Tune how often expired subscriptions are pruned (off → never auto-prune)
    'prune_after_days' => 90,
];
