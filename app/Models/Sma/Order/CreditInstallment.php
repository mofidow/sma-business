<?php

namespace App\Models\Sma\Order;

use App\Models\User;
use App\Models\Model;
use App\Tec\Casts\AppDate;
use App\Models\Sma\People\Customer;
use App\Models\Sma\Setting\Store;
use App\Tec\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditInstallment extends Model
{
    use HasFactory;
    use HasSchemalessAttributes;
    use SoftDeletes;

    protected $table = 'credit_installments';

    protected function casts(): array
    {
        return [
            'extra_attributes' => 'array',
            'due_date'         => AppDate::class,
            'paid_date'        => AppDate::class,
            'created_at'       => AppDate::class . ':time',
            'updated_at'       => AppDate::class . ':time',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
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

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(fn ($q) => $q->where('status', 'pending')->where('due_date', '<', now()->toDateString()));
    }

    public function scopeDueToday($query)
    {
        return $query->where('status', 'pending')->where('due_date', now()->toDateString());
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date && $this->due_date->lt(now());
    }

    public function daysOverdue(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        return (int) now()->diffInDays($this->due_date);
    }
}
