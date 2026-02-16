<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('messages:process-scheduled')->everyMinute();
Schedule::command('messages:sync-queued')->everyMinute();
Schedule::command('reminders:process')->everyMinute();
Schedule::command('social:process-scheduled')->everyMinute();
Schedule::command('beep-calls:process')->everyMinute();
