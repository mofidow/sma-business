<?php

namespace App\Models\Sma\Accounting;

use App\Models\Model;
use App\Tec\Traits\Trackable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;
    use Trackable;

    protected $appends = ['balance'];

    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }

    public function scopeActive($query)
    {
        $query->where('active', 1);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny(['title', 'details'], 'like', "%$search%")
            ->orWhereHas('accountType', fn ($q) => $q->search($search));
    }

    public function scopeFilter($query, $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->search($search))
            ->when($filters['account_type_id'] ?? null, fn ($q, $id) => $q->where('account_type_id', $id));
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
