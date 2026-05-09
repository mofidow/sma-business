<?php

namespace App\Models\Sma\Order;

use App\Models\User;
use App\Models\Model;
use App\Tec\Casts\AppDate;
use App\Tec\Traits\Trackable;
use App\Models\Sma\Setting\Store;
use App\Models\Sma\People\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiftCard extends Model
{
    use HasFactory;
    use Trackable;

    public static $hasStore = true;

    public static $hasUser = true;

    public $with = ['customer:id,company,name', 'store:id,name'];

    protected $appends = ['balance'];

    protected function casts(): array
    {
        return [
            'expiry_date' => AppDate::class,
            'created_at'  => AppDate::class . ':time',
            'updated_at'  => AppDate::class . ':time',
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

    public function forceDelete()
    {
        log_activity(__('{record} has permanently deleted.', ['record' => 'Gift card']), $this, $this, 'Gift card');

        return parent::forceDelete();
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($filters['sort'] ?? null, fn ($query, $sort) => $query->sort($sort));
    }

    public function scopeSearch($query, $s)
    {
        $query->where(fn ($q) => $q->where('number', 'like', "%{$s}%"))
            ->orWhereHas('customer', fn ($q) => $q->search($s));
    }

    protected static function booted()
    {
        parent::booted();
        static::created(function ($model) {
            $model->mutateTracking($model->amount, [
                'reference'   => auth()->user(),
                'description' => 'Gift card created',
            ]);

            if ($model->customer && $model->award_points && $model->use_award_points) {
                $model->customer?->awardPoints()->create([
                    'gift_card_id' => $model->id,
                    'store_id'     => $model->store_id,
                    'value'        => 0 - $model->award_points,
                    'details'      => 'Award points used for gift card #' . $model->number,
                ]);
            }
        });
    }
}
