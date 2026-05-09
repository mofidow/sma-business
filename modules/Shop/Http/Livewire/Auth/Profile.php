<?php

namespace Modules\Shop\Http\Livewire\Auth;

use Livewire\Component;

class Profile extends Component
{
    public function render()
    {
        return view('shop::jet.profile.show')->title(__('Profile'));
    }
}
