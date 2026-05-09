<?php

namespace App\Models\Sma\People;

use App\Models\User;
use App\Models\Model;
use App\Tec\Casts\AppDate;
use Nnjeim\World\Models\State;
use Nnjeim\World\Models\Country;
use App\Models\Sma\Setting\Store;
use App\Tec\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory;
    use HasSchemalessAttributes;

    public static $hasUser = true;

    protected $with = ['state', 'country'];

    protected function casts(): array
    {
        return [
            'extra_attributes' => 'array',
            'created_at'       => AppDate::class . ':time',
            'updated_at'       => AppDate::class . ':time',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function forceDelete()
    {
        log_activity(__('{record} has permanently deleted.', ['record' => 'Delivery']), $this, $this, 'Delivery');

        return parent::forceDelete();
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($filters['user_id'] ?? null, fn ($query, $user_id) => $query->ofUser($user_id))
            ->when($filters['store_id'] ?? null, fn ($query, $store_id) => $query->ofStore($store_id))
            ->when($filters['customer_id'] ?? null, fn ($query, $customer_id) => $query->ofCustomer($customer_id))
            ->when($filters['sort'] ?? null, fn ($query, $sort) => $query->sort($sort));
    }

    public function scopeOfCustomer($query, $customer_id)
    {
        $query->where('customer_id', $customer_id);
    }

    public function scopeOfStore($query, $store_id)
    {
        $query->where('store_id', $store_id);
    }

    public function scopeOfUser($query, $user_id = null)
    {
        $query->where('user_id', $user_id ?? auth()->id());
    }

    public function scopeSearch($query, $s)
    {
        $query->where(
            fn ($q) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhereRelation('customer', 'name', 'like', "%{$s}%")
        );
    }
}
