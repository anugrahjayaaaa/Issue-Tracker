<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class AuditPurgeCommand extends Command
{
    protected $signature = 'audit:purge
        {--days= : Delete activity_log rows older than this many days (default: AUDIT_RETENTION_DAYS or 365)}';

    protected $description = 'Delete old audit (activity_log) rows beyond the retention window';

    public function handle(): int
    {
        // ponytail: retention default via env; safe ceiling so logs can't grow unbounded
        $days = (int) ($this->option('days') ?? env('AUDIT_RETENTION_DAYS', 365));
        if ($days <= 0) {
            $this->error('Retention must be a positive number of days.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);
        $deleted = Activity::where('created_at', '<', $cutoff)->delete();

        $this->info("Purged {$deleted} audit entr".($deleted === 1 ? 'y' : 'ies')." older than {$days} days (before {$cutoff->toDateTimeString()}).");

        return self::SUCCESS;
    }
}
