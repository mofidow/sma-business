<?php

namespace App\Models\Sma\Product;

use App\Models\Model;
use App\Tec\Traits\Trackable;
use App\Models\Sma\Setting\Store;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stock extends Model
{
    use HasFactory;
    use Trackable;

    public static $hasStore = true;

    protected $appends = ['balance'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeOfProduct($query, $product)
    {
        return $query->where('product_id', $product);
    }

    public function scopeOfVariation($query, $variation)
    {
        return $query->where('variation_id', $variation);
    }

    public function scopeOfStore($query, $store = null)
    {
        return $query->where('store_id', $store ?? session('selected_store_id'));
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    protected static function booted()
    {
        parent::booted();

        static::deleted(function ($model) {
            $model->tracks()->delete();
        });

        static::forceDeleted(function ($model) {
            $model->tracks()->forceDelete();
        });
    }
}
