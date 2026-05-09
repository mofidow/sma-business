<?php

namespace Modules\Shop\Http\Components;

use Closure;
use Illuminate\View\Component;
use App\Models\Sma\Product\Product;
use Illuminate\Contracts\View\View;
use Modules\Shop\Models\ShopCurrency;
use Modules\Shop\Models\ShopRecentView;

class Header extends Component
{
    public function render(): View|Closure|string
    {
        $user = auth()->user();

        return view('shop::components.header', [
            'user'                 => $user,
            'shop_header_settings' => get_settings(['logo', 'logo_dark', 'page_menus', 'brands_article', 'general', 'notification']),
            'currencies'           => cache()->flexible('shop_currencies', [5, 10], fn () => ShopCurrency::all()),
            'featured'             => Product::select([
                'id', 'code', 'name', 'photo', 'price', 'slug',
                'description', 'brand_id', 'category_id', 'subcategory_id',
            ])->with([
                'brand:id,name,slug', 'category:id,name,slug', 'subcategory:id,name,slug',
            ])->where('featured', true)->inRandomOrder()->first(),
            'recent_views' => ShopRecentView::ofUserOrIp($user, request()->ip())->with([
                'product' => fn ($query) => $query->select([
                    'id', 'code', 'name', 'photo', 'price', 'slug',
                    'description', 'brand_id', 'category_id', 'subcategory_id',
                ])->with([
                    'brand:id,name,slug', 'category:id,name,slug', 'subcategory:id,name,slug',
                ]),
            ])->latest('updated_at')->take(4)->get(),
        ]);
    }
}
