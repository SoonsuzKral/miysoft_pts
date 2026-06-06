<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $companies = DB::table('companies')->where('status', 'active')->pluck('id');
    foreach ($companies as $companyId) {
        \App\Jobs\ConflictDetectionJob::dispatch($companyId, today()->toDateString());
    }
})->dailyAt('06:00')->name('shift-conflict-detection')->withoutOverlapping();
