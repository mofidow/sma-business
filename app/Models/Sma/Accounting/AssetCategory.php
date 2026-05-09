<?php

namespace App\Models\Sma\Accounting;

use App\Models\Model;

class AssetCategory extends Model
{
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function scopeActive($query)
    {
        $query->where('active', 1);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny(['name', 'description'], 'like', "%$search%");
    }

    public function scopeFilter($query, $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->search($search))
            ->when(isset($filters['active']), fn ($q) => $q->where('active', $filters['active']));
    }
}
