<?php

use App\Http\Controllers\BakongController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BakongController::class, 'index'])->name('products.index');
Route::post('/payment/generate/{product}', [BakongController::class, 'generate'])->name('payment.generate');
Route::post('/payment/check', [BakongController::class, 'check'])->name('payment.check');
Route::get('/payment/{payment:reference}/success', [BakongController::class, 'success'])->name('payment.success');
