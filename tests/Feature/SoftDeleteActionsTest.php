<?php

use App\Models\Role;
use App\Models\User;

beforeEach(fn () => $this->seed());

it('restores a soft-deleted role via POST', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $role = Role::create(['name' => 'tmp_restore', 'guard_name' => 'web']);
    $role->delete();
    $this->post(route('roles.restore', $role->id))->assertRedirect(route('roles.index'));
    expect(Role::find($role->id))->not->toBeNull();
});

it('permanently deletes a soft-deleted role', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $role = Role::create(['name' => 'tmp_force', 'guard_name' => 'web']);
    $role->delete();
    $this->post(route('roles.forceDelete', $role->id))->assertRedirect(route('roles.index'));
    expect(Role::withTrashed()->find($role->id))->toBeNull();
});

it('refuses to permanently delete super-admin', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $sa = Role::where('name', 'super-admin')->first();
    $this->post(route('roles.forceDelete', $sa->id))->assertRedirect();
    expect(Role::withTrashed()->find($sa->id))->not->toBeNull();
});

it('GET to restore route is rejected (browser-safe)', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $role = Role::create(['name' => 'tmp_get', 'guard_name' => 'web']);
    $role->delete();
    $this->get(route('roles.restore', $role->id))->assertStatus(405);
});
