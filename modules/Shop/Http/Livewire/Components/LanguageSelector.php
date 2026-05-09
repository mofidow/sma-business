<?php

namespace Modules\Shop\Http\Livewire\Components;

use Livewire\Component;

class LanguageSelector extends Component
{
    public $current = [];

    public $languages = [];

    public function mount()
    {
        $langFiles = json_decode(file_get_contents(lang_path('languages.json')));

        $this->languages = $langFiles->available ?? [];
        $this->current = collect($this->languages)->firstWhere(fn ($lang) => $lang->value == app()->getLocale()) ?? (object) ['label' => 'English', 'value' => 'en', 'flag' => 'US', 'rtl' => false];

        // Initialize RTL session if not set
        if (! session()->has('shop_rtl')) {
            $isRtl = request()->cookie('shop_rtl') === '1' || ($this->current->rtl ?? false);
            session(['shop_rtl' => $isRtl]);
        }
    }

    public function render()
    {
        return view('shop::components.shared.language-selector');
    }

    public function select($code)
    {
        $language = collect($this->languages)->firstWhere('value', $code);
        session(['shop_language' => $language]);
        session(['shop_rtl' => $language->rtl ?? false]);
        app()->setLocale($language->value);

        cookie()->queue(cookie()->forever('language', $language->value));
        cookie()->queue(cookie()->forever('shop_rtl', $language->rtl ?? false ? '1' : '0'));
        session()->flash('title', __('Use {x}', ['x' => $language->label]));
        session()->flash('success', __(':model has been :action', ['model' => __('Language'), 'action' => __('changed')]));

        return url()->previous() ? $this->redirect(url()->previous()) : $this->redirectRoute('shop.home');
    }
}
