<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\Staff\RegisterController;
use Laravel\Fortify\Contracts\LogoutResponse;
use App\Http\Responses\CustomRegisterResponse;
use App\Http\Responses\UserLogoutResponse;
use App\Http\Responses\AdminLogoutResponse;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            RegisteredUserController::class,
            RegisterController::class
        );

        $this->app->singleton(RegisterResponse::class, CustomRegisterResponse::class);

        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);

        $this->app->bind(LogoutResponse::class, function () {
            if (request()->is('admin/*')) {
                return new \App\Http\Responses\AdminLogoutResponse();
            }

            return new \App\Http\Responses\UserLogoutResponse();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('user.auth.register');
        });
        Fortify::loginView(function () {
            return view('user.auth.login');
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}
