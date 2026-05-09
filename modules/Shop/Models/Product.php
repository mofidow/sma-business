<?php

namespace Modules\Shop\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Sma\Product\Product as SMAProduct;

class Product extends SMAProduct
{
    protected $selectColumns = [
        'id', 'code', 'name', 'slug', 'type', 'photo', 'video', 'description', 'price',
        'has_serials', 'has_variants', 'variants', 'tax_included',
        'brand_id', 'category_id',  'subcategory_id', 'unit_id',
    ];

    public function scopeSelectColumns($query)
    {
        return $query->select($this->selectColumns);
    }

    public function scopeShopFilters($query, $filters = [])
    {
        $query->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($filters['store'] ?? null, fn ($query, $store) => $query->whereHas('stocks', fn ($q) => $q->ofStore($store)->withTrashed()))
            ->when($filters['in_stock'] ?? null, fn ($query) => $query->whereHas('stocks', fn ($q) => $q->whereHasBalance()))
            ->when($filters['featured'] ?? null, fn ($query) => $query->where('featured', true))
            ->when($filters['on_promo'] ?? null, fn ($query) => $query->has('validPromotions'))
            ->when($filters['min_price'] ?? null, fn ($query, $price) => $query->where('price', '>=', $price)) // TODO: store price
            ->when($filters['max_price'] ?? null, fn ($query, $price) => $query->where('price', '<=', $price)) // TODO: store price
            ->when($filters['brands'] ?? null, fn ($query, $brands) => $query->whereIn('brand_id', $brands))
            ->when($filters['categories'] ?? null, fn ($query, $categories) => $query->whereIn('category_id', $categories))
            ->when($filters['subcategories'] ?? null, fn ($query, $subcategories) => $query->whereIn('subcategory_id', $subcategories))
            ->when($filters['sort'] ?? null, fn ($query, $sort) => $query->sort($sort));
    }

    public function scopeSort($query, $sort)
    {
        $query->reorder();
        match ($sort) {
            'name_asc'    => $query->orderBy('name', 'asc'),
            'name_desc'   => $query->orderBy('name', 'desc'),
            'rating_asc'  => $query->orderBy('rating', 'asc'),
            'rating_desc' => $query->orderBy('rating', 'desc'),
            'latest'      => $query->latest(),
            'oldest'      => $query->oldest(),
            default       => $query->latest(),
        };

        return $query;
    }

    public function getMorphClass()
    {
        return parent::class;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope('shop', function (Builder $builder) {
            $builder->where(fn ($q) => $q->whereNull('hide_in_shop')->orWhere('hide_in_shop', 0));
        });
    }
}
