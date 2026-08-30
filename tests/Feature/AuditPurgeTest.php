<?php

use App\Console\Commands\AuditPurgeCommand;
use Spatie\Activitylog\Models\Activity;

beforeEach(fn () => $this->seed());

it('purges activity_log rows older than the retention window', function () {
    // seed two rows: one old, one recent
    $old = Activity::create(['log_name' => 'default', 'description' => 'old_event', 'created_at' => now()->subDays(400)]);
    $new = Activity::create(['log_name' => 'default', 'description' => 'new_event', 'created_at' => now()->subDays(1)]);

    $this->artisan(AuditPurgeCommand::class, ['--days' => 365])
        ->assertSuccessful()
        ->expectsOutputToContain('Purged 1 audit');

    expect(Activity::find($old->id))->toBeNull();
    expect(Activity::find($new->id))->not->toBeNull();
});

it('refuses non-positive retention', function () {
    $this->artisan(AuditPurgeCommand::class, ['--days' => 0])
        ->assertFailed();
});
