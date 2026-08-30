<?php

use App\Models\Role;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

beforeEach(fn () => $this->seed());

it('logs role force-delete via observer', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $role = Role::create(['name' => 'tmp_audit_fd', 'guard_name' => 'web']);
    $role->delete();
    $role->forceDelete();
    expect(Activity::where('description', 'role_force_deleted')->where('subject_id', $role->id)->exists())->toBeTrue();
});

it('logs user force-delete via observer', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $u = User::create(['name' => 'Tmp', 'username' => 'tmp_fd', 'email' => 'tmp_fd@example.com', 'password' => bcrypt('Secret@123456')]);
    $u->delete();
    $u->forceDelete();
    expect(Activity::where('description', 'user_force_deleted')->where('subject_id', $u->id)->exists())->toBeTrue();
});
