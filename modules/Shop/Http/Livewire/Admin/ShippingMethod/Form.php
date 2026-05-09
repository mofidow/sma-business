<?php

namespace Modules\Shop\Http\Livewire\Admin\ShippingMethod;

use Livewire\Component;
use App\Tec\Rules\AddressState;
use Modules\Shop\Models\ShopShippingMethod;

class Form extends Component
{
    public array $form;

    public $states = [];

    public $countries = [];

    public ShopShippingMethod $shipping_method;

    public function mount(?ShopShippingMethod $shipping_method)
    {
        if (auth()->guest()) {
            return redirect()->route('login');
        }

        $this->shipping_method = $shipping_method->id ? $shipping_method : new ShopShippingMethod;
        $this->shipping_method->active = ! $this->shipping_method->id ? true : (bool) $this->shipping_method->active;
        $this->shipping_method->charge_for_weight = ! $this->shipping_method->id ? false : (bool) $this->shipping_method->charge_for_weight;
        $this->form = $this->shipping_method->toArray();

        $this->countries = get_countries();
        if ($this->shipping_method->country_id) {
            $this->states = get_states($this->shipping_method->country_id);
        }
    }

    public function render()
    {
        return view('shop::pages.admin.shipping_method.form');
    }

    public function save()
    {
        $this->validate();

        if ($this->shipping_method->id) {
            $this->shipping_method->update($this->form);
        } else {
            $this->shipping_method = ShopShippingMethod::create($this->form);
        }

        cache()->forget('shop_shipping_methods');
        session()->flash('success', __(':model has been :action', ['model' => __('Shipping Method'), 'action' => $this->shipping_method->id ? __('updated') : __('created')]));
        $this->redirectRoute('shop.shipping_methods');
    }

    public function updated()
    {
        if ($this->form['country_id']) {
            $this->states = get_states($this->form['country_id']);
        }
    }

    protected function rules()
    {
        return [
            'form.provider_name'     => 'required|string',
            'form.rate'              => 'required|numeric',
            'form.state_id'          => [new AddressState],
            'form.country_id'        => 'required|exists:countries,id',
            'form.charge_for_weight' => 'nullable|boolean',
            'form.active'            => 'nullable|boolean',
            'form.free_order_amount' => 'nullable|numeric',
        ];
    }
}
