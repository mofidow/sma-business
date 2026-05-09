<?php

namespace Modules\Shop\Http\Livewire;

use Livewire\Component;
use Modules\Shop\Models\ShopRecentView;
use Modules\Shop\Models\Product as ShopProduct;

class Product extends Component
{
    public ShopProduct $product;

    public function mount($slug)
    {
        $this->product = ShopProduct::with([
            'attachments',
            'brand:id,name,slug,photo,description',
            'category:id,name,slug,photo,description',
            'subcategory:id,name,slug,photo,description',
            'storeStock', 'variations', 'variations.storeStock',
        ])->where('slug', $slug)->firstOrFail();
    }

    public function render()
    {
        $user = auth()->user();
        $data = [
            'user_id'    => $user?->id,
            'ip_address' => request()->ip(),
            'product_id' => $this->product->id,
        ];
        ShopRecentView::updateOrCreate($data, $data);

        $preSelectedVariation = null;
        if ($variationId = request()->query('variation_id')) {
            $preSelectedVariation = $this->product->variations->firstWhere('id', $variationId);
        }

        $isOutOfStock = ! $this->product->dont_track_stock && (
            $preSelectedVariation
                ? ($preSelectedVariation->storeStock?->balance ?? 0) <= 0
                : ($this->product->has_variants
                    ? $this->product->variations->every(fn ($v) => ($v->storeStock?->balance ?? 0) <= 0)
                    : ($this->product->storeStock?->balance ?? 0) <= 0)
        );

        return view('shop::pages.product', [
            'is_out_of_stock'  => $isOutOfStock,
            'variation_id'     => $preSelectedVariation?->id,
            'return_policy'    => get_settings('return_policy'),
            'shipping_policy'  => get_settings('shipping_policy'),
            'related_products' => ShopProduct::query()
                ->where(function ($query) {
                    $query->when(
                        $this->product->subcategory_id,
                        fn ($query) => $query->where('subcategory_id', $this->product->subcategory_id)
                    )
                        ->where('category_id', $this->product->category_id)
                        ->orWhere('brand_id', $this->product->brand_id);
                })
                ->where('id', '!=', $this->product->id)->inRandomOrder()->take(4)->get(),
        ]);
    }
}
