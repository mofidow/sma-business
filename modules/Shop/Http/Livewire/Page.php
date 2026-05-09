<?php

namespace Modules\Shop\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use Modules\Shop\Models\ShopPage;

class Page extends Component
{
    public $contents;

    public $options;

    public ShopPage $page;

    public function changePage($id)
    {
        if ($id != $this->page->id) {
            $this->page = ShopPage::find($id);
            $this->contents = (string) str($this->page->body)->markdown();

            if (Str::contains($this->contents, ['<!-- [map:', '<!-- [contact-form] -->'])) {
                $address = Str::before(Str::after($this->contents, '<!-- [map:'), '] -->');
                $contact = view()->file(base_path('modules/Shop/Resources/views/components/sections/contact-form.blade.php'))->render();
                $map = view()->file(base_path('modules/Shop/Resources/views/components/sections/map.blade.php'), ['address' => $address])->render();

                $this->contents = str_replace('<!-- [contact-form] -->', $contact, $this->contents);
                $this->contents = str_replace('<!-- [map:' . $address . '] -->', $map, $this->contents);
            }

            $this->dispatch('page-changed', [
                'title'       => __($this->page->title),
                'state'       => ['page' => $this->page->id],
                'description' => __($this->page->description),
                'url'         => route('shop.page', ['page' => $this->page->slug]),
            ]);
            $this->dispatch('page-updated');
        }
    }

    // public function getName()
    // {
    //     return 'shop::pages.page';
    // }

    public function mount(ShopPage $page)
    {
        $this->page = $page;
        $this->options = []; // ['html_input' => 'strip', 'allow_unsafe_links' => false];
        $this->contents = (string) str($this->page->body)->markdown();
    }

    public function render()
    {
        return view('shop::pages.page', [
            'pages' => ShopPage::select(['id', 'title', 'slug'])->get(),
        ]);
    }
}
