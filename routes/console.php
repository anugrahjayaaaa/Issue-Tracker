<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Audit log retention: purge rows older than the configured window (default 365d).
Schedule::command('audit:purge')->daily();

// Telescope entry retention: avoid unbounded growth of telescope_entries.
Schedule::command('telescope:prune')->daily();
