<?php

namespace Modules\Shop\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sma\Product\Brand;

class Brands extends Component
{
    use WithPagination;

    public function render()
    {
        return view('shop::pages.brands', [
            'brands' => Brand::select(['id', 'name', 'slug', 'photo'])
                ->orderBy('order')->paginate(25)->withQueryString(),
        ]);
    }
}
