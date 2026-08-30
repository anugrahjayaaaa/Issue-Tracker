<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

it('switches sidebar labels to indonesian when locale is id', function () {
    $admin = User::where('email', 'admin@laravel-base.local')->first()
        ?? User::factory()->create(['email' => 'admin@laravel-base.local', 'username' => 'admin']);
    Role::findOrCreate('admin', 'web')->syncPermissions(
        Permission::pluck('id')->toArray()
    );
    $admin->syncRoles(['admin']);

    // default (en) — sidebar header + dashboard + language switcher are always visible
    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSee('Main Menu')
        ->assertSee('Dashboard')
        ->assertSee('Language');

    // switch to id
    $this->actingAs($admin)
        ->post(route('locale.update'), ['locale' => 'id'])
        ->assertRedirect();

    $this->actingAs($admin)
        ->withSession(['locale' => 'id'])
        ->get(route('dashboard'))
        ->assertSee('Menu Utama')
        ->assertSee('Dasbor')
        ->assertSee('Bahasa');
});
