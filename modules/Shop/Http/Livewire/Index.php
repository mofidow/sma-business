<?php

namespace Modules\Shop\Http\Livewire;

use Livewire\Component;
use Modules\Shop\Models\Product;
use App\Models\Sma\Product\Brand;
use App\Models\Sma\Product\Category;

class Index extends Component
{
    public function render()
    {
        return view('shop::pages.index', [
            'total_brands'        => Brand::count(),
            'total_products'      => Product::count(),
            'total_categories'    => Category::count(),
            'shop_index_settings' => get_settings(['shop_slider', 'shop_cta']),
            'brands'              => Brand::select(['id', 'name', 'slug', 'photo'])->active()->orderBy('order')->take(10)->get(),
            'categories'          => Category::select(['id', 'name', 'slug', 'photo'])->active()->orderBy('order')->take(10)->get(),
            'featured_products'   => Product::selectColumns()->with([
                'brand:id,name,slug', 'category:id,name,slug', 'subcategory:id,name,slug',
                'storeStock', 'variations.storeStock',
            ])->where('featured', true)->inRandomOrder()->take(12)->get(),
            'latest_products' => Product::selectColumns()->with([
                'brand:id,name,slug', 'category:id,name,slug', 'subcategory:id,name,slug',
                'storeStock', 'variations.storeStock',
            ])->orderByDesc('created_at')->take(12)->get(),
        ]);
    }
}
