<?php

namespace Modules\Shop\Models;

use App\Tec\Casts\AppDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopCoupon extends Model
{
    use HasFactory;

    protected $casts = ['expiry_date' => AppDate::class . ':time'];

    protected $guarded = ['id'];

    public function categories()
    {
        return $this->morphedByMany(Category::class, 'couponable');
    }

    public function items()
    {
        return $this->morphedByMany(Item::class, 'couponable');
    }

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}
