<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Impersonate
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->has('impersonate') && get_settings('impersonation')) {
            Auth::guard('web')->onceUsingId($request->session()->get('impersonate'));
        }

        return $next($request);
    }
}
