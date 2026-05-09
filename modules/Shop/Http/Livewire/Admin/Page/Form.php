<?php

namespace Modules\Shop\Http\Livewire\Admin\Page;

use Livewire\Component;
use Modules\Shop\Models\ShopPage;

class Form extends Component
{
    public array $form;

    public ShopPage $page;

    public function mount(?ShopPage $page)
    {
        $this->page = $page->id ? $page : new ShopPage;
        $this->page->active = ! $this->page->id ? true : (bool) $this->page->active;
        $this->form = $this->page->toArray();
    }

    public function render()
    {
        return view('shop::pages.admin.page.form');
    }

    public function save()
    {
        $this->validate();

        if ($this->page->id) {
            $this->page->update($this->form);
        } else {
            $this->page = ShopPage::create($this->form);
        }

        session()->flash('success', __(':model has been :action', ['model' => __('Page'), 'action' => $this->page->id ? __('updated') : __('created')]));
        $this->redirectRoute('shop.pages');
    }

    protected function rules()
    {
        return [
            'form.title'       => 'required|min:5|max:60',
            'form.slug'        => 'required|alpha_dash|unique:shop_pages,slug,' . $this->page->id,
            'form.description' => 'required|string|max:160',
            'form.body'        => 'required',
            'form.order'       => 'nullable|integer',
            'form.active'      => 'nullable|boolean',
        ];
    }
}
