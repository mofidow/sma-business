<?php

namespace Modules\Shop\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Shop\Models\Product;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::selectColumns()
            ->with([
                'brand:id,name,slug',
                'category:id,name,slug',
                'storeStock',
                'variations.storeStock',
                'validPromotions',
            ])
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderByDesc('created_at')
            ->paginate(24);

        return view('shop::pages.index', compact('products'));
    }
}
