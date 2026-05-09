<?php

namespace App\Models\Sma\Order;

use App\Models\Model;
use App\Tec\Casts\AppDate;
use App\Models\Sma\Setting\Store;
use App\Models\Sma\Product\Product;
use App\Models\Sma\Product\Variation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CostAllocation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'created_at' => AppDate::class . ':time',
            'updated_at' => AppDate::class . ':time',
        ];
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(Variation::class);
    }

    public function returnOrderItem(): BelongsTo
    {
        return $this->belongsTo(ReturnOrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeOfSaleItem(Builder $query, int $saleItemId): Builder
    {
        return $query->where('sale_item_id', $saleItemId);
    }

    public function scopeOfPurchaseItem(Builder $query, int $purchaseItemId): Builder
    {
        return $query->where('purchase_item_id', $purchaseItemId);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
