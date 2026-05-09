<?php

namespace Modules\Shop\Models;

use Nnjeim\World\Models\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopCurrency extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
