<?php

namespace Modules\Shop\Http\Livewire\Components\Customer;

use Livewire\Component;
use App\Tec\Rules\AddressState;
use Nnjeim\World\Models\Country;
use App\Tec\Rules\ExtraAttributes;
use App\Models\Sma\People\Customer;

class Billing extends Component
{
    public array $form;

    public $countries;

    public $selected_country;

    public function mount()
    {
        $user = auth()->user();
        if (! $user) {
            return to_route('login');
        }

        if (! $user->customer_id) {
            $customer = Customer::create([
                'user_id' => $user->id,
                'name'    => $user->name,
                'company' => $user->name,
                'email'   => $user->email,
                'phone'   => $user->phone,
            ]);
            $user->customer_id = $customer->id;
            $user->save();
            $user->setRelation('customer', $customer);
        }

        $this->form = $user->customer->toArray();
        $this->countries = Country::with('states:id,name,country_id')->get();
        $this->selected_country = $user->customer->country_id ? $this->countries->firstWhere('id', $user->customer->country_id) : null;
    }

    public function render()
    {
        return view('shop::pages.customer.billing');
    }

    public function save()
    {
        $this->validate();

        unset(
            $this->form['points'],
            $this->form['state'],
            $this->form['country'],
            $this->form['balance'],
            $this->form['due_limit'],
            $this->form['price_group'],
            $this->form['customer_group'],
            $this->form['opening_balance'],
        );
        auth()->user()->customer->update($this->form);

        $this->dispatch('notify',
            type: 'success',
            content: __(':model has been :action', ['model' => __('Billing'), 'action' => __('updated')]),
        );
    }

    public function updated()
    {
        $this->selected_country = $this->form['country_id'] ? $this->countries->firstWhere('id', $this->form['country_id']) : null;
    }

    protected function rules()
    {
        $require_state = get_settings('require_state');

        return [
            'form.name'    => 'required',
            'form.phone'   => 'nullable',
            'form.company' => 'nullable|unique:customers,name,' . auth()->user()->customer?->id,
            'form.email'   => 'nullable|email',

            'form.lot_no'         => 'nullable',
            'form.street'         => 'nullable',
            'form.address_line_1' => 'nullable',
            'form.address_line_2' => 'nullable',
            'form.city'           => 'nullable',
            'form.postal_code'    => 'nullable',
            'form.state_id'       => $require_state == 1 ? [new AddressState] : 'nullable|exists:states,id',
            'form.country_id'     => $require_state == 1 ? 'required|exists:countries,id' : 'nullable|exists:countries,id',

            'form.telegram_user_id' => 'nullable|string',
            'form.extra_attributes' => ['nullable', new ExtraAttributes('customer')],
        ];
    }
}
