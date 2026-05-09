<?php

use App\Http\Controllers\Sma;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::middleware(['language', 'signed'])->group(function () {
    Route::get('/sale/{id}', [Sma\PublicController::class, 'sale'])->name('sale.show');
    Route::get('/payment/{id}', [Sma\PublicController::class, 'payment'])->name('payment.show');
    Route::get('/purchase/{id}', [Sma\PublicController::class, 'purchase'])->name('purchase.show');
    Route::get('/transfer/{id}', [Sma\PublicController::class, 'transfer'])->name('transfer.show');
    Route::get('/quotation/{id}', [Sma\PublicController::class, 'quotation'])->name('quotation.show');
    Route::get('/return_order/{id}', [Sma\PublicController::class, 'returnOrder'])->name('return_order.show');
});

Route::middleware('language')->group(function () {
    Route::get('/sale/{id}/{hash}', [Sma\PublicController::class, 'sale'])->name('sale.url');
    Route::get('/payment/{id}/{hash}', [Sma\PublicController::class, 'payment'])->name('payment.url');
    Route::get('/purchase/{id}/{hash}', [Sma\PublicController::class, 'purchase'])->name('purchase.url');
    Route::get('/transfer/{id}/{hash}', [Sma\PublicController::class, 'transfer'])->name('transfer.url');
    Route::get('/quotation/{id}/{hash}', [Sma\PublicController::class, 'quotation'])->name('quotation.url');
    Route::post('/quotation/{id}/{hash}/sign', [Sma\PublicController::class, 'signQuotation'])->name('quotation.sign');
    Route::get('/return_order/{id}/{hash}', [Sma\PublicController::class, 'returnOrder'])->name('return_order.url');

    // Public Repair Status Check
    Route::get('/repair-status', [Sma\Repair\RepairStatusController::class, 'index'])->name('repair-status.index');
});
