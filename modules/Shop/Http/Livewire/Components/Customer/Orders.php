<?php

namespace Modules\Shop\Http\Livewire\Components\Customer;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sma\Order\Sale;

class Orders extends Component
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
        return view('shop::pages.customer.orders', [
            'sales' => auth()->user()?->customer?->sales()->latest()->paginate()->withQueryString(),
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
        $sale->forceDelete();
        $this->dispatch('notify',
            type: 'success',
            content: __(':model has been :action', ['model' => __('Order'), 'action' => __('deleted')]),
        );
    }
}
