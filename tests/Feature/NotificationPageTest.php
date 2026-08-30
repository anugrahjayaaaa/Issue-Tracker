<?php

use App\Models\User;
use App\Notifications\AuditNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('shows notifications page with native notifications', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $u->notify(new AuditNotification('login_success', '127.0.0.1'));

    $this->actingAs($u)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Notifications')
        ->assertSee('Login successful'); // human-readable label, not raw 'login_success'
});

it('marks notifications read on view (unread count drops to 0)', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $u->notify(new AuditNotification('logout', '127.0.0.1'));

    expect($u->unreadNotifications()->count())->toBe(1);

    $this->actingAs($u)->get(route('notifications.index'));

    expect($u->fresh()->unreadNotifications()->count())->toBe(0);
});

it('denies notifications page to user without audit.view', function () {
    $noPerms = User::create([
        'name' => 'No Perms', 'username' => 'noperms4',
        'email' => 'noperms4@example.com', 'password' => bcrypt('password'),
    ]); // no roles -> no audit.view
    $this->actingAs($noPerms)
        ->get(route('notifications.index'))
        ->assertForbidden();
});

it('backfill command copies auth activity into notifications', function () {
    Activity::create([
        'log_name' => 'default', 'description' => 'login_success',
        'causer_type' => User::class, 'causer_id' => User::where('email', 'admin@laravel-base.local')->first()->id,
        'created_at' => now(),
    ]);

    $this->artisan('notifications:backfill')->assertSuccessful();
    expect(User::where('email', 'admin@laravel-base.local')->first()->notifications()->count())->toBeGreaterThan(0);
});
