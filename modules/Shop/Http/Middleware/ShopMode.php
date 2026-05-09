<?php

namespace Modules\Shop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Shop\Models\ShopCartItem;

class ShopMode
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $request->cookie('cart_id')) {
            $cartId = ShopCartItem::ofUser($user)->first()?->id ?: str()->uuid();
            cookie()->queue(cookie()->forever('cart_id', $cartId));
        }

        // Initialize RTL support based on cookie or current language
        if (! session()->has('shop_rtl')) {
            $isRtl = $request->cookie('shop_rtl') === '1';
            if (! $isRtl) {
                $langFiles = json_decode(file_get_contents(lang_path('languages.json')));
                $currentLang = collect($langFiles->available ?? [])->firstWhere('value', app()->getLocale());
                $isRtl = $currentLang->rtl ?? false;
            }
            session(['shop_rtl' => $isRtl]);
        }

        if ($user && $user->hasRole('Super Admin')) {
            return $next($request);
        }

        $settings = get_settings('general');
        if (($settings['shop_mode'] ?? null) != 'maintenance' && $user) {
            return $next($request);
        }

        return match ($settings['shop_mode'] ?? null) {
            'private'     => redirect()->route('shop.private'),
            'maintenance' => redirect()->route('shop.maintenance'),
            default       => $next($request),
        };
    }
}
