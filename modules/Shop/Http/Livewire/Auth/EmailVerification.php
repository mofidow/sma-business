<?php

namespace Modules\Shop\Http\Livewire\Auth;

use Livewire\Component;

class EmailVerification extends Component
{
    public function mount()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        return view('shop::jet.auth.verify-email')->title(__('Verify Email'));
    }
}
