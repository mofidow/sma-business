<?php

namespace App\Models\Sma\Accounting;

use App\Models\User;
use App\Models\Model;
use App\Tec\Casts\AppDate;

class AssetMaintenance extends Model
{
    public static $hasUser = true;

    public static array $types = ['Scheduled', 'Unscheduled', 'Emergency', 'Preventive'];

    public static array $statuses = ['Scheduled', 'In Progress', 'Completed', 'Cancelled'];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny(['title', 'note'], 'like', "%$search%")
            ->orWhereHas('asset', fn ($q) => $q->search($search));
    }

    public function scopeFilter($query, $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->search($search))
            ->when($filters['asset_id'] ?? null, fn ($q, $id) => $q->where('asset_id', $id))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status));
    }

    /**
     * @return array{string, string}
     */
    protected function casts(): array
    {
        return [
            'start_date' => AppDate::class,
            'end_date'   => AppDate::class,
            'cost'       => 'decimal:4',
        ];
    }
}
