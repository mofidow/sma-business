<?php

namespace Modules\Shop\Http\Livewire;

use Throwable;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\Sma\Product\Brand;
use App\Models\Sma\Product\Category;
use App\Models\Sma\Product\Subcategory;
use Modules\Shop\Models\Product as ShopProduct;

class Products extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    public array $filters = [
        'trashed'       => 'not',
        'in_stock'      => false,
        'featured'      => false,
        'on_promo'      => false,
        'min_price'     => null,
        'max_price'     => null,
        'brands'        => [],
        'categories'    => [],
        'subcategories' => [],
    ];

    public function mount()
    {
        try {
            $this->filters = array_merge($this->filters, request()->query('filters', []));
            $this->filters['in_stock'] = (bool) $this->filters['in_stock'];
            $this->filters['on_promo'] = (bool) $this->filters['on_promo'];
            $this->filters['featured'] = (bool) $this->filters['featured'];
        } catch (Throwable $th) {
            $this->filters = [
                'trashed'       => 'not',
                'in_stock'      => false,
                'featured'      => false,
                'on_promo'      => false,
                'min_price'     => null,
                'max_price'     => null,
                'brands'        => [],
                'categories'    => [],
                'subcategories' => [],
            ];
        }
    }

    public function render()
    {
        $brands = null;
        $categories = null;
        $subcategories = null;
        $settings = get_settings('general');

        if (! empty($this->filters['brands'])) {
            $brands = Brand::whereIn('id', $this->filters['brands'])->get(['id', 'name', 'slug']);
        }
        if (! empty($this->filters['categories'])) {
            $categories = Category::whereIn('id', $this->filters['categories'])->get(['id', 'name', 'slug']);
        }
        if (! empty($this->filters['subcategories'])) {
            $subcategories = Subcategory::whereIn('id', $this->filters['subcategories'])->get(['id', 'name', 'slug']);
        }

        return view('shop::pages.products', [
            'brands'        => $brands,
            'categories'    => $categories,
            'subcategories' => $subcategories,
            'products'      => ShopProduct::selectColumns()->with([
                'brand:id,name,slug', 'category:id,name,slug', 'subcategory:id,name,slug',
                'validPromotions', 'storeStock', 'variations', 'variations.storeStock',
            ])
                ->when($this->search, function ($query) {
                    $query->whereAny(['name', 'description'], 'like', '%' . $this->search . '%');
                })
                ->shopFilters($this->filters)->orderBy('name')
                ->paginate($settings['products_per_page'] ?? 24)->withQueryString(),
        ]);
    }
}
