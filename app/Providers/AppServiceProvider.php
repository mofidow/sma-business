<?php

namespace App\Providers;

use Exception;
use Carbon\Carbon;
use App\Models\Timezone;
use App\Tec\Events\AttachmentEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use App\Tec\Listeners\AttachmentEventListener;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend(
            \Illuminate\Translation\Translator::class,
            fn ($translator) => new \App\Tec\Core\Translator($translator->getLoader(), $translator->getLocale())
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! env('APP_INSTALLED', false)) {
            redirect()->to('/install');
        } elseif (function_exists('get_settings')) {
            app()->useLangPath(base_path('lang'));

            Event::listen(
                AttachmentEvent::class,
                AttachmentEventListener::class,
            );

            try {
                $settings = get_settings(['mail', 'payment', 'barcode', 'timezone_id', 'default_locale', 'captcha_provider', 'captcha_site_key', 'captcha_secret_key', 'fiscal_service_driver', 'telegram_bot_token', 'twilio_account_sid', 'twilio_auth_token', 'twilio_from', 'twilio_sms_service_sid']);

                if (($settings['captcha_provider'] ?? null) && ($settings['captcha_site_key'] ?? null) && ($settings['captcha_secret_key'] ?? null)) {
                    config(['captcha.sitekey' => $settings['captcha_site_key']]);
                    config(['captcha.secret' => $settings['captcha_secret_key']]);
                }

                if ($settings['telegram_bot_token'] ?? null) {
                    config(['services.telegram-bot-api.token' => $settings['telegram_bot_token']]);
                }

                if ($settings['twilio_account_sid'] ?? null) {
                    config(['twilio-notification-channel.account_sid' => $settings['twilio_account_sid']]);
                    config(['twilio-notification-channel.auth_token' => $settings['twilio_auth_token'] ?? null]);
                    config(['twilio-notification-channel.from' => $settings['twilio_from'] ?? null]);
                    config(['twilio-notification-channel.sms_service_sid' => $settings['twilio_sms_service_sid'] ?? null]);
                }

                config(['fiscal-services.default' => $settings['fiscal_service_driver'] ?? null]);

                if ($settings['timezone_id'] ?? null) {
                    $timezone = Timezone::find($settings['timezone_id'])?->name;
                }

                config(['app.timezone' => $timezone ?? 'UTC']);
                Carbon::setLocale($settings['default_locale'] ?? 'en');

                $mail = (array) ($settings['mail']['mail'] ?? []);
                if (! empty($mail)) {
                    $mail = array_replace_recursive(config('mail'), $mail);
                    config(['mail' => $mail]);
                }

                $services = (array) ($settings['mail']['services'] ?? []);
                if (! empty($services)) {
                    $services = array_replace_recursive(config('services'), $services);
                    config(['services' => $services]);
                }

                $services = (array) ($settings['payment']['services'] ?? []);
                if (! empty($services)) {
                    $services = array_replace_recursive(config('services'), $services);
                    config(['services' => $services]);
                }
            } catch (Exception $e) {
                if (! app()->runningInConsole()) {
                    logger('Provider settings error: ' . $e->getMessage());
                }
            }

            Gate::before(function ($user, $ability) {
                return $user->hasRole('Super Admin') ? true : null;
            });
        }

        if (app()->environment('local', 'testing') || demo()) {
            $this->loadMigrationsFrom(database_path('migrations/dump'));
        }

        // Catch N+1 queries in local/dev (does not run in production)
        Model::preventLazyLoading(! app()->isProduction());

        // Log slow queries in local dev (>500ms)
        if (app()->environment('local')) {
            DB::whenQueryingForLongerThan(500, function () {
                Log::warning('Slow query detected (>500ms)');
            });
        }
    }
}
