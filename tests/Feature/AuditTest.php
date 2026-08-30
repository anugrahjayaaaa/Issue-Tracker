<?php

use App\Models\Role;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed();
    $this->user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($this->user);
});

it('logs role creation via observer', function () {
    Role::create(['name' => 'audited_role', 'guard_name' => 'web']);
    expect(Activity::where('description', 'role_created')->exists())->toBeTrue();
});

it('shows audit index with logged activity', function () {
    Role::create(['name' => 'audited_role2', 'guard_name' => 'web']);
    $this->get(route('audit.index'))
        ->assertOk()
        ->assertSee('role_created');
});

it('filters audit by action', function () {
    Role::create(['name' => 'audited_role3', 'guard_name' => 'web']);
    $this->get(route('audit.index', ['action' => 'role_created']))
        ->assertOk()
        ->assertSee('role_created');
});

it('logs a real login event', function () {
    auth()->logout(); // ensure not already authenticated so attempt() fires Login
    $this->post(route('login.store'), [
        'identifier' => 'superadmin',
        'password' => 'Admin@base12345',
    ])->assertRedirect(route('dashboard'));

    expect(Activity::where('description', 'login_success')->exists())->toBeTrue();
});

it('records old and new values on user update (no password)', function () {
    $u = User::factory()->create(['username' => 'audupd'.time()]);
    $oldName = $u->name;
    $u->update(['name' => 'Changed Name', 'password' => bcrypt('NewPass@12345')]);

    $log = Activity::where('description', 'user_updated')->where('subject_id', $u->id)->latest()->first();
    expect($log)->not->toBeNull();
    expect($log->properties['old']['name'])->toBe($oldName);
    expect($log->properties['new']['name'])->toBe('Changed Name');
    expect($log->properties['new'])->not->toHaveKey('password'); // secret never logged
});
