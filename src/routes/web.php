<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AttendanceRequestController as AdminAttendanceRequestController;
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

Route::get('/stamp_correction_request/list', function (\Illuminate\Http\Request $request) {
    if (Auth::guard('admin')->check()) {
        return app(\App\Http\Controllers\Admin\AttendanceRequestController::class)->index($request);
    } elseif (Auth::guard('web')->check()) {
        return app(\App\Http\Controllers\AttendanceRequestController::class)->index($request);
    } else {
        abort(403);
    }
})->name('stamp_correction_request.index');

Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminAttendanceRequestController::class, 'show'])
    ->name('stamp_correction_request.show');

Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AdminAttendanceRequestController::class, 'approve'])
    ->name('stamp_correct_request.approve');