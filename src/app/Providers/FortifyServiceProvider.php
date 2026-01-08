<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Http\Requests\LoginRequest as CustomLoginRequest;
use App\Http\Responses\LoginResponse as CustomLoginResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Actions\Fortify\CreateNewUser;
use Laravel\Fortify\Contracts\LogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(LoginResponseContract::class, CustomLoginResponse::class);
    }

    public function boot()
    {
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::loginView(function (Request $request) {
            if ($request->routeIs('admin.login')) {
                return view('admin.auth.login');
            }
            return view('auth.login');
        });

        Fortify::createUsersUsing(CreateNewUser::class);

        $this->app->bind(FortifyLoginRequest::class, CustomLoginRequest::class);

        Fortify::authenticateUsing(function (Request $request) {

            $user = User::where('email', $request->email)->first();

            if (!$user || ! Hash::check($request->password, $user->password)) {
                return null;
            }

            if ($request->routeIs('admin.login.submit')) {
                if ($user->role === User::ROLE_ADMIN) {
                    Auth::guard('admin')->login($user);
                    return $user;
                }
                return null;
            }

            if ($request->routeIs('login') && $user->role === User::ROLE_USER) {
                return $user;
            }

            return null;
        });

        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                if ($request->routeIs('admin.*')) {
                    return redirect()->route('admin.login');
                }
                return redirect()->route('login');
            }
        });
    }
}