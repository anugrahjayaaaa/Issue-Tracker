<?php

return [
    // Dummy mode: checkout() issues + activates the license directly, no real PG.
    // Doc §6 — keeps dev usable without any payment gateway connected.
    'fake' => env('BILLING_FAKE', true),

    'gateway' => env('BILLING_GATEWAY', 'dummy'),
    'gateway_url' => env('BILLING_GATEWAY_URL'),
    // ponytail: no fallback — an unset secret must fail closed, not default to a public value.
    'webhook_secret' => env('BILLING_WEBHOOK_SECRET'),
];
