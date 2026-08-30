<?php

use App\Models\Role;
use App\Models\User;
use App\Services\AuditQueryService;
use App\Services\BulkDeleteService;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('UserService creates, updates, locks and unlocks a user', function () {
    $svc = new UserService;
    $roleId = Role::first()->id;

    $u = $svc->create([
        'name' => 'Serv Test',
        'username' => 'servtest',
        'email' => 'servtest@example.com',
        'password' => 'Secret@12345',
        'roles' => [$roleId],
    ]);
    expect($u->exists)->toBeTrue()
        ->and($u->roles->pluck('id')->all())->toBe([$roleId]);

    $svc->update($u, [
        'name' => 'Serv Renamed',
        'username' => 'servtest',
        'email' => 'servtest@example.com',
        'password' => '',
    ]);
    expect($u->fresh()->name)->toBe('Serv Renamed');

    $svc->lock($u);
    expect($u->fresh()->locked_permanently)->toBeTrue();

    $svc->unlock($u);
    expect($u->fresh()->locked_permanently)->toBeFalse();
});

it('UserService sendResetPassword returns the sent status', function () {
    Mail::fake();
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $status = (new UserService)->sendResetPassword($u);
    expect($status)->toBe(Password::RESET_LINK_SENT);
});

it('AuditQueryService filters by action', function () {
    $svc = new AuditQueryService;
    activity()->log('serv_test_action');

    $req = Request::create('/', 'GET', ['action' => 'serv_test_action']);
    expect($svc->forFilters($req)->count())->toBeGreaterThan(0);

    $reqNone = Request::create('/', 'GET', ['action' => 'no_such_action']);
    expect($svc->forFilters($reqNone)->count())->toBe(0);
});

it('BulkDeleteService soft-deletes and skips protected items', function () {
    $admin = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($admin);
    // ponytail: short-circuit the gate — spatie's can() is covered elsewhere;
    // this test exercises the loop + skip logic, not authorization.
    Gate::before(fn () => true);

    $role = Role::create(['name' => 'bulk_tmp_'.uniqid(), 'guard_name' => 'web']);
    $protected = Role::create(['name' => 'bulk_prot_'.uniqid(), 'guard_name' => 'web']);

    $done = (new BulkDeleteService)->run(
        Role::class,
        [$role->id, $protected->id],
        false,
        'roles',
        fn (Role $r) => $r->id === $protected->id
    );

    expect($done)->toBe(1)
        ->and($role->fresh()->deleted_at)->not->toBeNull()
        ->and($protected->fresh()->exists)->toBeTrue();
});
