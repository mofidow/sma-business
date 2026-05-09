<?php

namespace Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class ShopCartItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['selected' => 'array'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeOfCart($query, $cartId)
    {
        return $query->where('cart_id', $cartId);
    }

    public function scopeOfUser($query)
    {
        if ($user = auth()->user()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }
}
