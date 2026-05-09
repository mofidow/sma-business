<?php

use App\Http\Controllers\Sma\Setting;
use Illuminate\Support\Facades\Route;

Route::get('/', [Setting\SettingController::class, 'index'])->name('settings.index');
Route::post('/', [Setting\SettingController::class, 'store'])->name('settings.store');
Route::put('/support', [Setting\SettingController::class, 'supportLinks'])->name('settings.support_links');

Route::get('/mail', [Setting\MailController::class, 'index'])->name('settings.mail');
Route::post('/mail', [Setting\MailController::class, 'store'])->name('settings.mail.store');

Route::get('/payment', [Setting\PaymentController::class, 'index'])->name('settings.payment');
Route::post('/payment', [Setting\PaymentController::class, 'store'])->name('settings.payment.store');

Route::get('/barcode', [Setting\ScaleBarcodeController::class, 'index'])->name('settings.barcode');
Route::post('/barcode', [Setting\ScaleBarcodeController::class, 'store'])->name('settings.barcode.store');

Route::get('/v3/import', [Setting\V3ImportController::class, 'index'])->name('settings.import');
Route::post('/v3/import/test', [Setting\V3ImportController::class, 'testConnection'])->name('settings.import.test');
Route::post('/v3/import/process', [Setting\V3ImportController::class, 'processImport'])->name('settings.import.process');
Route::delete('/stores/{store}/logo', [Setting\StoreController::class, 'destroyLogo'])->name('stores.logo.destroy');

Route::resources([
    'taxes'         => Setting\TaxController::class,
    'stores'        => Setting\StoreController::class,
    'custom_fields' => Setting\CustomFieldController::class,
]);
