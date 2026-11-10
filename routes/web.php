<?php

use App\Http\Controllers\paymentController;
use Illuminate\Support\Facades\Route;

Route::get('/payment', [paymentController::class, 'index'])->name('demo-payment.index');
Route::post('/payment/pay', [paymentController::class, 'pay'])->name('demo-payment.pay');
Route::post('/payment/check', [paymentController::class, 'check'])->name('demo-payment.check');