<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;

beforeEach(fn () => $this->seed());

it('logs in with email', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->post(route('login.store'), ['identifier' => $u->email, 'password' => 'Admin@base12345'])
        ->assertRedirect(route('dashboard'));
    expect(auth()->check())->toBeTrue();
});

it('logs in with username', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->post(route('login.store'), ['identifier' => $u->username, 'password' => 'Admin@base12345'])
        ->assertRedirect(route('dashboard'));
    expect(auth()->check())->toBeTrue();
});

it('rejects phone login (removed)', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    // use a fake phone-like identifier; should NOT resolve to a user
    $this->post(route('login.store'), ['identifier' => '+62812345678', 'password' => 'Admin@base12345'])
        ->assertSessionHasErrors('identifier');
});

it('shows password link on login page', function () {
    $html = $this->get(route('login'))->assertOk()->getContent();
    expect(str_contains($html, (string) route('password.request')))->toBeTrue();
    expect(str_contains($html, 'Forgot your password?'))->toBeTrue();
});

it('forgot password stores token and reset works', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->post(route('password.email'), ['email' => $u->email])
        ->assertSessionHas('status');

    $row = DB::table('password_reset_tokens')->where('email', $u->email)->first();
    expect($row)->not->toBeNull();

    // simulate clicking reset link: extract token via broker
    $token = app('auth.password.broker')->createToken($u);

    $this->post(route('password.store'), [
        'token' => $token,
        'email' => $u->email,
        'password' => 'newpassword1',
        'password_confirmation' => 'newpassword1',
    ])->assertRedirect(route('login'));

    expect(Hash::check('newpassword1', $u->fresh()->password))->toBeTrue();

    // old password no longer works
    $this->post(route('login.store'), ['identifier' => $u->email, 'password' => 'Admin@base12345'])
        ->assertSessionHasErrors();
});

it('locks account after 5 fails regardless of IP rotation', function () {
    $email = 'admin@laravel-base.local';

    // 5 wrong attempts from IP A
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login.store'), ['identifier' => $email, 'password' => 'wrong'], [], ['REMOTE_ADDR' => '10.0.0.1']);
    }
    // 6th attempt from a DIFFERENT IP must still be blocked (account-centric limiter)
    $this->post(route('login.store'), ['identifier' => $email, 'password' => 'wrong'], [], ['REMOTE_ADDR' => '10.0.0.99'])
        ->assertSessionHasErrors('identifier');

    // and the account itself is locked in DB
    expect(User::where('email', $email)->first()->fresh()->isLocked())->toBeTrue();
});

it('logs password_reset_request to audit on reset link send', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->post(route('password.email'), ['email' => $u->email])->assertSessionHas('status');

    $logged = Activity::where('log_name', 'default')
        ->where('description', 'password_reset_request')
        ->where('properties->email', $u->email)
        ->exists();
    expect($logged)->toBeTrue();
});
