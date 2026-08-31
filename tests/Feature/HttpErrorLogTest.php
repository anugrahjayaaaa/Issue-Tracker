<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;

beforeEach(fn () => $this->seed());

// ponytail: resolve the real log file the active channel writes (single OR daily) instead
// of assuming a dated daily file exists — channel-agnostic, no crash if missing.
function readAppLog(): string
{
    foreach (Log::getLogger()->getHandlers() as $handler) {
        if ($handler instanceof StreamHandler && $url = $handler->getUrl()) {
            return file_exists($url) ? (string) file_get_contents($url) : '';
        }
    }

    return '';
}

it('logs 405 (wrong HTTP method) via middleware', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $role = Role::create(['name' => 'logme', 'guard_name' => 'web']);
    $role->delete();
    $this->get(route('roles.forceDelete', $role->id))->assertStatus(405);
    $role->forceDelete();

    $log = readAppLog();
    expect(str_contains($log, 'HTTP 405'))->toBeTrue();
    expect(str_contains($log, 'force-delete'))->toBeTrue();
});

it('does not log 404 (noise)', function () {
    $this->get('/this-route-does-not-exist')->assertNotFound();

    expect(str_contains(readAppLog(), 'HTTP 404'))->toBeFalse();
});
