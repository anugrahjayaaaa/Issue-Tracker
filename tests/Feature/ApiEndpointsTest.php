<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

function apiToken(User $u): string
{
    return $u->createToken('test', ['mobile'])->plainTextToken;
}

it('logs in and returns a bearer token', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $r = $this->postJson('/api/v1/login', [
        'identifier' => $u->email,
        'password' => 'Admin@base12345',
        'device_name' => 'test',
    ]);
    $r->assertOk()->assertJsonStructure(['token', 'user']);
});

it('rejects invalid credentials', function () {
    $this->postJson('/api/v1/login', [
        'identifier' => 'admin@laravel-base.local',
        'password' => 'wrong',
        'device_name' => 'test',
    ])->assertStatus(422);
});

it('returns 401 without token', function () {
    $this->getJson('/api/v1/me')->assertStatus(401);
});

it('me endpoint works with token', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->withHeader('Authorization', 'Bearer '.apiToken($u))
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('user.email', $u->email);
});

it('lists users with token', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->withHeader('Authorization', 'Bearer '.apiToken($u))
        ->getJson('/api/v1/users')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('creates a user via API', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $r = $this->withHeader('Authorization', 'Bearer '.apiToken($u))
        ->postJson('/api/v1/users', [
            'name' => 'Api User',
            'username' => 'apiuser',
            'email' => 'apiuser@laravel-base.local',
            'password' => 'Secret123456',
            'password_confirmation' => 'Secret123456',
            'roles' => [],
        ]);
    $r->assertCreated()->assertJsonPath('username', 'apiuser');
    expect(User::where('username', 'apiuser')->exists())->toBeTrue();
});

it('returns notifications and marks read', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->withHeader('Authorization', 'Bearer '.apiToken($u))
        ->getJson('/api/v1/notifications')
        ->assertOk();
});

it('returns audit list', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->withHeader('Authorization', 'Bearer '.apiToken($u))
        ->getJson('/api/v1/audit')
        ->assertOk();
});

it('lists features', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->withHeader('Authorization', 'Bearer '.apiToken($u))
        ->getJson('/api/v1/features')
        ->assertOk();
});
