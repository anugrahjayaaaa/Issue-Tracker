<?php

use App\Models\Role;
use App\Models\User;

beforeEach(fn () => $this->seed());

it('super-admin can restore and force-delete (specific perms seeded)', function () {
    $sa = User::where('email', 'admin@laravel-base.local')->first();
    expect($sa->can('role.restore'))->toBeTrue();
    expect($sa->can('role.force-delete'))->toBeTrue();
    expect($sa->can('user.restore'))->toBeTrue();
    expect($sa->can('permission.force-delete'))->toBeTrue();
});

it('staff without restore perm gets 403 on restore route', function () {
    $staff = User::create(['name' => 'Staff', 'username' => 'staff_x', 'email' => 'staff_x@example.com', 'password' => bcrypt('Secret@123456')]);
    $staff->assignRole('staff'); // only user.view, audit.view
    $this->actingAs($staff);
    $role = Role::create(['name' => 'tmp_gate', 'guard_name' => 'web']);
    $role->delete();
    $this->post(route('roles.restore', $role->id))->assertForbidden(); // 403
    $role->forceDelete();
});

it('super-admin restore/forceDelete still works end-to-end', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $role = Role::create(['name' => 'tmp_e2e', 'guard_name' => 'web']);
    $role->delete();
    $this->post(route('roles.restore', $role->id))->assertRedirect();
    expect(Role::find($role->id))->not->toBeNull();
    $role->delete();
    $this->post(route('roles.forceDelete', $role->id))->assertRedirect();
    expect(Role::withTrashed()->find($role->id))->toBeNull();
});
