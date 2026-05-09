<?php

namespace Modules\Shop\Http\Livewire\Auth;

use Livewire\Component;

class ForgotPassword extends Component
{
    public function mount()
    {
        if (auth()->check()) {
            return redirect()->route('shop.profile');
        }
    }

    public function render()
    {
        return view('shop::jet.auth.forgot-password')->title(__('Forgot Password'));
    }
}
