<?php

use App\Http\Controllers\BackupController;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily automatic backup for setups that run the scheduler (php artisan schedule:work,
// or schedule:run every minute from cron / Task Scheduler). Without a scheduler, the
// dashboard triggers the same check. It only backs up when the last copy is a day old.
Schedule::call(fn () => BackupController::backupIfDue())->hourly()->name('auto-backup')->withoutOverlapping();
