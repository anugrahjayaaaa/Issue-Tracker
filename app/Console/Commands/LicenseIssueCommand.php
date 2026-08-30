<?php

namespace App\Console\Commands;

use App\Services\LicenseService;
use Illuminate\Console\Command;

class LicenseIssueCommand extends Command
{
    protected $signature = 'license:issue
        {plan : plan slug to issue}
        {--type=manual : recurring|lifetime|manual}
        {--days= : validity in days (omit for lifetime)}';

    protected $description = 'Issue a signed license key (manual/off-market/lifetime)';

    public function handle(): int
    {
        $expiresAt = $this->option('days')
            ? now()->addDays((int) $this->option('days'))->format('Y-m-d H:i:s')
            : null;

        $key = LicenseService::issue($this->argument('plan'), [
            'type' => $this->option('type'),
            'expires_at' => $expiresAt,
        ]);

        $this->info("Issued: $key");

        return self::SUCCESS;
    }
}
