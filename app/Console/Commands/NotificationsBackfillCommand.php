<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\AuditNotification;
use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class NotificationsBackfillCommand extends Command
{
    protected $signature = 'notifications:backfill';

    protected $description = 'Copy historical auth activity_log rows into the native notifications table (idempotent).';

    public function handle(): int
    {
        $authActions = ['login_success', 'logout', 'login_failed', 'password_reset', 'email_verified'];
        $count = 0;

        Activity::whereIn('description', $authActions)
            ->whereNotNull('causer_id')
            ->with('causer')
            ->chunkById(200, function ($rows) use (&$count) {
                foreach ($rows as $a) {
                    /** @var User|null $user */
                    $user = $a->causer;
                    if (! $user) {
                        continue;
                    }
                    // idempotent: skip if an identical notification already exists
                    $exists = $user->notifications()
                        ->where('type', AuditNotification::class)
                        ->where('data->action', $a->description)
                        ->where('created_at', $a->created_at)
                        ->exists();
                    if ($exists) {
                        continue;
                    }
                    $user->notify(new AuditNotification($a->description, $a->properties['ip'] ?? null));
                    $count++;
                }
            });

        $this->info("Backfilled {$count} notification(s).");

        return self::SUCCESS;
    }
}
