<?php

namespace Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopPage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}
