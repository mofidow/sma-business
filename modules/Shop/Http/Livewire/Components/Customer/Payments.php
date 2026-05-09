<?php

namespace Modules\Shop\Http\Livewire\Components\Customer;

use Livewire\Component;
use Livewire\WithPagination;

class Payments extends Component
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
        $sales = auth()->user()->sales()->latest()->paginate()->withQueryString();

        return view('shop::pages.customer.orders', [
            'sales' => $sales,
        ]);
    }

    public function removeSale($id)
    {
        $sale = Sale::findOrFail($id);
        if (! $sale->shop && $sale->customer_id != auth()->user()?->customer_id) {
            $this->dispatch('notify',
                type: 'error',
                content: __('You are not authorized to perform this action.'),
            );
            abort(403);
        }
        $sale->delete();
        $this->dispatch('notify',
            type: 'success',
            content: __(':model has been :action', ['model' => __('Sale'), 'action' => __('deleted')]),
        );
    }
}
