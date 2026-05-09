<?php

namespace App\Models\Sma\Product;

use App\Models\Model;
use App\Tec\Traits\HasStock;
use App\Tec\Traits\HasTaxes;
use Spatie\Sluggable\HasSlug;
use App\Tec\Traits\GroupPrice;
use App\Models\Sma\Setting\Tax;
use App\Models\Sma\Setting\Store;
use App\Tec\Traits\HasPromotions;
use Spatie\Sluggable\SlugOptions;
use App\Models\Sma\Order\SaleItem;
use App\Tec\Traits\HasAttachments;
use App\Models\Sma\People\Supplier;
use App\Models\Sma\Order\PurchaseItem;
use App\Tec\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use GroupPrice;
    use HasAttachments;
    use HasFactory;
    use HasPromotions;
    use HasSchemalessAttributes;
    use HasSlug;
    use HasStock;
    use HasTaxes;

    public static $hasSku = true;

    public $casts = ['variants' => 'array'];

    protected $with = ['taxes'];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class)->with('children');
    }

    public function subcategory()
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, null, 'combo_id')->withPivot(['quantity']);
    }

    public function selectedStore()
    {
        return $this->stores()->where('store_id', session('selected_store_id'))
            ->using(ProductStore::class)->withPivot(['price', 'quantity', 'alert_quantity', 'taxes']);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class)
            ->using(ProductStore::class)->withPivot(['price', 'quantity', 'alert_quantity', 'taxes']);
    }

    public function taxes()
    {
        return $this->belongsToMany(Tax::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function serials()
    {
        return $this->hasMany(Serial::class)->orderBy('number');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class)->whereNull('variation_id');
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function usedInRecipes()
    {
        return $this->hasMany(Recipe::class, 'ingredient_id');
    }

    public function storeStock($store = null)
    {
        return $this->hasOne(Stock::class)->ofMany([], fn ($q) => $q->ofStore($store)
        );
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class)->with('subunits');
    }

    public function unitPrices()
    {
        return $this->morphMany(UnitPrice::class, 'subject');
    }

    public function variations()
    {
        return $this->hasMany(Variation::class)->orderBy('sku');
    }

    public function scopeActive($query)
    {
        $query->where('active', 1);
    }

    public function scopeInactive($query)
    {
        $query->whereNull('active')->orWhere('active', 0);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeFilter($query, $filters = [])
    {
        $query->when($filters['trashed'] ?? 'with', fn ($q, $t) => $q->trashed($t))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->ofType($type))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($filters['products'] ?? null, fn ($query, $products) => $query->whereIn('id', $products))
            ->when($filters['category_id'] ?? null, fn ($query, $category) => $query->where('category_id', $category))
            ->when(($filters['store'] ?? null) && get_settings('hide_out_of_stock'), fn ($query, $store) => $query->whereHas('stocks', fn ($q) => $q->ofStore($store)->withTrashed()))
            ->when($filters['reorder'] ?? null, fn ($query) => $query->whereHas('stocks', fn ($q) => $q->whereHasBalanceBelow('alert_quantity')))
            ->when($filters['sort'] ?? null, fn ($query, $sort) => $query->sort($sort));
    }

    public function scopeSearch($query, $search)
    {
        $query->whereAny(['code', 'name', 'description'], 'like', "%$search%")
            ->orWhereRelation('brand', 'name', 'like', "%{$search}%")
            ->orWhereRelation('category', 'name', 'like', "%{$search}%");
    }

    public function scopeSort($query, $sort)
    {
        if ($sort == 'latest') {
            $query->latest();
        } elseif (str($sort)->contains('extra_attributes')) {
            [$relation, $column] = explode('.', $sort);
            [$column, $direction] = explode(':', $column);
            $query->orderByRaw('CAST(JSON_EXTRACT(extra_attributes, "$.' . str($column)->replace(' ', '_') . '") AS CHAR) ' . $direction);
        } else {
            if (str($sort)->contains('.')) {
                $relation_tables = [
                    'brand'    => ['table' => 'brands', 'model' => 'App\Models\Sma\Product\Brand'],
                    'category' => ['table' => 'categories', 'model' => 'App\Models\Sma\Product\Category'],
                    'supplier' => ['table' => 'suppliers', 'model' => 'App\Models\Sma\People\Supplier'],
                ];
                [$relation, $column] = explode('.', $sort);
                [$column, $direction] = explode(':', $column);
                $table = $relation_tables[$relation];
                $query->orderBy($table['model']::select($column)->whereColumn($table['table'] . '.id', 'products.' . $relation . '_id'), $direction);
            } else {
                [$column, $direction] = explode(':', $sort);
                $query->orderBy(str($column)->replace(' ', '_'), $direction);
            }
        }

        return $query;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(50);
    }

    public function getAvailableQuantityFromRecipe(): ?int
    {
        if ($this->dont_track_stock || ! $this->recipes->count()) {
            return null;
        }

        $minQuantity = null;

        foreach ($this->recipes as $recipe) {
            $ingredientStock = $recipe->ingredient->getStock();
            $availableFromIngredient = (int) floor($ingredientStock / $recipe->quantity);

            if ($minQuantity === null || $availableFromIngredient < $minQuantity) {
                $minQuantity = $availableFromIngredient;
            }
        }

        return $minQuantity;
    }

    public function delete()
    {
        if ($this->saleItems()->exists() || $this->purchaseItems()->exists() || $this->stocks->sum('balance') > 0) {
            return false;
        }

        $this->taxes()->detach();
        $this->stores()->detach();
        $this->serials()->delete();
        $this->recipes()->delete();
        $this->products()->detach();
        $this->variations()->delete();
        $this->stocks->each->delete();

        return parent::delete();
    }

    public function forceDelete()
    {
        if ($this->saleItems()->exists() || $this->purchaseItems()->exists() || $this->stocks->sum('balance') > 0) {
            return false;
        }

        $this->taxes()->detach();
        $this->stores()->detach();
        $this->recipes()->delete();
        $this->products()->detach();
        $this->serials()->forceDelete();
        $this->variations()->forceDelete();
        $this->stocks->each->forceDelete();

        log_activity(__('{record} has permanently deleted.', ['record' => 'Product']), $this, $this, 'Product');

        return parent::forceDelete();
    }

    public function getStock($store_id = null)
    {
        return $this->getProductStock($store_id);
    }

    public function adjustStock($type, $quantity, $data)
    {
        $this->adjustProductStock($type, $quantity, $data);
    }

    public function setStock()
    {
        $this->setProductStock();
    }

    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope('active', function ($builder) {
            $builder->when(! request()->routeIs('products.*'), fn ($q) => $q->where('active', 1));
        });

        static::retrieved(function (Product $product) {
            $user = auth()->user();
            if ($user && $user->cant('show-cost')) {
                $product->setHidden(['cost']);
            }
        });

        static::created(function ($model) {
            $model->setStock();
        });
    }
}
