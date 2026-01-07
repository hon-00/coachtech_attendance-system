<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();
        if ($user->role === \App\Models\User::ROLE_ADMIN) {
            return redirect()->route('admin.attendance.list');
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}