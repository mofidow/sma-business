<?php

namespace App\Models\Sma\Order;

use App\Models\User;
use App\Models\Model;
use App\Tec\Casts\AppDate;
use App\Tec\Scopes\OfStore;
use App\Models\Sma\Pos\Register;
use App\Models\Sma\Setting\Store;
use App\Tec\Policies\UpdatePolicy;
use App\Tec\Traits\HasAttachments;
use App\Models\Sma\People\Customer;
use App\Models\Sma\People\Supplier;
use App\Tec\Observers\PaymentObserver;
use App\Tec\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[ScopedBy(OfStore::class)]
#[UsePolicy(UpdatePolicy::class)]
#[ObservedBy(PaymentObserver::class)]
class Payment extends Model
{
    use HasAttachments;
    use HasFactory;
    use HasSchemalessAttributes;

    public static $hasUser = true;

    public static $hasStore = true;

    public static $hasRegister = true;

    public static $userRecords = true;

    public static $hasReference = true;

    protected $setHash = true;

    protected function casts(): array
    {
        return [
            'extra_attributes' => 'array',
            'method_data'      => 'array',
            'date'             => AppDate::class,
            'created_at'       => AppDate::class . ':time',
            'updated_at'       => AppDate::class . ':time',
        ];
    }

    public function purchases(): MorphToMany
    {
        return $this->morphedByMany(Purchase::class, 'payable');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function sales(): MorphToMany
    {
        return $this->morphedByMany(Sale::class, 'payable')->withPivot('amount');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function register()
    {
        return $this->belongsTo(Register::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOfCustomer($query, $customer_id)
    {
        $query->where('customer_id', $customer_id);
    }

    public function scopeOfSupplier($query, $supplier_id)
    {
        $query->where('supplier_id', $supplier_id);
    }

    public function scopeOfMethod($query, $method)
    {
        $query->where('method', $method);
    }

    public function scopeOfStore($query, $store_id = null)
    {
        $query->where('store_id', $store_id ?: session('selected_store_id'));
    }

    public function scopeOfUser($query, $user_id = null)
    {
        $query->where('user_id', $user_id ?: auth()->id());
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($filters['end'] ?? null, fn ($query, $end) => $query->where('created_at', '<=', $end))
            ->when($filters['start'] ?? null, fn ($query, $start) => $query->where('created_at', '>=', $start))
            ->when($filters['method'] ?? null, fn ($query, $method) => $query->ofMethod($method))
            ->when($filters['user_id'] ?? null, fn ($query, $user_id) => $query->ofUser($user_id))
            ->when($filters['store_id'] ?? null, fn ($query, $store_id) => $query->ofStore($store_id))
            ->when($filters['customer_id'] ?? null, fn ($query, $customer_id) => $query->ofCustomer($customer_id))
            ->when($filters['supplier_id'] ?? null, fn ($query, $supplier_id) => $query->ofSupplier($supplier_id))
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query->where('date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query->where('date', '<=', $date))
            ->when($filters['sort'] ?? null, fn ($query, $sort) => $query->sort($sort));
    }

    public function scopeSearch($query, $s)
    {
        $query->where(fn ($q) => $q->where('date', 'like', "%{$s}%")
            ->orWhere('reference', 'like', "%{$s}%"))
            ->orWhereHas('customer', fn ($q) => $q->search($s))
            ->orWhereHas('supplier', fn ($q) => $q->search($s));
    }

    public function scopeReceived($query)
    {
        $query->where('received', 1);
    }

    public function scopePending($query)
    {
        $query->whereNull('received')->orWhere('received', 0);
    }

    public function forceDelete()
    {
        log_activity(__('{record} has permanently deleted.', ['record' => 'Payment']), $this, $this, 'Payment');

        return parent::forceDelete();
    }

    protected static function booted()
    {
        parent::booted();

        $user = auth()->user();
        if ($user && $user->hasRole('Customer') && $user->hasRole('Supplier')) {
            static::addGlobalScope('customer_supplier_data', fn ($q) => $q->where('customer_id', $user->customer_id)->orWhere('supplier_id', $user->supplier_id));
        } elseif ($user && $user->hasRole('Customer')) {
            static::addGlobalScope('customer_data', fn ($q) => $q->where('customer_id', $user->customer_id));
        } elseif ($user && $user->hasRole('Supplier')) {
            static::addGlobalScope('supplier_data', fn ($q) => $q->where('supplier_id', $user->supplier_id));
        }

        if (session()->has('impersonate')) {
            $acting_as_user = User::find(session()->get('impersonate'));
            if ($acting_as_user && $acting_as_user->hasRole('Customer') && $acting_as_user->hasRole('Supplier')) {
                static::addGlobalScope('customer_supplier_data', fn ($q) => $q->where('customer_id', $acting_as_user->customer_id)->orWhere('supplier_id', $acting_as_user->supplier_id));
            } elseif ($acting_as_user && $acting_as_user->hasRole('Customer')) {
                static::addGlobalScope('customer_data', fn ($q) => $q->where('customer_id', $acting_as_user->customer_id));
            } elseif ($acting_as_user && $acting_as_user->hasRole('Supplier')) {
                static::addGlobalScope('supplier_data', fn ($q) => $q->where('supplier_id', $acting_as_user->supplier_id));
            }
        }

        static::creating(function ($model) {
            if ($model->payment_for == 'Customer') {
                $model->supplier_id = null;
            }
            if ($model->payment_for == 'Supplier') {
                $model->customer_id = null;
            }
        });
    }
}
