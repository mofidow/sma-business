<?php

use App\Http\Controllers\Sma\Pos;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'language', 'auth:sanctum', config('jetstream.auth_session'),
])->group(function () {
    Route::get('/view', [Pos\PosController::class, 'customerView'])->name('customer.view');
    Route::post('/verify', [Pos\PosController::class, 'verifyPinCode'])->name('verify.pin_code');
    // POS Routes
    Route::middleware('store')->group(function () {
        Route::post('/pos/register', [Pos\PosRegisterController::class, 'store'])->name('pos.register');

        // Restaurant Routes
        Route::name('pos.')->group(function () {
            Route::get('tables/available', [Pos\TableController::class, 'available'])->name('tables.available');
            Route::post('tables/available', [Pos\TableController::class, 'available'])->name('table-status');

            Route::extendedResources([
                'halls'  => Pos\HallController::class,
                'tables' => Pos\TableController::class,
            ]);

            Route::get('qr-codes', [Pos\QrCodeController::class, 'index'])->name('qr-codes.index');
            Route::post('qr-codes/{table}/regenerate', [Pos\QrCodeController::class, 'regenerate'])->name('qr-codes.regenerate');
        });

        Route::middleware('register')->group(function () {
            Route::get('/', [Pos\PosController::class, 'index'])->name('pos');
            Route::get('/orders', [Pos\PosController::class, 'orders'])->name('pos.orders');
            Route::post('/orders', [Pos\OrderController::class, 'store'])->name('pos.orders.store');
            Route::delete('/orders/{order}', [Pos\OrderController::class, 'destroy'])->name('pos.orders.destroy');
            Route::post('/orders/check_reference', [Pos\OrderController::class, 'checkReference'])->name('pos.orders.checkReference');

            Route::get('/qr-orders', [Pos\PosController::class, 'qrOrders'])->name('pos.qr-orders');
            Route::get('/qr-orders/count', [Pos\PosController::class, 'qrOrdersCount'])->name('pos.qr-orders.count');
            Route::post('/qr-orders/{order}/accept', [Pos\PosController::class, 'acceptQrOrder'])->name('pos.qr-orders.accept');

            Route::get('/product/{product}', [Pos\PosController::class, 'product'])->name('pos.product');
            Route::get('/products/{category}', [Pos\PosController::class, 'products'])->name('pos.products');

            Route::get('/register', [Pos\PosRegisterController::class, 'details'])->name('pos.register.details');
            Route::put('/register', [Pos\PosRegisterController::class, 'close'])->name('pos.register.close');
        });
    });

    // POS Admin Routes
    Route::prefix('admin/pos')->group(function () {
        Route::resource('/orders', Pos\OrderController::class)->only(['index', 'destroy']);
        Route::resource('/register', Pos\RegisterController::class)->except(['create', 'edit']);

        Route::get('/registers', [Pos\RegisterReportController::class, 'index'])->name('registers.report');
        Route::get('/registers/{id}', [Pos\RegisterReportController::class, 'show'])->name('registers.report.show');

        Route::get('/settings', [Pos\SettingController::class, 'index'])->name('settings.pos');
        Route::post('/settings', [Pos\SettingController::class, 'store'])->name('settings.pos.store');
    });
});
