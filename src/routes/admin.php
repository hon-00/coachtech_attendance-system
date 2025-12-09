<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('admin.auth.login');
})->name('admin.login');

Route::post('/login', [\App\Http\Controllers\Admin\LoginController::class, 'login'])
    ->name('admin.login.submit');