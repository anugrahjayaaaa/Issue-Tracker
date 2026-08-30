<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('exports audit log as CSV respecting the action filter', function () {
    Activity::create(['log_name' => 'default', 'description' => 'login_success', 'created_at' => now()]);

    $u = User::where('email', 'admin@laravel-base.local')->first();
    $resp = $this->actingAs($u)->get(route('audit.export', ['action' => 'login_success']));

    $resp->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=utf-8');
    $body = $resp->streamedContent();
    expect($body)->toContain('time,action,causer,subject_type,subject_id,ip,user_agent');
    expect($body)->toContain('login_success');
    expect($body)->not->toContain('permission_created');
});

it('denies audit export without audit.view', function () {
    $noPerms = User::create([
        'name' => 'No Perms', 'username' => 'noperms2',
        'email' => 'noperms2@example.com', 'password' => bcrypt('password'),
    ]);
    $this->actingAs($noPerms)->get(route('audit.export'))->assertForbidden();
});
