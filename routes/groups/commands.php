<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/migrate', function () {
    Artisan::call('migrate --force');

    return response()->json(['message' => Artisan::output()]);
});

Route::get('/db_backup', function () {
    Artisan::call('backup:run --only-db');

    return response()->json(['message' => Artisan::output()]);
});

Route::get('/storage_link', function () {
    Artisan::call('storage:link --force');

    return response()->json(['message' => Artisan::output()]);
});
