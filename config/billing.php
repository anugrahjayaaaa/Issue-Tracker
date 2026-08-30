<?php

return [
    // Dummy mode: checkout() issues + activates the license directly, no real PG.
    // Doc §6 — keeps dev usable without any payment gateway connected.
    'fake' => env('BILLING_FAKE', true),

    'gateway' => env('BILLING_GATEWAY', 'dummy'),
    'gateway_url' => env('BILLING_GATEWAY_URL'),
    'webhook_secret' => env('BILLING_WEBHOOK_SECRET', 'dummy-webhook-secret'),
];
