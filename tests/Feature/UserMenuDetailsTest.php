<?php

use App\Models\User;

beforeEach(fn () => $this->seed());

it('user menu uses native details (works without Bootstrap JS)', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $html = $this->get(route('dashboard'))->assertOk()->getContent();
    expect(str_contains($html, 'class="nav-item dropdown user-menu"'))->toBeTrue();
    expect(str_contains($html, '<summary'))->toBeTrue();
    expect(str_contains($html, (string) route('logout')))->toBeTrue();
    // menu items present
    expect(str_contains($html, 'Profile'))->toBeTrue();
    expect(str_contains($html, 'Logout'))->toBeTrue();
});
