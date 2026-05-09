<?php

namespace Modules\Shop\Http\Livewire\Components\Customer;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sma\People\Address;

class Addresses extends Component
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
        $addresses = auth()->user()->addresses()->paginate()->withQueryString();

        return view('shop::pages.customer.addresses', ['addresses' => $addresses]);
    }

    public function removeAddress($id)
    {
        $address = Address::findOrFail($id);
        if (! $address->customer_id || $address->customer_id != auth()->user()?->customer_id) {
            $this->dispatch('notify',
                type: 'error',
                content: __('You are not authorized to perform this action.'),
            );
            abort(403);
        }
        $address->delete();
        $this->dispatch('notify',
            type: 'success',
            content: __(':model has been :action', ['model' => __('Address'), 'action' => __('deleted')]),
        );
    }
}
