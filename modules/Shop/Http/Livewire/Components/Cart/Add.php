<?php

namespace Modules\Shop\Http\Livewire\Components\Cart;

use Livewire\Component;
use Modules\Shop\Models\Product;
use Modules\Shop\Models\ShopCartItem;
use Modules\Shop\Tec\Actions\FreeItem;

class Add extends Component
{
    public $popup_variant;

    public $cartId;

    public $product;

    public $oId;

    public $quantity = 1;

    public $selected = ['variants' => [], 'variations' => []];

    public $showModal = false;

    public $size;

    public $variation;

    public function add($product_id, $quantity = 1, $oId = null)
    {
        $this->product = Product::selectColumns()->find($product_id);
        if ($this->product->has_variants && $this->product->variants) {
            $this->variation = $this->product->variations->first();
        }
        $this->oId = $oId;
        $this->quantity = $quantity;

        return $this;
    }

    public function mount($product, $size = 'normal', $popup_variant = false)
    {
        $this->size = $size;
        $this->product = $product;
        $this->popup_variant = $popup_variant;
        $this->cartId = request()->cookie('cart_id');
        if ($this->product->has_variants && $this->product->variants) {
            foreach ($this->product->variants as $variant) {
                $this->selected['variants'][$variant['name']] = null;
            }

            if ($queryVariation = request()->query('variation')) {
                foreach ($this->product->variants as $variant) {
                    if (isset($queryVariation[$variant['name']])) {
                        $this->selected['variants'][$variant['name']] = $queryVariation[$variant['name']];
                    }
                }

                if ($variation = $this->product->variations->where('meta', $this->selected['variants'])->first()) {
                    $this->variation = $variation;
                }
            }
        }
    }

    public function render()
    {
        return view('shop::components.cart.add', ['product' => $this->product]);
    }

    public function submit()
    {
        $error = false;
        // Session has cart_id
        if ($this->cartId = request()->cookie('cart_id')) {
            if ($this->product->has_variants && $this->product->variants) {
                $variant_error = [];
                foreach ($this->product->variants as $variant) {
                    if (empty($this->selected['variants'][$variant['name']])) {
                        $variant_error[] = __('Please select :variant', ['variant' => $variant['name']]);
                        $error = true;
                    }
                }
                if ($error) {
                    $this->dispatch('notify', type: 'error', content: implode(', ', $variant_error));
                }
            }

            if (! $error) {
                $data = ['product_id' => $this->product->id, 'cart_id' => request()->cookie('cart_id')];
                $cartItem = ShopCartItem::where($data)->whereNull('oId')->first();

                if ($this->product->has_variants && $this->product->variants) {
                    if (! empty($this->variation)) {
                        $exists = false;
                        $variant_quantity = $this->quantity;
                        if (! empty($cartItem->selected['variations'] ?? [])) {
                            foreach ($cartItem->selected['variations'] as $cisv) {
                                if ($cisv['id'] == $this->variation->id) {
                                    $variant_quantity = $cisv['quantity'] + $this->quantity;
                                }
                            }
                        }
                        $variation_stock = $this->variation->storeStock()->first();
                        if (! $variation_stock) {
                            $this->dispatch('notify',
                                type: 'error',
                                content: $this->errorMessage($variant_quantity, 0, $cartItem, meta_array_to_string($this->variation->meta)),
                            );

                            // return false;
                            return to_route('shop.product', [
                                'slug'         => $this->product->slug,
                                'variation_id' => $this->variation->id,
                                'variation'    => $this->variation->meta,
                            ]);
                        } elseif ($variation_stock->balance < $variant_quantity) {
                            $this->dispatch('notify',
                                type: 'error',
                                content: $this->errorMessage($variant_quantity, $variation_stock->balance, $cartItem, meta_array_to_string($this->variation->meta)),
                            );

                            // return false;
                            return to_route('shop.product', [
                                'slug'         => $this->product->slug,
                                'variation_id' => $this->variation->id,
                                'variation'    => $this->variation->meta,
                            ]);
                        }

                        if ($cartItem && ! $cartItem->oId) {
                            $variations = $cartItem->selected['variations'];
                            foreach ($variations as &$sv) {
                                if ($sv['id'] == $this->variation->id) {
                                    $exists = true;
                                    $sv['quantity'] += $this->quantity;
                                    $variant_quantity = $sv['quantity'];
                                }
                            }
                            $variants = $this->selected['variants'];
                            $this->selected = ['variations' => $variations, 'variants' => $variants];
                        }

                        if (! $exists) {
                            $this->selected['variations'][] = ['id' => $this->variation->id, 'quantity' => $this->quantity, 'price' => $this->variation->getPrice() ?? $this->product->getPrice()];
                        }

                        foreach ($this->product->variants as $variant) {
                            $this->selected['variants'][$variant['name']] = null;
                        }
                    } else {
                        $this->dispatch('notify',
                            type: 'error',
                            content: __('The variation is not found.')
                        );
                        $this->dispatch('cart-updated-error');

                        return false;
                    }
                } elseif ($this->product->type == 'Standard') {
                    $stock = $this->product->storeStock?->first();
                    $product_quantity = $cartItem ? $cartItem->quantity + $this->quantity : $this->quantity;
                    if (! $stock) {
                        $this->dispatch('notify',
                            type: 'error',
                            content: $this->errorMessage($product_quantity, 0, $cartItem)
                        );

                        return false;
                    } elseif ($stock->balance < $product_quantity) {
                        $this->dispatch('notify',
                            type: 'error',
                            content: $this->errorMessage($product_quantity, $stock->balance, $cartItem)
                        );

                        return false;
                    }
                }

                if ($cartItem && ! $cartItem->oId) {
                    $new_quantity = $cartItem->quantity + $this->quantity;
                    FreeItem::update($cartItem->product_id, $new_quantity, $this->cartId);
                    $cartItem->update(['selected' => $this->selected, 'quantity' => $new_quantity]);
                } else {
                    if ($user = auth()->user()) {
                        $data['user_id'] = $user->id;
                    }
                    $data['oId'] = $this->oId;
                    $data['selected'] = $this->selected;
                    $data['quantity'] = $this->quantity;
                    $cartItem = ShopCartItem::create($data);
                    FreeItem::update($cartItem->product_id, $this->quantity, $this->cartId);
                }

                cache()->forget('cart' . $this->cartId);
                $this->dispatch('cart-updated', cart_items_quantity($this->cartId));
                $this->dispatch('notify',
                    type: 'success',
                    content: __('Product has been added to your cart.'),
                );
            } else {
                $this->dispatch('cart-updated-error');
            }
        }
    }

    public function updatedSelected()
    {
        if ($this->product->has_variants && $this->product->variants) {
            if ($variation = $this->product->variations->where('meta', $this->selected['variants'])->first()) {
                $this->variation = $variation;
                $stock = $variation->storeStock()->first();
                $this->dispatch('variant-stock-changed',
                    variationId: $variation->id,
                    isOutOfStock: ! $stock || $stock->balance <= 0,
                );
            }
        }
    }

    private function errorMessage($ordering, $qty, $exists, $type = '')
    {
        $replacements = ['product' => $this->product->name, 'ordering' => $ordering, 'available' => (float) $qty, 'type' => $type];
        $this->dispatch('cart-updated-error');
        if ($exists) {
            if ($ordering == 1) {
                return $this->showMessage(__(':product (:type) do not have more stock', $replacements));
            }

            return $this->showMessage(__(':product (:type) do not have :ordering quantity in stock but only :available.', $replacements));
        }
        if ($ordering == 1) {
            return $this->showMessage(__(':product (:type) is out of stock.', $replacements));
        }

        return $this->showMessage(__(':product (:type) do not have :ordering quantity in stock but only :available.', $replacements));
    }

    private function showMessage($message)
    {
        return str_replace('() ', '', $message);
    }
}
