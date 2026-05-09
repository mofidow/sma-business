<?php

namespace App\Models\Sma\Pos;

use App\Models\User;
use App\Models\Model;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Setting\Store;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hall extends Model
{
    use HasFactory;
    use SoftDeletes;

    public static $hasUser = true;

    public static $hasStore = true;

    protected $fillable = [
        'name', 'description', 'active', 'sort_order', 'store_id', 'user_id',
    ];

    public $casts = ['active' => 'boolean', 'sort_order' => 'integer'];

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeFilter($query, $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($filters['store_id'] ?? null, fn ($query, $store_id) => $query->ofStore($store_id))
            ->when($filters['active'] ?? null, fn ($query, $active) => $query->where('active', $active))
            ->when($filters['sort'] ?? null, fn ($query, $sort) => $query->sort($sort));
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny(['name', 'description'], 'like', "%$search%");
    }

    public function scopeOfStore($query, $store_id = null)
    {
        return $query->where('store_id', $store_id ?: session('selected_store_id'));
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function delete()
    {
        if ($this->tables()->exists()) {
            return false;
        }

        return parent::delete();
    }

    public function forceDelete()
    {
        if ($this->tables()->exists()) {
            return false;
        }

        log_activity(__('{record} has permanently deleted.', ['record' => 'Hall']), $this, $this, 'Hall');

        return parent::forceDelete();
    }
}
