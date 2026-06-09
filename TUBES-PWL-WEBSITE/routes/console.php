<?php

use App\Console\Commands\ExpirePassedOrders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ──────────────────────────────────────────────────────────────────────────────
// Scheduler: Auto-expire paid/pending orders whose visit date has passed.
// Runs every day at 00:05 (5 minutes past midnight) so it catches rollovers.
// ──────────────────────────────────────────────────────────────────────────────
Schedule::command(ExpirePassedOrders::class)
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/expire-orders.log'));

