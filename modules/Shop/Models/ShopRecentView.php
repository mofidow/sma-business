<?php

namespace Modules\Shop\Models;

use App\Models\Sma\Product\Product;
use Illuminate\Database\Eloquent\Model;

class ShopRecentView extends Model
{
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeOfUser($query, $user = null)
    {
        return $query->where('user_id', $user ? $user->id : auth()->id());
    }

    public function scopeOfIp($query, $ip_address)
    {
        return $query->where('ip_address', inet_pton($ip_address ? $ip_address : request()->ip()));
    }

    public function scopeOfUserOrIp($query, $user = null, $ip_address = null)
    {
        return $query->where('user_id', $user ? $user->id : auth()->id())
            ->orWhere('ip_address', inet_pton($ip_address ? $ip_address : request()->ip()));
    }

    protected function ipAddress(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => inet_ntop($value),
            set: fn (string $value) => inet_pton($value),
        );
    }

    protected static function booted()
    {
        if (auth()->check()) {
            static::addGlobalScope('mine', fn ($q) => $q->where('user_id', auth()->id()));
        }
    }
}
