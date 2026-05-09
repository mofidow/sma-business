<?php

namespace Modules\Shop\Tec;

use Illuminate\View\View;
use App\Models\Sma\Product\Brand;
use App\Models\Sma\Product\Category;

class ShopComposer
{
    public function compose(View $view): void
    {
        $view->with('seo', cache()->flexible('shop_seo', [5, 10], fn () => get_settings('seo')));

        $view->with('settings', cache()->flexible('settings', [5, 10], fn () => get_public_settings()));

        $view->with('shop_settings', cache()->flexible('shop_settings', [5, 10], function () {
            $settings = get_settings('general');
            session(['selected_store_id' => $settings['store_id'] ?? null]);

            return $settings;
        }));

        $view->with('brandsMenu', cache()->flexible('shop_brands', [5, 10], fn () => Brand::select(['id', 'name', 'slug', 'photo'])->active()->withCount('products')->has('products')->orderBy('order')->get()));

        $view->with('categoriesMenu', cache()->flexible('shop_categories', [5, 10], fn () => Category::select(['id', 'name', 'slug', 'photo'])->active()->withCount('products')->onlyParent()->with(['children' => fn ($q) => $q->select(['id', 'name', 'slug', 'photo', 'category_id'])->active()->has('childProducts')->withCount('childProducts')])->orderBy('order')->get()));
    }
}
