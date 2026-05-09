<?php

namespace App\Models\Sma\Hr;

use App\Models\User;
use App\Models\Model;
use App\Models\Sma\Setting\Store;

class Claim extends Model
{
    protected $with = ['employee.user:id,name', 'store:id,name', 'approvedBy:id,name'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeFilter($query, array $filters): void
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('title', 'like', "%$s%")
                ->orWhereHas('employee.user', fn ($u) => $u->where('name', 'like', "%$s%")))
            ->when($filters['store_id'] ?? null, fn ($q, $id) => $q->where('store_id', $id))
            ->when($filters['employee_id'] ?? null, fn ($q, $id) => $q->where('employee_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['sort'] ?? null, fn ($q, $sort) => $q->sort($sort));
    }
}
