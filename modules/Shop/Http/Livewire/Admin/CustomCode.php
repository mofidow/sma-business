<?php

namespace Modules\Shop\Http\Livewire\Admin;

use App\Models\Setting;
use Livewire\Component;
use Livewire\Attributes\Validate;

class CustomCode extends Component
{
    #[Validate('required')]
    public array $settings;

    public function mount()
    {
        $this->settings = get_settings(['shop_header_code', 'shop_footer_code']);
    }

    public function render()
    {
        return view('shop::pages.admin.custom_code')->title(__('Shop Custom Code'));
    }

    public function save()
    {
        if (demo()) {
            return back()->with('error', __('Changes are not allowed in demo mode.'));
        }

        Setting::updateOrCreate(['tec_key' => 'shop_header_code'], ['tec_value' => $this->settings['shop_header_code'] ?? '']);
        Setting::updateOrCreate(['tec_key' => 'shop_footer_code'], ['tec_value' => $this->settings['shop_footer_code'] ?? '']);

        $this->dispatch('notify',
            type: 'success',
            content: __(':model has been :action', ['model' => __('Custom Code'), 'action' => __('updated')]),
        );
    }
}
