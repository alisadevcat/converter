<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Currency\Controllers\CurrencyConverterController;

Route::get('/converter', [CurrencyConverterController::class, 'index'])->name('converter.index');
Route::post('/converter', [CurrencyConverterController::class, 'convert'])->name('converter.convert');

