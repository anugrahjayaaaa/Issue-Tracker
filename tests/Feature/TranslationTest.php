<?php

use App\Http\Middleware\SetLocale;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Spatie\TranslationLoader\LanguageLine;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('lists translations for admin', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($u)
        ->get(route('translations.index'))
        ->assertOk()
        ->assertSee('messages') // group column
        ->assertSee('users');    // key column (rendered in <code>)
});

it('updates a translation value and reflects in __()', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $line = LanguageLine::where('group', 'messages')->where('key', 'users')->first();

    $this->actingAs($u)
        ->put(route('translations.update', $line), ['en' => 'Users', 'id' => 'Pengguna Edit'])
        ->assertRedirect(route('translations.index'));

    $line->refresh();
    expect($line->text['id'])->toBe('Pengguna Edit');

    session(['locale' => 'id']);
    $request = request()->create('/dashboard');
    app(SetLocale::class)->handle($request, function ($req) {
        expect(__('messages.users'))->toBe('Pengguna Edit');
    });
});

it('denies translations to unauthorized user', function () {
    $user = User::factory()->create(['username' => 'unauth'.time()]);
    $this->actingAs($user)
        ->get(route('translations.index'))
        ->assertForbidden();
});

it('blocks a translation.view holder when the flag is off (404)', function () {
    $role = Role::findOrCreate('translator', 'web');
    $role->syncPermissions(['translation.view']);
    $u = User::factory()->create(['username' => 'translator'.time()]);
    $u->assignRole($role);

    // flag on -> allowed
    $this->actingAs($u)->get(route('translations.index'))->assertOk();

    // flag off -> 404 (fail-closed), not a permission error
    Feature::deactivate('translations');
    $this->actingAs($u)->get(route('translations.index'))->assertNotFound();
});

it('lets a feature.manage holder reach translations while the flag is off', function () {
    // ponytail: kill-switch — disabled flag blocks everyone, including managers.
    Feature::deactivate('translations');
    $u = User::where('email', 'admin@laravel-base.local')->first(); // holds feature.manage

    $this->actingAs($u)->get(route('translations.index'))->assertNotFound();
});

it('search translation by group/key/text', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($u)
        ->get(route('translations.index', ['q' => 'users']))
        ->assertOk()
        ->assertSee('users');
    $this->actingAs($u)
        ->get(route('translations.index', ['q' => 'nonexistent_xyz_123']))
        ->assertOk()
        ->assertSee(ui('no_translations'));
});

it('sorts translations table by column', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($u)
        ->get(route('translations.index', ['sort' => 'key', 'dir' => 'desc']))
        ->assertOk()
        ->assertSee('users');
});
