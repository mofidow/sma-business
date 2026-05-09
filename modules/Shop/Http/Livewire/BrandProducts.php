<?php

namespace Modules\Shop\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Shop\Models\Product;
use App\Models\Sma\Product\Brand;

class BrandProducts extends Component
{
    use WithPagination;

    public Brand $brand;

    public function mount($slug)
    {
        $this->brand = Brand::where('slug', $slug)->firstOrFail();

        return redirect()->route('shop.products', ['filters' => ['brands' => [$this->brand->id]]]);
    }

    public function render()
    {
        $settings = get_settings('general');

        return view('shop::pages.products', [
            'brand'    => $this->brand,
            'products' => Product::select([
                'id', 'code', 'name', 'photo', 'price', 'slug',
                'description', 'brand_id', 'category_id', 'subcategory_id',
            ])->with([
                'brand:id,name,slug', 'category:id,name,slug', 'subcategory:id,name,slug',
            ])->where('brand_id', $this->brand->id)
                ->paginate($settings['products_per_page'] ?? 24)->withQueryString(),
        ]);
    }
}
