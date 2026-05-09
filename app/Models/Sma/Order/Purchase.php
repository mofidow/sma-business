<?php

namespace App\Models\Sma\Order;

use App\Models\User;
use App\Models\Model;
use App\Tec\Casts\AppDate;
use App\Tec\Scopes\OfStore;
use App\Models\Sma\Setting\Tax;
use App\Models\Sma\Setting\Store;
use App\Tec\Policies\UpdatePolicy;
use App\Tec\Traits\HasAttachments;
use App\Tec\Traits\SupplierRecord;
use App\Models\Sma\People\Supplier;
use App\Tec\Observers\PurchaseObserver;
use App\Tec\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[ScopedBy(OfStore::class)]
#[UsePolicy(UpdatePolicy::class)]
#[ObservedBy(PurchaseObserver::class)]
class Purchase extends Model
{
    use HasAttachments;
    use HasFactory;
    use HasSchemalessAttributes;
    use SupplierRecord;

    public static $hasUser = true;

    public static $hasStore = true;

    public static $hasRegister = true;

    public static $userRecords = true;

    public static $hasReference = true;

    protected $setHash = true;

    protected function casts(): array
    {
        return [
            'extra_attributes' => 'array',
            'date'             => AppDate::class,
            'created_at'       => AppDate::class . ':time',
            'updated_at'       => AppDate::class . ':time',
        ];
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): MorphToMany
    {
        return $this->morphToMany(Payment::class, 'payable')
            ->withPivot('amount')->withoutGlobalScope(OfStore::class);
    }

    public function returnOrders()
    {
        return $this->hasMany(ReturnOrder::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function taxes()
    {
        return $this->belongsToMany(Tax::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if model is paid
     */
    public function isPaid()
    {
        return $this->grand_total >= $this->paid;
    }

    /**
     * Check if model is unpaid
     */
    public function isUnpaid()
    {
        return $this->grand_total < $this->paid;
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($filters['end'] ?? null, fn ($query, $end) => $query->where('created_at', '<=', $end))
            ->when($filters['start'] ?? null, fn ($query, $start) => $query->where('created_at', '>=', $start))
            ->when($filters['unpaid'] ?? null, fn ($query) => $query->unpaid())
            ->when($filters['user_id'] ?? null, fn ($query, $user_id) => $query->ofUser($user_id))
            ->when($filters['store_id'] ?? null, fn ($query, $store_id) => $query->ofStore($store_id))
            ->when($filters['supplier_id'] ?? null, fn ($query, $supplier_id) => $query->ofSupplier($supplier_id))
            ->when($filters['products'] ?? null, fn ($query, $products) => $query->whereRelation('items', 'product_id', $products))
            ->when($filters['reference'] ?? null, fn ($query, $reference) => $query->where('reference', 'like', "%{$reference}%"))
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query->where('date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query->where('date', '<=', $date))
            ->when($filters['sort'] ?? null, fn ($query, $sort) => $query->sort($sort));
    }

    public function scopeOfSupplier($query, $supplier_id)
    {
        $query->where('supplier_id', $supplier_id);
    }

    public function scopeOfStore($query, $store_id = null)
    {
        $query->where('store_id', $store_id ?: session('selected_store_id'));
    }

    public function scopeOfUser($query, $user_id = null)
    {
        $query->where('user_id', $user_id ?: auth()->id());
    }

    public function scopePaid($query)
    {
        $query->whereRaw('paid >= grand_total');
    }

    public function scopeUnpaid($query)
    {
        $query->whereNull('paid')->orWhereRaw('paid < grand_total');
    }

    public function scopeSearch($query, $s)
    {
        $query->where(fn ($q) => $q->where('date', 'like', "%{$s}%")
            ->orWhere('reference', 'like', "%{$s}%"))
            ->orWhereHas('supplier', fn ($q) => $q->search($s));
    }

    public function forceDelete()
    {
        log_activity(__('{record} has permanently deleted.', ['record' => 'Purchase']), $this, $this, 'Purchase');
        $this->items()->withTrashed()->get()->each->forceDelete();
        $this->payments()->withTrashed()->get()->each->forceDelete();
        $this->returnOrders()->withTrashed()->get()->each->forceDelete();

        return parent::forceDelete();
    }
}
