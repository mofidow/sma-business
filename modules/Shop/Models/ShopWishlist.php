<?php

namespace Modules\Shop\Models;

use App\Models\Sma\Product\Product;
use Illuminate\Database\Eloquent\Model;

class ShopWishlist extends Model
{
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeOfUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
}
