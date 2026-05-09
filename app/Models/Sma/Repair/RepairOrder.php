<?php

namespace App\Models\Sma\Repair;

use App\Models\User;
use App\Models\Model;
use App\Tec\Casts\AppDate;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Setting\Tax;
use App\Models\Sma\Setting\Store;
use App\Models\Sma\People\Customer;
use App\Tec\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RepairOrder extends Model
{
    use HasFactory, HasSchemalessAttributes, SoftDeletes;

    public static $userRecords = true;

    protected function casts(): array
    {
        return [
            'extra_attributes' => 'array',
            'price'            => 'decimal:4',
            'actual_cost'      => 'decimal:4',
            'tax_amount'       => 'decimal:4',
            'tax_included'     => 'boolean',
            'received_date'    => AppDate::class,
            'due_date'         => AppDate::class,
            'completed_date'   => AppDate::class,
            'created_at'       => AppDate::class . ':time',
            'updated_at'       => AppDate::class . ':time',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Sale::class, 'invoice_id');
    }

    public function attachments()
    {
        return $this->hasMany(RepairOrderAttachment::class);
    }

    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'repair_order_tax')
            ->withPivot('amount')
            ->withTimestamps();
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForTechnician($query, $technicianId)
    {
        return $query->where('technician_id', $technicianId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'delivered', 'cancelled']);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('reference', 'like', "%{$search}%")
                ->orWhere('serial_no', 'like', "%{$search}%")
                ->orWhere('problem_description', 'like', "%{$search}%")
                ->orWhereHas('serviceType', fn ($q) => $q->search($search))
                ->orWhereHas('customer', fn ($q) => $q->search($search))
                ->orWhereHas('technician', fn ($q) => $q->search($search));
        });
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['priority'] ?? null, fn ($query, $priority) => $query->where('priority', $priority))
            ->when($filters['service_type_id'] ?? null, fn ($query, $id) => $query->where('service_type_id', $id))
            ->when($filters['technician_id'] ?? null, fn ($query, $id) => $query->where('technician_id', $id))
            ->when($filters['customer_id'] ?? null, fn ($query, $id) => $query->where('customer_id', $id))
            ->when($filters['store_id'] ?? null, fn ($query, $id) => $query->where('store_id', $id))
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query->whereDate('received_date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query->whereDate('received_date', '<=', $date))
            ->when($filters['overdue'] ?? false, fn ($query) => $query->overdue())
            ->when($filters['sort'] ?? null, function ($query, $sort) {
                if ($sort == 'latest') {
                    $query->latest();
                } else {
                    [$field, $direction] = explode(':', $sort);
                    $query->orderBy($field, $direction);
                }
            });
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'delivered']);
    }

    public function canGenerateInvoice(): bool
    {
        return $this->status === 'completed' && ! $this->invoice_id;
    }

    // public function getStatusColorAttribute(): string
    // {
    //     return match ($this->status) {
    //         'pending'       => 'gray',
    //         'in_progress'   => 'blue',
    //         'waiting_parts' => 'yellow',
    //         'completed'     => 'green',
    //         'delivered'     => 'green',
    //         'cancelled'     => 'red',
    //         default         => 'gray',
    //     };
    // }

    // public function getPriorityColorAttribute(): string
    // {
    //     return match ($this->priority) {
    //         'low'    => 'gray',
    //         'normal' => 'blue',
    //         'high'   => 'orange',
    //         'urgent' => 'red',
    //         default  => 'blue',
    //     };
    // }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($repairOrder) {
            $repairOrder->hash = str()->ulid();
            $repairOrder->user_id = auth()->id();
            if (empty($repairOrder->reference)) {
                $repairOrder->reference = get_reference($repairOrder);
            }
            if (! $repairOrder->store_id) {
                $repairOrder->store_id = session('selected_store_id');
            }
            if (empty($repairOrder->received_date)) {
                $repairOrder->received_date = now();
            }
        });
    }
}
