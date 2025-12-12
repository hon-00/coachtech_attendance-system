<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('auth')->group(function () {

    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'create'])
            ->name('attendance.create');

        Route::post('/clock-in',  [AttendanceController::class, 'clockIn'])
            ->name('attendance.clockIn');
        Route::post('/clock-out', [AttendanceController::class, 'clockOut'])
            ->name('attendance.clockOut');
        Route::post('/break-in',  [AttendanceController::class, 'breakIn'])
            ->name('attendance.breakIn');
        Route::post('/break-out', [AttendanceController::class, 'breakOut'])
            ->name('attendance.breakOut');

        Route::get('/list', [AttendanceController::class, 'index'])
            ->name('attendance.list');

        Route::get('/detail/{id}', [AttendanceController::class, 'show'])
            ->name('attendance.detail');
        Route::post('/request/{attendanceId}', [AttendanceRequestController::class, 'store'])
            ->name('attendance.request.store');
    });

    Route::prefix('stamp_correction_request')->group(function () {
        Route::get('/list', [AttendanceRequestController::class, 'index'])
            ->name('attendance_request.index');
    });

});

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])
        ->name('admin.login');

    Route::post('/login', [AdminLoginController::class, 'login'])
        ->name('admin.login.submit');

    Route::post('/logout', function () {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    })->name('admin.logout');
});

Route::middleware(['auth:admin', 'can:isAdmin'])->prefix('admin')->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('admin.attendance.list');

    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])
        ->name('admin.attendance.show');

    Route::put('/attendance/{id}', [AdminAttendanceController::class, 'update'])
        ->name('admin.attendance.update');
});