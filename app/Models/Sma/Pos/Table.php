<?php

namespace App\Models\Sma\Pos;

use App\Models\User;
use App\Models\Model;
use Illuminate\Support\Str;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Setting\Store;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Table extends Model
{
    use HasFactory;
    use SoftDeletes;

    public static $hasUser = true;

    public static $hasStore = true;

    protected $fillable = [
        'hall_id', 'name', 'seats', 'description',
        'active', 'sort_order', 'store_id', 'user_id', 'qr_token',
    ];

    public $casts = [
        'active'     => 'boolean',
        'seats'      => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = ['status'];

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function currentOrder()
    {
        return $this->hasOne(Order::class)->latestOfMany();
    }

    public function qrOrders()
    {
        return $this->hasMany(QrOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOfHall($query, $hall_id)
    {
        return $query->where('hall_id', $hall_id);
    }

    public function scopeOfStore($query, $store_id = null)
    {
        return $query->where('store_id', $store_id ?: session('selected_store_id'));
    }

    public function scopeAvailable($query)
    {
        return $query->active()->doesntHave('currentOrder');
    }

    public function scopeFilter($query, $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($filters['store_id'] ?? null, fn ($query, $store_id) => $query->ofStore($store_id))
            ->when($filters['hall_id'] ?? null, fn ($query, $hallId) => $query->where('hall_id', $hallId))
            ->when($filters['active'] ?? null, fn ($query, $active) => $query->where('active', $active))
            ->when($filters['sort'] ?? null, fn ($query, $sort) => $query->sort($sort));
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny(['name', 'description'], 'like', "%$search%")
            ->orWhereHas('hall', fn ($q) => $q->where('name', 'like', "%$search%"));
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeOccupied($query)
    {
        return $query->whereHas('currentOrder');
    }

    public function isOccupied(): bool
    {
        return $this->currentOrder()->exists();
    }

    public function getStatusAttribute(): string
    {
        return $this->isOccupied() ? 'occupied' : 'available';
    }

    public function getMenuUrlAttribute(): string
    {
        return url('/menu/' . $this->qr_token);
    }

    public function regenerateQrToken(): self
    {
        $this->update(['qr_token' => Str::uuid()->toString()]);

        return $this;
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (Table $table) {
            if (! $table->qr_token) {
                $table->qr_token = Str::uuid()->toString();
            }
        });
    }
}
