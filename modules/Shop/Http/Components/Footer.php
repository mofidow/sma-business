<?php

namespace Modules\Shop\Http\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Footer extends Component
{
    public function render(): View|Closure|string
    {
        return view('shop::components.footer', [
            'shop_footer_settings' => get_settings(['name', 'social_links', 'shop_footer', 'newsletter_input']),
        ]);
    }
}
