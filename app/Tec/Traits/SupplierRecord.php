<?php

namespace App\Tec\Traits;

use App\Models\User;

trait SupplierRecord
{
    public static function bootSupplierRecord()
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Supplier')) {
            static::addGlobalScope('supplier_data', fn ($q) => $q->where('supplier_id', $user->supplier_id));
        }

        if (session()->has('impersonate')) {
            $acting_as_user = User::find(session()->get('impersonate'));
            if ($acting_as_user && $acting_as_user->hasRole('Supplier')) {
                static::addGlobalScope('supplier_data', fn ($q) => $q->where('supplier_id', $acting_as_user->supplier_id));
            }
        }
    }
}
