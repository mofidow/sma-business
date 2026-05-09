<?php

namespace App\Models\Sma\People;

use App\Models\User;
use App\Models\Model;
use App\Tec\Core\Notifiable;
use App\Tec\Traits\Trackable;
use Nnjeim\World\Models\State;
use Nnjeim\World\Models\Country;
use App\Models\Sma\Order\Expense;
use App\Models\Sma\Order\Payment;
use App\Models\Sma\Order\Purchase;
use App\Models\Sma\Product\Product;
use App\Models\Sma\Order\ReturnOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;
    use Notifiable;
    use Trackable;

    public static $hasUser = true;

    protected $appends = ['balance'];

    protected $with = ['state', 'country'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function returnOrders()
    {
        return $this->hasMany(ReturnOrder::class);
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
            ->when($filters['sort'] ?? null, fn ($query, $sort) => $query->sort($sort));
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny(['name', 'company', 'email', 'phone'], 'like', "%$search%");
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
            $this->products()->withoutGlobalScopes()->exists() ||
            $this->expenses()->withoutGlobalScopes()->exists() ||
            $this->purchases()->withoutGlobalScopes()->exists() ||
            $this->payments()->withoutGlobalScopes()->exists() ||
            $this->returnOrders()->withoutGlobalScopes()->exists()
        ) {
            return false;
        }

        $this->users()->delete();
        $this->tracks()->delete();

        return parent::delete();
    }

    public function forceDelete()
    {
        if (
            $this->products()->withoutGlobalScopes()->exists() ||
            $this->expenses()->withoutGlobalScopes()->exists() ||
            $this->purchases()->withoutGlobalScopes()->exists() ||
            $this->payments()->withoutGlobalScopes()->exists() ||
            $this->returnOrders()->withoutGlobalScopes()->exists()
        ) {
            return false;
        }

        $this->users()->forceDelete();
        $this->tracks()->forceDelete();

        log_activity(__('{record} has permanently deleted.', ['record' => 'Supplier']), $this, $this, 'Supplier');

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
