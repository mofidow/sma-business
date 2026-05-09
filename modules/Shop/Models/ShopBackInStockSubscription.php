<?php

namespace Modules\Shop\Models;

use App\Models\Sma\Product\Product;
use App\Models\Sma\Product\Variation;
use Illuminate\Database\Eloquent\Model;

class ShopBackInStockSubscription extends Model
{
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(Variation::class);
    }
}
