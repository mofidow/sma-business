<?php

namespace Modules\Shop\Http\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Url;

class ResetPassword extends Component
{
    #[Url]
    public $email;

    public $token;

    public function mount($token)
    {
        if (auth()->check()) {
            return redirect()->route('shop.profile');
        }

        $this->token = $token;
    }

    public function render()
    {
        return view('shop::jet.auth.reset-password')->title(__('Reset Password'));
    }
}
