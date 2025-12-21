<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AttendanceRequestController as AdminAttendanceRequestController;

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', function () {
        \Illuminate\Support\Facades\Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    })->name('admin.logout');

    Route::middleware(['auth:admin', 'can:isAdmin'])->name('admin.')->group(function () {
        Route::get('/staff/list', [UserController::class, 'index'])->name('user.index');

        Route::prefix('attendance')->group(function () {
            Route::get('/list', [AdminAttendanceController::class, 'index'])->name('attendance.list');
            Route::get('/staff/{id}', [AdminAttendanceController::class, 'staffMonthly'])->name('attendance.staff');
            Route::get('/staff/{id}/csv', [AdminAttendanceController::class, 'exportCsv'])->name('attendance.staff.csv');
            Route::get('/create', [AdminAttendanceController::class, 'create'])->name('attendance.create');
            Route::post('/store', [AdminAttendanceController::class, 'store'])->name('attendance.store');
            Route::get('/{attendance}', [AdminAttendanceController::class, 'show'])->name('attendance.show');
            Route::put('/{attendance}', [AdminAttendanceController::class, 'update'])->name('attendance.update');
        });
    });
});