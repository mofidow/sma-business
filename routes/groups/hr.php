<?php

use App\Http\Controllers\Sma\Hr;
use Illuminate\Support\Facades\Route;

Route::post('leaves/{leave}/approve', [Hr\LeaveController::class, 'approve'])->name('leaves.approve');
Route::post('claims/{claim}/approve', [Hr\ClaimController::class, 'approve'])->name('claims.approve');
Route::post('attendances/bulk', [Hr\AttendanceController::class, 'bulkStore'])->name('attendances.bulk');
Route::post('payrolls/{payroll}/mark-paid', [Hr\PayrollController::class, 'markPaid'])->name('payrolls.mark-paid');

Route::resources(['leave-types' => Hr\LeaveTypeController::class]);

Route::extendedResources([
    'claims'      => Hr\ClaimController::class,
    'payrolls'    => Hr\PayrollController::class,
    'employees'   => Hr\EmployeeController::class,
    'attendances' => Hr\AttendanceController::class,
]);

Route::resource('leaves', Hr\LeaveController::class, ['parameters' => ['leaves' => 'leave']]);
Route::put('leaves/{leave}/restore', [Hr\LeaveController::class, 'restore'])->name('leaves.restore');
Route::delete('leaves/delete/many', [Hr\LeaveController::class, 'destroyMany'])->name('leaves.destroy.many');
Route::delete('leaves/{leave}/permanently', [Hr\LeaveController::class, 'destroyPermanently'])->name('leaves.destroy.permanently');
