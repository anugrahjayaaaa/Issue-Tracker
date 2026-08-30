<?php

use App\Models\Role;
use App\Models\User;

beforeEach(fn () => $this->seed());

it('logs 405 (wrong HTTP method) via middleware', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $role = Role::create(['name' => 'logme', 'guard_name' => 'web']);
    $role->delete();
    $this->get(route('roles.forceDelete', $role->id))->assertStatus(405);
    $role->forceDelete();

    $log = file_get_contents(storage_path('logs/laravel-'.date('Y-m-d').'.log'));
    expect(str_contains($log, 'HTTP 405'))->toBeTrue();
    expect(str_contains($log, 'force-delete'))->toBeTrue();
});

it('does not log 404 (noise)', function () {
    $this->get('/this-route-does-not-exist')->assertNotFound();
    $log = file_get_contents(storage_path('logs/laravel-'.date('Y-m-d').'.log'));
    expect(str_contains($log, 'HTTP 404'))->toBeFalse();
});
