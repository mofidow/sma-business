<?php

namespace Modules\Shop\Http\Livewire\Admin\Currency;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Shop\Models\ShopCurrency;

class Index extends Component
{
    use WithPagination;

    public function mount()
    {
        if (auth()->guest()) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        $currencies = ShopCurrency::with('currency')->paginate()->withQueryString();

        return view('shop::pages.admin.currency.index', ['currencies' => $currencies]);
    }

    public function removeCurrency($id)
    {
        ShopCurrency::findOrFail($id)->delete();
        $this->dispatch('notify',
            type: 'success',
            content: __(':model has been :action', ['model' => __('Currency'), 'action' => __('deleted')]),
        );
    }
}
