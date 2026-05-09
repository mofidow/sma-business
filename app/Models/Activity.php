<?php

namespace App\Models;

use App\Tec\Casts\AppDate;
use Spatie\Activitylog\Models\Activity as ActivityModel;

class Activity extends ActivityModel
{
    protected function casts(): array
    {
        return [
            'created_at' => AppDate::class . ':time',
            'updated_at' => AppDate::class . ':time',
        ];
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, fn ($q, $s) => $q->search($s))
            ->when($filters['user_id'] ?? null, fn ($q, $user_id) => $q->where('causer_id', $user_id))
            ->when($filters['start_date'] ?? null, function ($query, $start_date) {
                $query->whereDate('created_at', '>=', $start_date);
            })->when($filters['end_date'] ?? null, function ($query, $end_date) {
                $query->whereDate('created_at', '<=', $end_date);
            });
    }

    public function scopeSearch($query, $search = null)
    {
        return $query->when($search, function ($query, $search) {
            $query->whereAny(['log_name', 'description'], 'like', '%' . $search . '%')
                ->orWhereHas('causer', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                });
        });
    }
}
