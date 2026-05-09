<?php

namespace App\Models\Sma\Setting;

use App\Models\Model;
use App\Models\Sma\Order\SaleItem;
use App\Models\Sma\Product\Product;
use App\Models\Sma\Order\PurchaseItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tax extends Model
{
    use HasFactory;

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function purchaseItems()
    {
        return $this->belongsToMany(PurchaseItem::class);
    }

    public function saleItems()
    {
        return $this->belongsToMany(SaleItem::class);
    }

    public function delete()
    {
        if ($this->products()->exists() || $this->saleItems()->exists() || $this->purchaseItems()->exists()) {
            return false;
        }

        return parent::delete();
    }

    public function forceDelete()
    {
        if ($this->products()->exists() || $this->saleItems()->exists() || $this->purchaseItems()->exists()) {
            return false;
        }

        log_activity(__('{model} has been successfully {action}.', [
            'model'  => __('Tax'),
            'action' => __('deleted'),
        ]), $this, $this, 'Tax');

        return parent::forceDelete();
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny(['code', 'name'], 'like', "%$search%");
    }
}
