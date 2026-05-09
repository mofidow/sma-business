<?php

use App\Http\Controllers\Sma\Report;
use Illuminate\Support\Facades\Route;

Route::get('/sales', [Report\SaleController::class, 'index'])->name('sales.report');
Route::get('/sales/{id}', [Report\SaleController::class, 'show'])->name('sales.report.show');

Route::get('daily/{month?}/{year?}', [Report\SaleController::class, 'dailySales'])->name('daily_sales.report');
Route::get('monthly/{year?}', [Report\SaleController::class, 'monthlySales'])->name('monthly_sales.report');

Route::get('/payments', [Report\PaymentController::class, 'index'])->name('payments.report');
Route::get('/payments/{id}', [Report\PaymentController::class, 'show'])->name('payments.report.show');

Route::get('/expenses', [Report\ExpenseController::class, 'index'])->name('expenses.report');
Route::get('/expenses/{id}', [Report\ExpenseController::class, 'show'])->name('expenses.report.show');

Route::get('/income', [Report\IncomeController::class, 'index'])->name('income.report');
Route::get('/income/{id}', [Report\IncomeController::class, 'show'])->name('income.report.show');

Route::get('/purchases', [Report\PurchaseController::class, 'index'])->name('purchases.report');
Route::get('/purchases/{id}', [Report\PurchaseController::class, 'show'])->name('purchases.report.show');

Route::get('/brands', Report\BrandController::class)->name('brands.report');
Route::get('/products', Report\ProductController::class)->name('products.report');
Route::get('/categories', Report\CategoryController::class)->name('categories.report');

Route::get('/transfers', [Report\TransferController::class, 'index'])->name('transfers.report');
Route::get('/transfers/{id}', [Report\TransferController::class, 'show'])->name('transfers.report.show');

Route::get('/adjustments', [Report\AdjustmentController::class, 'index'])->name('adjustments.report');
Route::get('/adjustments/{id}', [Report\AdjustmentController::class, 'show'])->name('adjustments.report.show');

Route::get('/return_orders', [Report\ReturnOrderController::class, 'index'])->name('return_orders.report');
Route::get('/return_orders/{id}', [Report\ReturnOrderController::class, 'show'])->name('return_orders.report.show');

Route::get('/staff', Report\StaffController::class)->name('staff.report');
Route::get('/customers', Report\CustomerController::class)->name('customers.report');
Route::get('/suppliers', Report\SupplierController::class)->name('suppliers.report');
Route::get('/activities', Report\ActivityController::class)->name('activities.report');

Route::get('/profit-loss', Report\ProfitLossController::class)->name('profit_loss.report');
Route::get('/inventory-accounting', Report\InventoryAccountingController::class)->name('inventory_accounting.report');

Route::get('/credits', [Report\CreditController::class, 'index'])->name('credits.report');
