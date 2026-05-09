<?php

namespace App\Models\Sma\Product;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'ingredient_id', 'quantity', 'unit_id', 'sort_order',
    ];

    public $casts = [
        'quantity'   => 'decimal:4',
        'sort_order' => 'integer',
    ];

    protected $with = ['ingredient:id,name,code,cost', 'unit:id,name,code'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Product::class, 'ingredient_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function scopeOfProduct($query, $product_id)
    {
        return $query->where('product_id', $product_id);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
