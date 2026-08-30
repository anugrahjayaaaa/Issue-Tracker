<?php

use App\Models\User;

beforeEach(function () {
    $this->seed();
    $this->user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($this->user);
});

it('lists tokens page', function () {
    $this->get(route('api-tokens.index'))->assertOk();
});

it('creates and shows plain token once', function () {
    $this->post(route('api-tokens.store'), ['name' => 'test-device'])
        ->assertRedirect(route('api-tokens.index'))
        ->assertSessionHas('new_token');
    expect($this->user->fresh()->tokens()->count())->toBe(1);
});

it('revokes a token', function () {
    $token = $this->user->createToken('x')->accessToken;
    $id = $this->user->tokens()->first()->id;
    $this->delete(route('api-tokens.destroy', $id))->assertRedirect();
    expect($this->user->fresh()->tokens()->count())->toBe(0);
});
