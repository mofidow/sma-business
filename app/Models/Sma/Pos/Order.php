<?php

namespace App\Models\Sma\Pos;

use App\Models\User;
use App\Models\Model;
use App\Tec\Casts\AppDate;
use App\Models\Sma\Order\Sale;
use Illuminate\Support\Carbon;
use App\Models\Sma\Setting\Store;
use App\Tec\Policies\UpdatePolicy;
use App\Models\Sma\People\Customer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[UsePolicy(UpdatePolicy::class)]
class Order extends Model
{
    use HasFactory;

    public static $hasUser = true;

    public static $hasStore = true;

    public static $hasRegister = true;

    public static $userRecords = true;

    protected $appends = ['created_at_for_humans', 'getting_late'];

    public $casts = ['data' => 'array', 'notes' => 'array'];

    public $with = [
        'user:id,name',
        'hall:id,name',
        'customer:id,company,name',
        'table:id,hall_id,name,seats',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => AppDate::class . ':time',
            'updated_at' => AppDate::class . ':time',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->hasOne(Sale::class, 'order_number', 'number');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['trashed'] ?? 'not', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($filters['user_id'] ?? null, fn ($query, $user_id) => $query->ofUser($user_id))
            ->when($filters['store_id'] ?? null, fn ($query, $store_id) => $query->ofStore($store_id))
            ->when($filters['customer_id'] ?? null, fn ($query, $customer_id) => $query->ofCustomer($customer_id))
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query->where('created_at', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query->where('created_at', '<=', $date))
            ->when($filters['sort'] ?? null, fn ($query, $sort) => $query->sort($sort));
    }

    public function scopeOfCustomer($query, $customer_id)
    {
        $query->where('customer_id', $customer_id);
    }

    public function scopeOfStore($query, $store_id = null)
    {
        $query->where('store_id', $store_id ?: session('selected_store_id'));
    }

    public function scopeOfUser($query, $user_id = null)
    {
        $query->where('user_id', $user_id ?: auth()->id());
    }

    public function scopeQrOrders($query)
    {
        $query->where('source', 'qr');
    }

    public function scopePosOrders($query)
    {
        $query->where('source', 'pos');
    }

    public function scopePending($query)
    {
        $query->where('status', 'pending');
    }

    public function scopeSearch($query, $s)
    {
        $query->where(fn ($q) => $q->where('number', 'like', "%{$s}%")
            ->orWhere('reference', 'like', "%{$s}%")
            ->orWhere('customer_name', 'like', "%{$s}%"))
            ->orWhereHas('customer', fn ($q) => $q->search($s));
    }

    public function isQrOrder(): bool
    {
        return $this->source === 'qr';
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

    protected function createdAtForHumans(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Carbon::parse($attributes['created_at'])->diffForHumans(),
        );
    }

    protected function gettingLate(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['status'] == 'pending' && Carbon::parse($attributes['created_at'])->lt(now()->subMinutes(5)),
        );
    }

    public function forceDelete()
    {
        log_activity(__('{record} has permanently deleted.', ['record' => 'Sale']), $this, $this, 'Sale');

        return parent::forceDelete();
    }
}
