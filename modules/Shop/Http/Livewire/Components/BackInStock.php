<?php

namespace Modules\Shop\Http\Livewire\Components;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Modules\Shop\Models\ShopBackInStockSubscription;

class BackInStock extends Component
{
    public int $productId;

    public ?int $variationId = null;

    public bool $isOutOfStock = false;

    #[Validate('required|email|max:255')]
    public string $email = '';

    public bool $subscribed = false;

    public function mount(int $productId, bool $initialIsOutOfStock = false, ?int $variationId = null): void
    {
        $this->productId = $productId;
        $this->variationId = $variationId;
        $this->isOutOfStock = $initialIsOutOfStock;

        if ($user = auth()->user()) {
            $this->email = $user->email;

            $this->subscribed = ShopBackInStockSubscription::where('product_id', $this->productId)
                ->where('variation_id', $this->variationId)
                ->where('user_id', $user->id)->exists();
        }
    }

    public function render()
    {
        return view('shop::components.back-in-stock');
    }

    public function subscribe(?int $variationId = null): void
    {
        $this->validate();

        if ($variationId !== null) {
            $this->variationId = $variationId;
        }

        $existing = ShopBackInStockSubscription::where('product_id', $this->productId)
            ->where('variation_id', $this->variationId)
            ->where('email', $this->email)
            ->exists();

        if (! $existing) {
            ShopBackInStockSubscription::create([
                'product_id'   => $this->productId,
                'variation_id' => $this->variationId,
                'email'        => $this->email,
                'user_id'      => auth()->id(),
            ]);
        }

        $this->dispatch('back-in-stock-subscribed');

        $this->dispatch('notify',
            type: 'success',
            content: __("We'll notify you when this product is back in stock."),
        );
    }
}
