<?php

namespace App\Models\Sma\Accounting;

use App\Models\User;
use App\Models\Model;
use App\Tec\Casts\AppDate;

class Asset extends Model
{
    public static $hasUser = true;

    public static array $conditions = ['New', 'Good', 'Fair', 'Poor', 'Damaged'];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function allocations()
    {
        return $this->hasMany(AssetAllocation::class);
    }

    public function maintenances()
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    public function currentAllocation()
    {
        return $this->hasOne(AssetAllocation::class)->whereNull('return_date')->latest();
    }

    public function scopeActive($query)
    {
        $query->where('active', 1);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny(['name', 'code', 'serial_number', 'description'], 'like', "%$search%");
    }

    public function scopeFilter($query, $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->search($search))
            ->when($filters['asset_category_id'] ?? null, fn ($q, $id) => $q->where('asset_category_id', $id))
            ->when($filters['condition'] ?? null, fn ($q, $c) => $q->where('condition', $c))
            ->when(isset($filters['active']), fn ($q) => $q->where('active', $filters['active']));
    }

    /**
     * @return array{string, string}
     */
    protected function casts(): array
    {
        return [
            'purchase_date'   => AppDate::class,
            'warranty_expiry' => AppDate::class,
            'purchase_cost'   => 'decimal:4',
        ];
    }
}
