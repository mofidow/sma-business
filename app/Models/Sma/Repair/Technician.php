<?php

namespace App\Models\Sma\Repair;

use App\Models\Model;
use App\Tec\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Technician extends Model
{
    use HasFactory, HasSchemalessAttributes, SoftDeletes;

    protected function casts(): array
    {
        return [
            'active'           => 'boolean',
            'extra_attributes' => 'array',
        ];
    }

    public function repairOrders()
    {
        return $this->hasMany(RepairOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny(['name', 'email', 'phone', 'skills'], 'like', "%{$search}%");
    }

    public function scopeFilter($query, $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->search($search))
            ->when(isset($filters['active']), fn ($q) => $q->where('active', $filters['active']));
    }
}
