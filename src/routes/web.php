<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AttendanceRequestController as AdminAttendanceRequestController;
use App\Http\Controllers\StampRequestRouter;
use Illuminate\Http\Request;


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

Route::middleware('auth:web')->group(function () {

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

});

Route::middleware('auth.user_or_admin')->group(function () {

    Route::get('/stamp_correction_request/list', [StampRequestRouter::class, 'index'])
        ->name('stamp_correction_request.index');

    Route::get('/stamp_correction_request/show/{id}', [StampRequestRouter::class, 'show'])
        ->name('stamp_correction_request.show');

    Route::post('/stamp_correction_request/approve/{id}', [StampRequestRouter::class, 'approve'])
        ->name('stamp_correction_request.approve');
});