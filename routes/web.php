<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'installed'])->group(function () {
    Route::get('/home', function () {
        return get_module('shop')
            ? to_route('shop.home')
            : redirect('/admin');
    })->name('home');

    Route::redirect('/dashboard', '/admin/dashboard');
    Route::prefix('admin')->group(base_path('routes/groups/sma.php'));
    Route::prefix('views')->group(base_path('routes/groups/public.php'));
    Route::get('/lang/{language}', [App\Http\Controllers\Sma\AjaxController::class, 'langFile'])->name('lang.file');

    // Command Routes (Please comment out again after use)
    // Route::prefix('commands')->group(base_path('routes/groups/commands.php'));

    // Payment Gateway Routes
    Route::prefix('payment_gateways')->as('payment_gateways.')
        ->group(base_path('routes/groups/payments.php'));

    if (get_module('pos')) {
        Route::prefix('pos')->group(base_path('routes/groups/pos.php'));

        Route::prefix('menu')->group(function () {
            Route::get('/{token}', [App\Http\Controllers\Sma\Pos\QrMenuController::class, 'show'])->name('qr-menu.show');
            Route::get('/{token}/products/{category}', [App\Http\Controllers\Sma\Pos\QrMenuController::class, 'products'])->name('qr-menu.products');
            Route::post('/{token}', [App\Http\Controllers\Sma\Pos\QrMenuController::class, 'store'])->name('qr-menu.store');
            Route::get('/{token}/confirmed', [App\Http\Controllers\Sma\Pos\QrMenuController::class, 'confirmed'])->name('qr-menu.confirmed');
        });
    }
    if (! get_module('shop')) {
        Route::redirect('/', '/admin');
    }
});
