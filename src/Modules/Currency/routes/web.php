<?php

use Illuminate\Support\Facades\Route;
use Modules\Currency\Controllers\CurrencyConverterController;

Route::middleware('web')
    ->group(function () {
        Route::get('/converter', [CurrencyConverterController::class, 'index'])
            ->name('converter.index');
    });

