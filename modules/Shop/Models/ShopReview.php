<?php

namespace Modules\Shop\Models;

use App\Models\User;
use App\Models\Sma\Product\Product;
use Illuminate\Database\Eloquent\Model;

class ShopReview extends Model
{
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeOfProduct($query, $product_id)
    {
        return $query->where('product_id', $product_id);
    }

    public function scopeOfUser($query)
    {
        return $query->where('user_id', auth()->user()?->id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
