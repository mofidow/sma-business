<?php

namespace Modules\Shop\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sma\Product\Category;

class Categories extends Component
{
    use WithPagination;

    public function render()
    {
        return view('shop::pages.categories', [
            'categories' => Category::select(['id', 'name', 'slug', 'photo'])
                ->orderBy('order')->paginate(25)->withQueryString(),
        ]);
    }
}
