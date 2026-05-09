<?php

namespace App\Models\Sma\Accounting;

use App\Models\User;
use App\Models\Model;

class AccountTransfer extends Model
{
    protected $table = 'account_transfers';

    public static $hasReference = true;

    public static $hasUser = true;

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny(['reference', 'note'], 'like', "%$search%")
            ->orWhereHas('fromAccount', fn ($q) => $q->search($search))
            ->orWhereHas('toAccount', fn ($q) => $q->search($search));
    }

    public function scopeFilter($query, $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->search($search))
            ->when($filters['from_account_id'] ?? null, fn ($q, $id) => $q->where('from_account_id', $id))
            ->when($filters['to_account_id'] ?? null, fn ($q, $id) => $q->where('to_account_id', $id));
    }

    protected static function booted()
    {
        parent::booted();

        static::created(function ($transfer) {
            $transfer->fromAccount->decreaseBalance($transfer->amount, [
                'description' => "Transfer to {$transfer->toAccount->title}",
                'reference'   => $transfer,
            ]);

            $transfer->toAccount->increaseBalance($transfer->amount, [
                'description' => "Transfer from {$transfer->fromAccount->title}",
                'reference'   => $transfer,
            ]);
        });
    }
}
