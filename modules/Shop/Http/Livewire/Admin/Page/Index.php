<?php

namespace Modules\Shop\Http\Livewire\Admin\Page;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Shop\Models\ShopPage;

class Index extends Component
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
        $pages = ShopPage::paginate()->withQueryString();

        return view('shop::pages.admin.page.index', ['pages' => $pages]);
    }

    public function removePage($id)
    {
        ShopPage::findOrFail($id)->delete();
        $this->dispatch('notify',
            type: 'success',
            content: __(':model has been :action', ['model' => __('Page'), 'action' => __('deleted')]),
        );
    }
}
