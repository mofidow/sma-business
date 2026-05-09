<?php

namespace App\Models\Sma\Repair;

use App\Models\Model;
use App\Tec\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceType extends Model
{
    use HasFactory, SoftDeletes, HasSchemalessAttributes;

    protected function casts(): array
    {
        return [
            'active'           => 'boolean',
            'sort_order'       => 'integer',
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

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeFilter($query, $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->search($search))
            ->when(isset($filters['active']), fn ($q) => $q->where('active', $filters['active']));
    }
}
