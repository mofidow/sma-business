<?php

namespace App\Models\Sma\People;

use App\Models\User;
use App\Models\Model;
use App\Tec\Core\Notifiable;
use App\Tec\Traits\Trackable;
use App\Models\Sma\Order\Sale;
use Nnjeim\World\Models\State;
use Nnjeim\World\Models\Country;
use App\Models\Sma\Order\Payment;
use App\Tec\Traits\HasAwardPoints;
use App\Models\Sma\Order\Quotation;
use App\Models\Sma\Order\ReturnOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasAwardPoints;
    use HasFactory;
    use Notifiable;
    use Trackable;

    public static $hasUser = true;

    protected $appends = ['balance', 'points'];

    protected $with = ['state', 'country', 'customerGroup', 'priceGroup'];

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function returnOrders()
    {
        return $this->hasMany(ReturnOrder::class);
    }

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function priceGroup()
    {
        return $this->belongsTo(PriceGroup::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function scopeFilter($query, $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when(($filters['overdue'] ?? null) == 1, fn ($query) => $query->whereHasBalanceAbove('due_limit'))
            ->when($filters['sort'] ?? null, fn ($query, $sort) => $query->sort($sort));
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny(['name', 'company', 'email', 'phone'], 'like', "%$search%");
    }

    public function scopeSort($query, $sort)
    {
        if ($sort == 'latest') {
            $query->latest('id');
        } elseif (str($sort)->contains('balance')) {
            [$column, $direction] = explode(':', $sort);
            $query->addSelect(['due_amount' => function ($query) {
                $query->selectRaw('SUM(tracks.value)')->from('tracks')
                    ->whereColumn('tracks.trackable_id', 'customers.id')
                    ->where('tracks.trackable_type', Customer::class);
            }])->orderBy('due_amount', $direction);
            // $query->selectRaw('customers.*, SUM(tracks.value) as due_amount')
            //     ->leftJoin('tracks', function ($join) {
            //         $join->on('tracks.trackable_id', '=', 'customers.id')
            //             ->where('tracks.trackable_type', '=', self::class);
            //     })->orderBy('due_amount ' . $direction)->groupBy('customers.id');
        } elseif (str($sort)->contains('.')) {
            $relation_tables = [
                'customer_group' => ['table' => 'customer_groups', 'model' => 'App\Models\Sma\People\CustomerGroup'],
                'price_group'    => ['table' => 'price_groups', 'model' => 'App\Models\Sma\People\PriceGroup'],
            ];
            [$relation, $column] = explode('.', $sort);
            [$column, $direction] = explode(':', $column);
            $table = $relation_tables[$relation];
            $query->orderBy($table['model']::select($column)->whereColumn($table['table'] . '.id', 'customers.' . $relation . '_id'), $direction);
        } else {
            [$column, $direction] = explode(':', $sort);
            $query->orderBy('customers.' . $column, $direction);
        }

        return $query;
    }

    public function routeNotificationForTelegram(): ?string
    {
        return $this->telegram_user_id;
    }

    public function routeNotificationForTwilio(): ?string
    {
        return $this->phone;
    }

    public function delete()
    {
        if (
            $this->sales()->withoutGlobalScopes()->exists() ||
            $this->payments()->withoutGlobalScopes()->exists() ||
            $this->quotations()->withoutGlobalScopes()->exists() ||
            $this->returnOrders()->withoutGlobalScopes()->exists()
        ) {
            return false;
        }

        $this->tracks()->delete();
        $this->addresses()->delete();

        return parent::delete();
    }

    public function forceDelete()
    {
        if (
            $this->sales()->withoutGlobalScopes()->exists() ||
            $this->payments()->withoutGlobalScopes()->exists() ||
            $this->quotations()->withoutGlobalScopes()->exists() ||
            $this->returnOrders()->withoutGlobalScopes()->exists()
        ) {
            return false;
        }

        $this->tracks()->forceDelete();
        $this->addresses()->forceDelete();

        log_activity(__('{record} has permanently deleted.', ['record' => 'Customer']), $this, $this, 'Customer');

        return parent::forceDelete();
    }

    protected static function booted()
    {
        parent::booted();

        static::created(function ($model) {
            $model->mutateTracking($model->opening_balance ?: 0, [
                'description' => 'Opening Balance',
            ]);
        });
    }
}
