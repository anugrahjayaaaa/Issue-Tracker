<?php

use App\Models\Plan;
use App\Models\Setting;
use App\Models\User;
use App\Services\LicenseService;
use App\Services\PlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('checkout route exists and method is reachable (dummy mode)', function () {
    config(['billing.fake' => true]);
    $user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($user);

    Plan::create([
        'slug' => 'pro', 'name' => 'Pro', 'price_monthly' => 99000,
        'is_active' => true, 'billing_period' => 'monthly',
        'limits' => ['max_members' => 5], 'features' => ['kanban'],
    ]);

    // form posts plan_slug (as plans/index.blade does)
    $this->post(route('billing.checkout'), ['plan_slug' => 'pro'])
        ->assertRedirect(route('billing.index'));

    // dummy mode should have paid immediately
    $payments = \App\Models\Payment::where('plan_slug', 'pro')->get();
    expect($payments->count())->toBe(1)
        ->and($payments->first()->status)->toBe('paid');
});

it('checkout rejects non-existent plan_slug (404)', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $this->post(route('billing.checkout'), ['plan_slug' => 'nonexistent'])
        ->assertNotFound();
});

it('checkout rejects free plan (price=0) — 404', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $this->post(route('billing.checkout'), ['plan_slug' => 'free'])
        ->assertNotFound();
});

it('webhook rejects invalid signature (403)', function () {
    $this->post(route('billing.webhook'), ['plan_slug' => 'free', 'status' => 'paid'])
        ->assertForbidden();
});

it('webhook with valid signature completes payment', function () {
    config(['billing.fake' => true, 'billing.webhook_secret' => 'test-secret']);
    Plan::create([
        'slug' => 'pro', 'name' => 'Pro', 'price_monthly' => 99000,
        'is_active' => true, 'billing_period' => 'monthly',
        'limits' => ['max_members' => 5], 'features' => ['kanban'],
    ]);

    $this->post(route('billing.webhook'), [
        'plan_slug' => 'pro', 'status' => 'paid', 'order_id' => 'order-test-1',
    ], [
        'X-Billing-Signature' => 'test-secret',
    ])->assertOk();

    expect(\App\Models\Payment::where('gateway_ref', 'order-test-1')->first()->status)->toBe('paid');
});

it('tampered plan setting reverts to free features (§10.1)', function () {
    // client edits settings.active_plan directly without valid license
    \App\Models\Setting::set('active_plan', 'pro');
    \App\Models\Setting::set('license_key', null);

    Plan::create(['slug' => 'pro', 'name' => 'Pro', 'price_monthly' => 99000,
        'is_active' => true, 'billing_period' => 'monthly',
        'limits' => ['max_members' => 5], 'features' => ['kanban']]);

    // no activated license -> PlanService refuses paid features
    expect(PlanService::for()->can('kanban'))->toBeFalse();
    expect(Setting::get('active_plan'))->toBe('pro'); // setting persists but features locked
});

it('dashboard renders license badge', function () {
    $user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('No license')
        ->assertSee('Free plan');
});
