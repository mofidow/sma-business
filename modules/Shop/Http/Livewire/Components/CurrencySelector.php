<?php

namespace Modules\Shop\Http\Livewire\Components;

use Livewire\Component;
use Modules\Shop\Models\ShopCurrency;

class CurrencySelector extends Component
{
    public function render()
    {
        return view('shop::components.shared.currency-selector', [
            'currencies' => cache()->flexible('shop_currencies', [5, 10], fn () => ShopCurrency::with('currency')->get()),
        ]);
    }

    public function select($code)
    {
        $currency = ShopCurrency::with('currency')
            ->whereRelation('currency', 'code', '=', $code)->firstOrFail();
        session(['shop_currency' => $currency]);

        session()->flash('title', __('Use {x}', ['x' => $currency->currency->name]));
        session()->flash('success', __(':model has been :action', ['model' => __('Currency'), 'action' => __('changed')]));

        return url()->previous() ? $this->redirect(url()->previous()) : $this->redirectRoute('shop.home');
    }
}
