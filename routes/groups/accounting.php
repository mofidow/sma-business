<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sma\Accounting;

Route::get('accounts/{account}/statement', [Accounting\AccountController::class, 'statement'])->name('accounts.statement');

Route::resources([
    'accounts'             => Accounting\AccountController::class,
    'account-types'        => Accounting\AccountTypeController::class,
    'account-transactions' => Accounting\AccountTransactionController::class,
    'account-transfers'    => Accounting\AccountTransferController::class,
    'assets'               => Accounting\AssetController::class,
    'asset-categories'     => Accounting\AssetCategoryController::class,
    'asset-allocations'    => Accounting\AssetAllocationController::class,
    'asset-maintenances'   => Accounting\AssetMaintenanceController::class,
]);
