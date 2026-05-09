<?php

namespace App\Models\Sma\Hr;

use App\Models\User;
use App\Models\Model;
use App\Tec\Casts\AppDate;

class Leave extends Model
{
    protected $with = [
        'approvedBy:id,name',
        'employee.user:id,name',
        'leaveType:id,name,is_paid',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => AppDate::class,
            'end_date'   => AppDate::class,
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeFilter($query, array $filters): void
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->whereHas('employee.user', fn ($u) => $u->where('name', 'like', "%$s%")))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['employee_id'] ?? null, fn ($q, $id) => $q->where('employee_id', $id))
            ->when($filters['leave_type_id'] ?? null, fn ($q, $id) => $q->where('leave_type_id', $id))
            ->when($filters['sort'] ?? null, fn ($q, $sort) => $q->sort($sort));
    }
}
