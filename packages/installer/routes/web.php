<?php

use Illuminate\Support\Facades\Route;
use Tecdiary\Installer\Http\Middleware\CanInstall;
use Tecdiary\Installer\Http\Controllers\ModuleController;
use Tecdiary\Installer\Http\Controllers\InstallController;

Route::prefix('install')
    ->middleware('web')
    ->middleware('\\' . CanInstall::class)
    ->group(function () {
        // cache()->forget('sma_modules');
        Route::get('/', ['\\' . InstallController::class, 'index']);
        Route::post('demo', ['\\' . InstallController::class, 'demo']);
        Route::post('save', ['\\' . InstallController::class, 'save']);
        Route::post('check', ['\\' . InstallController::class, 'show']);
        Route::post('finalize', ['\\' . InstallController::class, 'finalize']);
    });

Route::prefix('manage-modules')->middleware('web')->group(function () {
    Route::get('/', ['\\' . ModuleController::class, 'index'])
        ->name('modules')->can('manage-modules');
    Route::post('/', ['\\' . ModuleController::class, 'enable'])
        ->name('modules.enable')->can('manage-modules');
    Route::post('disable', ['\\' . ModuleController::class, 'disable'])
        ->name('modules.disable')->can('manage-modules');
});
