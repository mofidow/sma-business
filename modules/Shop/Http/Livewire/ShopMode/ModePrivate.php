<?php

namespace Modules\Shop\Http\Livewire\ShopMode;

use Livewire\Component;

class ModePrivate extends Component
{
    public function mount()
    {
        $settings = get_settings('general');
        if ($settings['shop_mode'] != 'private') {
            redirect()->route('shop.home');
        }
    }

    public function render()
    {
        return view('shop::pages.modes.mode_private')->layout('shop::components.layouts.base');
    }
}
