<?php

namespace Modules\Shop\Http\Livewire\Admin\Currency;

use Livewire\Component;
use Nnjeim\World\Models\Currency;
use Modules\Shop\Models\ShopCurrency;

class Form extends Component
{
    public array $form;

    public array $currencies;

    public ShopCurrency $currency;

    public function mount(?ShopCurrency $currency)
    {
        if (auth()->guest()) {
            return redirect()->route('login');
        }
        $this->currencies = Currency::all(['id', 'name', 'symbol_native', 'code'])
            ->map(fn ($currency) => [
                'id'    => $currency->id,
                'label' => $currency->name . ': ' . $currency->code . ' (' . $currency->symbol_native . ')',
            ])->toArray();

        $this->currency = $currency->id ? $currency : new ShopCurrency;
        $this->currency->auto_update = ! $this->currency->id ? true : (bool) $this->currency->auto_update;
        $this->currency->show_at_end = ! $this->currency->id ? false : (bool) $this->currency->show_at_end;

        $this->form = $this->currency->toArray();
    }

    public function render()
    {
        return view('shop::pages.admin.currency.form', [
            'default_currency' => default_currency(),
        ]);
    }

    public function save()
    {
        $this->validate();

        if ($this->currency->id) {
            $this->currency->update($this->form);
        } else {
            $this->currency = ShopCurrency::create($this->form);
        }

        cache()->forget('shop_currencies');
        session()->flash('success', __(':model has been :action', ['model' => __('Currency'), 'action' => $this->currency->id ? __('updated') : __('created')]));
        $this->redirectRoute('shop.currencies');
    }

    protected function rules()
    {
        return [
            'form.currency_id'   => 'required|exists:currencies,id',
            'form.exchange_rate' => 'required|numeric',
            'form.auto_update'   => 'nullable|boolean',
            'form.show_at_end'   => 'nullable|boolean',
        ];
    }
}
