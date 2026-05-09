<?php

namespace App\Http\Middleware;

use Closure;
use Exception;

class Language
{
    public function handle($request, Closure $next)
    {
        try {
            $locale = 'en';
            if (optional($request->user())->language) {
                $locale = $request->user()->language;
            } elseif (env('APP_INSTALLED') && function_exists('get_settings')) {
                $locale = get_settings('language');
            }

            app()->setLocale($request->cookie('language') ?: ($locale ?: config('app.locale')));

            return $next($request);
        } catch (Exception $e) {
            return $next($request);
        }
    }
}
