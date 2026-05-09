<?php

namespace Modules\Shop\Http\Livewire\Components\Customer;

use Livewire\Component;
use App\Tec\Rules\AddressState;
use Nnjeim\World\Models\Country;
use App\Models\Sma\People\Address;
use App\Tec\Rules\ExtraAttributes;
use App\Models\Sma\People\Customer;

class AddressForm extends Component
{
    public array $form = [];

    public Address $address;

    public $countries;

    public $selected_country;

    public $property;

    public function mount($edit = null, $property = 'open')
    {
        if (auth()->guest()) {
            return redirect()->route('login');
        }

        $this->property = $property;
        $this->address = $edit ? Address::find($edit) : new Address;
        $this->countries = Country::with('states:id,name,country_id')->get();
        $this->selected_country = $edit ? $this->countries->firstWhere('id', $this->address->country_id) : null;

        $this->form = $this->address->toArray();
        $this->form['is_default'] = (bool) ($this->address?->id ? $this->address->default : true);
    }

    public function render()
    {
        return view('shop::pages.customer.address-form');
    }

    public function store()
    {
        $this->validate();

        $user = auth()->user();
        if ($user->addresses()->count() >= 5) {
            $this->dispatch('notify',
                type: 'error',
                content: __('You can only have 5 addresses.')
            );

            $this->dispatch('address-added');

            return false;
        }

        $this->form['default'] = $this->form['is_default'];
        unset($this->form['is_default']);
        if ($this->form['default']) {
            Address::where('default', 1)->ofUser($user->id)->update(['default' => null]);
        }

        $this->form['user_id'] = $user->id;
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
        }
        $this->form['customer_id'] = $user->customer_id;

        if ($this->address->id) {
            unset($this->form['state'], $this->form['country']);
            $this->address->update($this->form);
            session()->flash('success', __('Address has been updated'));

            return to_route('shop.addresses');
        }
        $this->address = Address::create($this->form);

        $this->dispatch('address-added');
        $this->dispatch('notify',
            type: 'success',
            content: __(':model has been :action', ['model' => __('Address'), 'action' => __('saved')])
        );
    }

    public function updated()
    {
        $this->selected_country = ($this->form['country_id'] ?? null) ? $this->countries->firstWhere('id', $this->form['country_id']) : null;
    }

    public function rules()
    {
        return [
            'form.name'    => 'required',
            'form.phone'   => 'required',
            'form.company' => 'nullable',
            'form.email'   => 'nullable|email',
            'form.default' => 'nullable|boolean',

            'form.lot_no'         => 'nullable',
            'form.street'         => 'nullable',
            'form.address_line_1' => 'required',
            'form.address_line_2' => 'nullable',
            'form.city'           => 'required',
            'form.postal_code'    => 'required',
            'form.state_id'       => [new AddressState],
            'form.country_id'     => 'required|exists:countries,id',

            'form.extra_attributes' => ['nullable', new ExtraAttributes('address')],
        ];
    }
}
