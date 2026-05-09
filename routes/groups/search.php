<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sma\Search\SearchController;

Route::post('/sale', [SearchController::class, 'sale'])->name('search.sale');
Route::post('/fields', [SearchController::class, 'fields'])->name('search.fields');
Route::post('/serials', [SearchController::class, 'serials'])->name('search.serials');
Route::post('/products', [SearchController::class, 'products'])->name('search.products');
Route::post('/purchase', [SearchController::class, 'purchase'])->name('search.purchase');
Route::post('/countries', [SearchController::class, 'countries'])->name('search.countries');
Route::post('/customers', [SearchController::class, 'customers'])->name('search.customers');
Route::post('/suppliers', [SearchController::class, 'suppliers'])->name('search.suppliers');
Route::post('/categories', [SearchController::class, 'categories'])->name('search.categories');
