<?php

namespace App\Tec\Traits;

use App\Models\User;

trait CustomerRecord
{
    public static function bootCustomerRecord()
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Customer')) {
            static::addGlobalScope('customer_data', fn ($q) => $q->where('customer_id', $user->customer_id));
        }

        if (session()->has('impersonate')) {
            $acting_as_user = User::find(session()->get('impersonate'));
            if ($acting_as_user && $acting_as_user->hasRole('Customer')) {
                static::addGlobalScope('customer_data', fn ($q) => $q->where('customer_id', $acting_as_user->customer_id));
            }
        }
    }
}
