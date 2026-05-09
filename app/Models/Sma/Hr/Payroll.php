<?php

namespace App\Models\Sma\Hr;

use App\Models\User;
use App\Models\Model;
use App\Models\Sma\Setting\Store;

class Payroll extends Model
{
    protected $with = ['store:id,name', 'user:id,name'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function scopeFilter($query, array $filters): void
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('title', 'like', "%$s%"))
            ->when($filters['store_id'] ?? null, fn ($q, $id) => $q->where('store_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['year'] ?? null, fn ($q, $y) => $q->where('year', $y))
            ->when($filters['month'] ?? null, fn ($q, $m) => $q->where('month', $m))
            ->when($filters['sort'] ?? null, fn ($q, $sort) => $q->sort($sort));
    }
}
