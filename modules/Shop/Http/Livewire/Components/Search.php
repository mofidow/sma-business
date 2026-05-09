<?php

namespace Modules\Shop\Http\Livewire\Components;

use Livewire\Component;
use App\Models\Sma\Product\Product;

class Search extends Component
{
    public $search = '';

    public function updateSearch($search)
    {
        $this->search = trim($search);
        // $this->render();
    }

    public function render()
    {
        return view('shop::components.search', [
            'results' => $this->search ? Product::select(['id', 'name', 'photo', 'slug', 'description'])
                ->whereAny(['name', 'description'], 'like', '%' . $this->search . '%')
                ->take(20)->get() : [],
        ]);
    }
}
