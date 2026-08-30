<?php

use App\Models\Role;
use App\Models\User;

beforeEach(fn () => $this->seed());

// ---- Validation (negative) ----

it('UserStoreRequest rejects duplicate email', function () {
    $admin = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Dup', 'username' => 'dup1', 'email' => $admin->email,
            'password' => 'Password@123', 'password_confirmation' => 'Password@123',
        ])
        ->assertSessionHasErrors('email');
});

it('UserStoreRequest rejects password below 12 chars', function () {
    $admin = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Short', 'username' => 'short1', 'email' => 'short1@x.com',
            'password' => 'abc', 'password_confirmation' => 'abc',
        ])
        ->assertSessionHasErrors('password');
});

it('UserStoreRequest rejects unconfirmed password', function () {
    $admin = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Conf', 'username' => 'conf1', 'email' => 'conf1@x.com',
            'password' => 'Password@123', 'password_confirmation' => 'Different@123',
        ])
        ->assertSessionHasErrors('password');
});

it('RoleStoreRequest rejects duplicate name', function () {
    $admin = User::where('email', 'admin@laravel-base.local')->first();
    $existing = Role::where('name', 'admin')->first();
    $this->actingAs($admin)
        ->post(route('roles.store'), ['name' => $existing->name])
        ->assertSessionHasErrors('name');
});

it('PermissionStoreRequest rejects duplicate name', function () {
    $admin = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($admin)
        ->post(route('permissions.store'), ['name' => 'user.view'])
        ->assertSessionHasErrors('name');
});

// ---- Authorization (gate) ----

it('denies user creation for staff (no user.create permission)', function () {
    $staffRole = Role::where('name', 'staff')->first();
    $staff = User::factory()->create(['username' => uniqid('staff')]);
    $staff->assignRole($staffRole); // only user.view, audit.view

    $this->actingAs($staff)
        ->post(route('users.store'), [
            'name' => 'X', 'username' => 'x1', 'email' => 'x1@x.com',
            'password' => 'Password@123', 'password_confirmation' => 'Password@123',
        ])
        ->assertForbidden(); // 403 from Form Request authorize() OR route can:
});

it('denies role creation for staff', function () {
    $staffRole = Role::where('name', 'staff')->first();
    $staff = User::factory()->create(['username' => uniqid('staff')]);
    $staff->assignRole($staffRole);

    $this->actingAs($staff)
        ->post(route('roles.store'), ['name' => 'try-role'])
        ->assertForbidden();
});
