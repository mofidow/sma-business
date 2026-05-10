<?php

namespace App\Http\Middleware;

use App\Models\User;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Store;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $opened_register = null;
        $available_stores = null;

        $user = $request->user();
        if ($request->session()->has('impersonate')) {
            Auth::guard('web')->onceUsingId($request->session()->get('impersonate'));
            $acting_as_user = User::find($request->session()->get('impersonate'));
        }

        if ($user) {
            $user->loadMissing(['roles', 'store']);
            if (get_module('pos')) {
                $opened_register = $user->openedRegister;
                session(['open_register_id' => $opened_register?->id]);
                if ($user->store_id) {
                    $available_stores = [$user->store];
                    session(['selected_store_id' => $user->store_id]);
                }
            }

            if ($user->hasRole(['Customer', 'Supplier'])) {
                session(['selected_store_id' => null]);
            } elseif ($user->isImpersonating() && $acting_as_user && $acting_as_user->hasRole(['Customer', 'Supplier'])) {
                session(['selected_store_id' => null]);
            }
        }

        if (! $available_stores) {
            $available_stores = Store::active()->get(['id', 'name']);

            // Auto-select when only one store exists and none is selected
            if (! session('selected_store_id') && $available_stores->count() === 1) {
                session(['selected_store_id' => $available_stores->first()->id]);
            }
        }

        $langFiles = json_decode(file_get_contents(lang_path('languages.json')));

        return array_merge(parent::share($request), [
            'demo'             => demo(),
            'base'             => url('/'),
            'previous'         => url()->previous(),
            'opened_register'  => $opened_register,
            'available_stores' => $available_stores,
            'pos_module'       => get_module('pos'),
            'language'         => app()->getLocale(),
            'shop_module'      => get_module('shop'),
            'default_currency' => default_currency(),
            'languages'        => $langFiles->available,
            'settings'         => get_public_settings(),
            'acting_as_user'   => $acting_as_user ?? null,
            'captcha_src'      => $user ? '' : captcha_src(),
            'open_register'    => $opened_register ? false : true,
            'selected_store'   => session('selected_store_id', null),
            'can_impersonate'  => $request->user()?->canImpersonate(),
            'is_impersonating' => $request->user()?->isImpersonating(),
            'select_store'     => $request->flash['select_store'] ?? false,
            'filters'          => $request->input('filters') ?? ['search' => '', 'sort' => 'latest'],
            'rtl_support'      => session('rtl_support', $request->cookie('rtl_support', get_settings('rtl_support'))),
            'flash'            => [
                'data'          => session('data'),
                'error'         => session('error'),
                'message'       => session('message'),
                'select_store'  => session('select_store') ?: $request->flash['select_store'] ?? null,
                'open_register' => session('open_register') ?: $request->flash['open_register'] ?? null,
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
        ]);
    }
}
