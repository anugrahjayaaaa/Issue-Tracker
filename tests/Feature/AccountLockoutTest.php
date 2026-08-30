<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(fn () => $this->seed());

it('locks the account after 5 failed login attempts', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $u->update(['password' => Hash::make('RightPass@12345')]); // known good pw

    for ($i = 1; $i <= 5; $i++) {
        $r = $this->post(route('login.store'), [
            'identifier' => $u->email,
            'password' => 'WrongPass@00000',
        ]);
        if ($i < 5) {
            $r->assertSessionHasErrors('identifier'); // still "bad credentials"
        }
    }

    expect($u->fresh()->isLocked())->toBeTrue();
});

it('rejects login with correct password while locked', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $u->update([
        'password' => Hash::make('RightPass@12345'),
        'locked_until' => now()->addMinutes(10),
    ]);

    $this->post(route('login.store'), [
        'identifier' => $u->email,
        'password' => 'RightPass@12345',
    ])->assertSessionHasErrors('identifier');

    expect($u->fresh()->isLocked())->toBeTrue();
});

it('auto-unlocks after the lock window passes', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $u->update([
        'password' => Hash::make('RightPass@12345'),
        'locked_until' => now()->subMinute(), // expired
    ]);

    $this->post(route('login.store'), [
        'identifier' => $u->email,
        'password' => 'RightPass@12345',
    ])->assertRedirect(route('dashboard'));

    expect($u->fresh()->locked_until)->toBeNull();
});
