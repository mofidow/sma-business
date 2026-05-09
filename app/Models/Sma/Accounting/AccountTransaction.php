<?php

namespace App\Models\Sma\Accounting;

use App\Models\User;
use App\Models\Model;

class AccountTransaction extends Model
{
    protected $table = 'account_transactions';

    public static $hasReference = true;

    public static $hasUser = true;

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny(['reference', 'note'], 'like', "%$search%")
            ->orWhereHas('account', fn ($q) => $q->search($search));
    }

    public function scopeFilter($query, $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->search($search))
            ->when($filters['account_id'] ?? null, fn ($q, $id) => $q->where('account_id', $id))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type));
    }

    public function isDebit(): bool
    {
        return $this->type === 'debit';
    }

    public function isCredit(): bool
    {
        return $this->type === 'credit';
    }

    protected static function booted()
    {
        parent::booted();

        static::created(function ($transaction) {
            if ($transaction->isDebit()) {
                $transaction->account->decreaseBalance($transaction->amount, [
                    'description' => "Debit transaction: {$transaction->reference}",
                    'reference'   => $transaction,
                ]);
            } else {
                $transaction->account->increaseBalance($transaction->amount, [
                    'description' => "Credit transaction: {$transaction->reference}",
                    'reference'   => $transaction,
                ]);
            }
        });
    }
}
