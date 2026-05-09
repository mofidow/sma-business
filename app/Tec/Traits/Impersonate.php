<?php

namespace App\Tec\Traits;

use App\Models\User;

trait Impersonate
{
    public function canBeImpersonated()
    {
        return $this->hasRole('Customer');
    }

    public function canImpersonate()
    {
        return ! $this->hasRole('Customer');
    }

    public function impersonatedAs()
    {
        if (! session()->has('impersonate')) {
            return null;
        }

        return User::find(session('impersonate'));
    }

    public function isImpersonating()
    {
        return session()->has('impersonate');
    }

    public function startImpersonating()
    {
        session()->put('impersonate', $this->id);
    }

    public function stopImpersonating()
    {
        session()->forget('impersonate');
    }
}
