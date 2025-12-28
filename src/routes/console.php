<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Modules\Currency\Jobs\UpdateCurrencyRatesJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily exchange rate updates
Schedule::call(function () {
    UpdateCurrencyRatesJob::dispatch();
})->dailyAt(config('currency.sync.schedule_time', '00:00'))
  ->name('currency:update-rates')
  ->description('Update exchange rates from FreeCurrencyAPI')
  ->withoutOverlapping()
  ->onOneServer();
