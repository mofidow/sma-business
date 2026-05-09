<?php

namespace App\Models;

use App\Tec\Casts\AppDate;
use App\Tec\Core\Notifiable;
use App\Models\Sma\Pos\Order;
use App\Models\Sma\Order\Sale;
use App\Tec\Traits\Impersonate;
use App\Models\Sma\Pos\Register;
use App\Models\Sma\Setting\Store;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Sma\Order\Purchase;
use App\Models\Sma\People\Address;
use App\Tec\Traits\HasAwardPoints;
use App\Models\Sma\People\Customer;
use App\Models\Sma\People\Supplier;
use App\Models\Sma\People\UserSetting;
use App\Models\Sma\Repair\RepairOrder;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Pennant\Concerns\HasFeatures;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasAwardPoints;
    use HasFactory;
    use HasFeatures;
    use HasProfilePhoto;
    use HasRoles;
    use Impersonate;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name', 'phone', 'email', 'username', 'password', 'locale',
        'customer_id', 'supplier_id', 'store_id', 'employee', 'active',
        'bulk_actions', 'can_be_impersonated', 'telegram_user_id',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret',
    ];

    protected $appends = ['profile_photo_url', 'points', 'allPermissions'];

    protected function casts(): array
    {
        return [
            'password'          => 'hashed',
            'email_verified_at' => AppDate::class . ':time',
            'created_at'        => AppDate::class . ':time',
            'updated_at'        => AppDate::class . ':time',
        ];
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function openedRegister()
    {
        return $this->hasOne(Register::class)->whereNull('closed_at');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class);
    }

    public function registers()
    {
        return $this->hasMany(Register::class);
    }

    public function repairOrders()
    {
        return $this->hasMany(RepairOrder::class);
    }

    public function settings()
    {
        return $this->hasMany(UserSetting::class);
    }

    public function scopeActive($query)
    {
        $query->where('active', 1);
    }

    public function scopeEmployee($query)
    {
        $query->where('employee', 1);
    }

    public function scopeNotEmployee($query)
    {
        $query->whereNull('employee')->orWhere('employee', 0);
    }

    public function scopeFilter($query, $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->roles($role))
            ->when($filters['sort'] ?? null, fn ($query, $sort) => $query->sort($sort));
    }

    public function scopeRoles($query, $role)
    {
        return match ($role) {
            ''             => $query,
            'employee'     => $query->employee(),
            'non-employee' => $query->notEmployee(),
            default        => $query->role($role),
        };
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny(['name', 'phone', 'email', 'username'], 'like', "%$search%");
    }

    public function scopeSort($query, $sort)
    {
        if ($sort == 'latest') {
            $query->latest();
        } elseif (str($sort)->contains('.')) {
            [$relation, $column] = explode('.', $sort);
            [$column, $direction] = explode(':', $column);
            $query->withAggregate($relation, $column)->orderBy($relation . '_' . $column, $direction);
        } else {
            [$column, $direction] = explode(':', $sort);
            $query->orderBy($column, $direction);
        }
    }

    public function scopeTrashed($query, $value)
    {
        if (in_array($value, ['with', 'only'])) {
            return $query->{$value . 'Trashed'}();
        }

        return $query;
    }

    protected function getAllPermissionsAttribute()
    {
        return $this->getAllPermissions()->pluck('name');
    }

    public function routeNotificationForTelegram(): ?string
    {
        return $this->telegram_user_id;
    }

    public function routeNotificationForTwilio(): ?string
    {
        return $this->phone;
    }
}
