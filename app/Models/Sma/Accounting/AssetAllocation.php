<?php

namespace App\Models\Sma\Accounting;

use App\Models\User;
use App\Models\Model;
use App\Tec\Casts\AppDate;

class AssetAllocation extends Model
{
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function allocatedTo()
    {
        return $this->belongsTo(User::class, 'allocated_to');
    }

    public function allocatedBy()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny(['note'], 'like', "%$search%")
            ->orWhereHas('asset', fn ($q) => $q->search($search));
    }

    public function scopeFilter($query, $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->search($search))
            ->when($filters['asset_id'] ?? null, fn ($q, $id) => $q->where('asset_id', $id))
            ->when($filters['allocated_to'] ?? null, fn ($q, $id) => $q->where('allocated_to', $id))
            ->when(isset($filters['returned']), fn ($q) => $filters['returned']
                ? $q->whereNotNull('return_date')
                : $q->whereNull('return_date')
            );
    }

    /**
     * @return array{string, string}
     */
    protected function casts(): array
    {
        return [
            'allocated_date' => AppDate::class,
            'return_date'    => AppDate::class,
        ];
    }
}
