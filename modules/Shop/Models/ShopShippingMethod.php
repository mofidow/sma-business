<?php

namespace Modules\Shop\Models;

use Nnjeim\World\Models\State;
use Nnjeim\World\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopShippingMethod extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $with = ['state', 'country'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}
