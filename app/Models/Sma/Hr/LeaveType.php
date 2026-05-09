<?php

namespace App\Models\Sma\Hr;

use App\Models\Model;

class LeaveType extends Model
{
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function scopeFilter($query, array $filters): void
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->when($filters['sort'] ?? null, fn ($q, $sort) => $q->sort($sort));
    }
}
