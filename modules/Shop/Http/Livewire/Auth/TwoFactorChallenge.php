<?php

namespace Modules\Shop\Http\Livewire\Auth;

use Livewire\Component;

class TwoFactorChallenge extends Component
{
    public function render()
    {
        session()->put('url.back', session('url.back') ?? url()->previous() ?? route('shop.home'));
        session()->put('url.intended', session('url.intended') ?? url()->previous() ?? route('shop.home'));

        return view('shop::jet.auth.two-factor-challenge')->title(__('OTP Code'));
    }
}
