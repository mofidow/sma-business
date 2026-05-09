<?php

namespace Modules\Shop\Http\Livewire\ShopMode;

use Livewire\Component;

class ModeMaintenance extends Component
{
    public function mount()
    {
        $settings = get_settings('general');
        if ($settings['shop_mode'] != 'maintenance') {
            redirect()->route('shop.home');
        }
    }

    public function render()
    {
        return view('shop::pages.modes.mode_maintenance')->layout('shop::components.layouts.base');
    }
}
