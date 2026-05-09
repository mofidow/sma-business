<?php

namespace App\Models\Sma\Pos;

use App\Models\Sma\Setting\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QrOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'items' => 'array',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function scopeOfStore($query, $storeId = null): void
    {
        $query->where('store_id', $storeId ?: session('selected_store_id'));
    }

    public function scopePending($query): void
    {
        $query->where('status', 'pending');
    }

    public function scopeFilter($query, $filters = []): void
    {
        $query->when($filters['search'] ?? null, fn ($q, $search) => $q->where('number', 'like', "%{$search}%")
            ->orWhere('customer_name', 'like', "%{$search}%"))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['sort'] ?? null, fn ($q, $sort) => $q->sort($sort));
    }

    public function scopeSort($query, $sort): void
    {
        if ($sort) {
            [$column, $direction] = explode(':', $sort);
            $query->orderBy($column, $direction ?: 'asc');
        } else {
            $query->latest('id');
        }
    }

    protected static function booted(): void
    {
        static::creating(function (QrOrder $model) {
            if (! $model->number) {
                $model->number = 'QR' . now()->format('ymd') . '-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
