<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Store;
// use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class SelectStore
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $store_id = $request->session()->get('selected_store_id', false);
        $store = $store_id ? Store::where('id', $store_id)->exists() : false;

        $user = $request->user();
        if (! $store_id && in_array($request->route()->getActionMethod(), ['index', 'show']) && ($user && ($user->hasRole(['Super Admin', 'Customer', 'Supplier']) || $user->can('read-all')))) {
            return $next($request);
        }

        if (! $store_id || ! $store) {
            // abort(403, __('Please select a store first!'));
            $request->session()->flash('select_store', true);
            $request->session()->put('select_store_from', $request->fullUrl());
            $request->session()->flash('error', __('Please select a store first!'));

            $redirecting = $request->session()->get('redirecting_back', 0);
            $request->session()->put('redirecting_back', $redirecting + 1);

            return $request->session()->get('redirecting_back') > 1 ? to_route('dashboard') : back();
        }
        // Context::add('selected_store_id', $store_id);

        return $next($request);
    }
}
