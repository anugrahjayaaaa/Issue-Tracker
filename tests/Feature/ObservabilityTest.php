<?php

use App\Models\User;

beforeEach(function () {
    $this->seed();
    $this->user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($this->user);
});

it('applies security headers', function () {
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Content-Security-Policy');
});

it('health endpoint responds 200', function () {
    $this->get('/up')->assertOk()->assertJson(['status' => 'ok']);
});
