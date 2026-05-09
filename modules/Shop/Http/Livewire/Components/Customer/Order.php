<?php

namespace Modules\Shop\Http\Livewire\Components\Customer;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sma\Order\Sale;

class Order extends Component
{
    use WithPagination;

    public Sale $sale;

    public function mount($id, $hash = null)
    {
        $this->sale = Sale::withoutGlobalScopes()->with([
            'store', 'customer', 'user:id,name',
            'payments:id,date,amount,method,reference',
            'directPendingPayments:id,sale_id,date,amount,reference',
            'items.variations', 'items.product', 'items.unit:id,code,name',
        ])->find($id);

        // dd($this->sale->toArray());
        if (auth()->user() && $this->sale->customer_id != auth()->user()->customer_id) {
            abort(404);
        } elseif (auth()->guest() && (! $this->sale || $this->sale->hash != $hash)) {
            abort(404);
        }
    }

    public function render()
    {
        return view('shop::pages.customer.order', [
            'settings' => get_public_settings(),
        ]);
    }
}
