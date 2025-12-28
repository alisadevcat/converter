<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule currency rates sync to run daily
Schedule::command('app:currency-rates-sync')
    ->daily()
    ->at('00:00')
    ->timezone('UTC');
