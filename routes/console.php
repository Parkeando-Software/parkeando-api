<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');







// cron
Schedule::command('notifications:expire')
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/notifications-expire.log'));

// (Opcional) “latido” para verificar que el scheduler sí lee este archivo:
Schedule::call(function () {
    \Log::info('scheduler: tick');
})->everyMinute();




