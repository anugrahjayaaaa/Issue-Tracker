<?php

namespace App\Console\Commands;

use App\Services\LicenseService;
use Illuminate\Console\Command;

class LicenseActivateCommand extends Command
{
    protected $signature = 'license:activate {key} {--issued-to=}';

    protected $description = 'Activate a license key on this instance';

    public function handle(): int
    {
        $ok = LicenseService::activate($this->argument('key'), $this->option('issued-to'));

        if (! $ok) {
            $this->error('Invalid, expired, or revoked license key.');

            return self::FAILURE;
        }

        $this->info('License activated: '.LicenseService::status()
            .' ('.($this->daysLeftText()).')');

        return self::SUCCESS;
    }

    private function daysLeftText(): string
    {
        $d = LicenseService::daysLeft();

        return $d === null ? 'lifetime' : $d.' days left';
    }
}
