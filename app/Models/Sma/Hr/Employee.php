<?php

namespace App\Models\Sma\Hr;

use App\Models\User;
use App\Models\Model;
use App\Tec\Casts\AppDate;
use App\Models\Sma\Setting\Store;

class Employee extends Model
{
    public static $hasStore = true;

    protected $with = [
        'store:id,name',
        'user:id,name,email,phone,profile_photo_path',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => AppDate::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    public function scopeFilter($query, array $filters): void
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->whereHas('user', fn ($u) => $u->whereAny(['name', 'email', 'phone'], 'like', "%$s%"))
                ->orWhere('department', 'like', "%$s%")
                ->orWhere('job_title', 'like', "%$s%"))
            ->when($filters['store_id'] ?? null, fn ($q, $id) => $q->where('store_id', $id))
            ->when($filters['sort'] ?? null, fn ($q, $sort) => $q->sort($sort));
    }
}
