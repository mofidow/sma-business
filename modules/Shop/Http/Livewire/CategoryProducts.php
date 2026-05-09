<?php

namespace Modules\Shop\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Shop\Models\Product;
use App\Models\Sma\Product\Category;

class CategoryProducts extends Component
{
    use WithPagination;

    public Category $category;

    public ?Category $subcategory;

    public function mount($slug, $sub_slug = null)
    {
        $this->category = Category::where('slug', $slug)->firstOrFail();
        $this->subcategory = $sub_slug ? Category::where('slug', $sub_slug)->firstOrFail() : null;

        $categories = [$this->category->id];
        if ($this->subcategory) {
            $categories[] = $this->category->id;
        }

        return redirect()->route('shop.products', ['filters' => ['categories' => $categories]]);
    }

    public function render()
    {
        if (! ($this->subcategory ?? false)) {
            $this->subcategory = null;
        }
        $settings = get_settings('general');

        return view('shop::pages.products', [
            'category'    => $this->category,
            'subcategory' => $this->subcategory,
            'products'    => Product::select([
                'id', 'code', 'name', 'photo', 'price', 'slug',
                'description', 'brand_id', 'category_id', 'subcategory_id',
            ])->with([
                'brand:id,name,slug', 'category:id,name,slug', 'subcategory:id,name,slug',
            ])->where('category_id', $this->category->id)
                ->when($this->subcategory, fn ($query) => $query->where('subcategory_id', $this->subcategory->id))
                ->paginate($settings['products_per_page'] ?? 24)->withQueryString(),
        ]);
    }
}
