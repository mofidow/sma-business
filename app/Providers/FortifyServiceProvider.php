<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use App\Tec\Actions\Fortify\CreateNewUser;
use Illuminate\Support\Facades\RateLimiter;
use App\Tec\Actions\Fortify\ResetUserPassword;
use Illuminate\Validation\ValidationException;
use App\Tec\Actions\Fortify\UpdateUserPassword;
use App\Tec\Actions\Fortify\UpdateUserProfileInformation;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('username', $request->username)
                ->orWhere('email', $request->username)->first();

            if ($user && ! $user->active) {
                throw ValidationException::withMessages([
                    Fortify::username() => __('Account is not active!'),
                ]);
            }

            if ($user && Hash::check($request->password, $user->password)) {
                activity()->log(__('User :username has been logged in.', ['username' => $user->username]));

                return $user;
            }
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
