<?php

namespace App\Providers;

use Liberu\Foundation\JetstreamBridge\Actions\Fortify\CreateNewUser as CreateNewUserWithTeams;
use Liberu\Foundation\JetstreamBridge\Actions\Fortify\ResetUserPassword;
use Liberu\Foundation\JetstreamBridge\Actions\Fortify\UpdateUserPassword;
use Liberu\Foundation\JetstreamBridge\Actions\Fortify\UpdateUserProfileInformation;
use Liberu\Foundation\Identity\Http\Responses\LoginResponse;
use Liberu\Foundation\Identity\Http\Responses\LogoutResponse;
use Liberu\Foundation\Identity\Http\Responses\RegisterResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUserWithTeams::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));

        $this->app->bind(\Filament\Auth\Http\Responses\Contracts\LogoutResponse::class, LogoutResponse::class);
        $this->app->singleton(\Laravel\Fortify\Contracts\LoginResponse::class, LoginResponse::class);
        $this->app->singleton(\Laravel\Fortify\Contracts\RegisterResponse::class, RegisterResponse::class);
    }
}
