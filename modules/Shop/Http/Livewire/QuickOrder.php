<?php

namespace Modules\Shop\Http\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Modules\Shop\Models\Product;
use App\Models\Sma\People\Customer;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Setting\Store;
use Illuminate\Support\Facades\DB;

class QuickOrder extends Component
{
    public Product $product;

    public int $quantity = 1;

    #[Validate('required|string|max:100', message: 'Please enter your full name.')]
    public string $name = '';

    #[Validate('required|string|max:30', message: 'Please enter your phone number.')]
    public string $phone = '';

    #[Validate('required|string', message: 'Please select your region.')]
    public string $region = 'Banaadir';

    public string $notes = '';

    public bool $submitted = false;
    public string $orderRef = '';

    public static array $somaliRegions = [
        'Awdal',
        'Woqooyi Galbeed',
        'Togdheer',
        'Sanaag',
        'Sool',
        'Bari',
        'Nugaal',
        'Mudug',
        'Galgaduud',
        'Hiiraan',
        'Shabeellaha Dhexe',
        'Banaadir',
        'Shabeellaha Hoose',
        'Bay',
        'Bakool',
        'Gedo',
        'Jubada Dhexe',
        'Jubada Hoose',
    ];

    public function mount(string $slug): void
    {
        $this->product = Product::where('slug', $slug)
            ->with(['category:id,name', 'brand:id,name', 'validPromotions'])
            ->firstOrFail();
    }

    public function increment(): void
    {
        $this->quantity = min(99, $this->quantity + 1);
    }

    public function decrement(): void
    {
        $this->quantity = max(1, $this->quantity - 1);
    }

    public function submit(): void
    {
        $this->validate();

        DB::transaction(function () {
            // Resolve store
            $general  = get_settings('general');
            $storeId  = $general['store_id'] ?? Store::first()?->id ?? 1;

            // Ensure session has store so Sale observer picks it up
            session(['selected_store_id' => $storeId]);

            // Find or create customer by phone
            $customer = Customer::firstOrCreate(
                ['phone' => trim($this->phone)],
                ['name'  => trim($this->name), 'phone' => trim($this->phone)]
            );

            // If name changed (returning customer), update it
            if ($customer->name !== trim($this->name) && trim($this->name)) {
                $customer->update(['name' => trim($this->name)]);
            }

            $unitPrice = (float) $this->product->price;
            $qty       = $this->quantity;
            $total     = round($unitPrice * $qty, 4);

            $notesText = 'Region: ' . $this->region;
            if (trim($this->notes)) {
                $notesText .= ' | ' . trim($this->notes);
            }

            // Create sale
            $sale = Sale::create([
                'store_id'    => $storeId,
                'customer_id' => $customer->id,
                'date'        => now()->toDateString(),
                'sub_total'   => $total,
                'grand_total' => $total,
                'shop'        => true,
                'notes'       => $notesText,
            ]);

            // Create sale item (sale_items uses 'subtotal', not 'sub_total')
            $sale->items()->create([
                'product_id' => $this->product->id,
                'store_id'   => $storeId,
                'quantity'   => $qty,
                'price'      => $unitPrice,
                'cost'       => (float) ($this->product->cost ?? 0),
                'subtotal'   => $total,
                'total'      => $total,
            ]);

            $this->orderRef = $sale->reference;
        });

        $this->submitted = true;
        $this->dispatch('order-placed');
    }

    public function render()
    {
        return view('shop::pages.quick-order');
    }
}
