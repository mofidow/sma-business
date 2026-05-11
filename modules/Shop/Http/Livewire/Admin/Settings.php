<?php

namespace Modules\Shop\Http\Livewire\Admin;

use App\Models\Setting;
use Livewire\Component;
use Illuminate\Http\File;
use Livewire\WithFileUploads;
use App\Models\Sma\Setting\Store;
use Livewire\Attributes\Validate;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Storage;

class Settings extends Component
{
    use WithFileUploads;

    public array $settings;

    #[Validate('nullable|max:2048|mimes:jpg,jpeg,png,svg,avif,webp')]
    public $currentFile;

    public function mount()
    {
        $this->settings = (array) get_settings([
            'general', 'seo', 'shop_slider', 'shop_cta', 'page_menus', 'notification',
            'shop_footer',  'social_links', 'newsletter_input', 'brands_article', 'shipping_policy', 'return_policy', 'captcha',
        ]);
    }

    public function render()
    {
        return view('shop::pages.admin.settings', [
            'stores' => Store::active()->get(['id', 'name']),
        ])->title(__('Shop Settings'));
    }

    public function save()
    {
        $errors = [];
        try {
            $this->withValidator(function (Validator $validator) use (&$errors) {
                $validator->after(function ($validator) use (&$errors) {
                    $errors = $validator->errors()->toArray();
                });
            })->validate();
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $errors], 422);
        }

        foreach ($this->settings as $key => $value) {
            if ($key == 'shop_slider') {
                foreach ($value as $index => $slide) {
                    if (is_string($slide['image']) && str_starts_with($slide['image'], '/asset/temp/')) {
                        $path = Storage::disk('asset')->putFile('slides', new File(public_path($slide['image'])));
                        $this->settings['shop_slider'][$index]['image'] = '/asset/' . $path;
                    }
                    if (isset($slide['bg_image']) && is_string($slide['bg_image']) && str_starts_with($slide['bg_image'], '/asset/temp/')) {
                        $path = Storage::disk('asset')->putFile('slides', new File(public_path($slide['bg_image'])));
                        $this->settings['shop_slider'][$index]['bg_image'] = '/asset/' . $path;
                    }
                }
            } elseif ($key == 'shop_cta') {
                if (isset($value['bg_image']) && is_string($value['bg_image']) && str_starts_with($value['bg_image'], '/asset/temp/')) {
                    $path = Storage::disk('asset')->putFile('slides', new File(public_path($value['bg_image'])));
                    $this->settings['shop_cta']['bg_image'] = '/asset/' . $path;
                }
            }
        }
        Storage::disk('asset')->deleteDirectory('temp');

        $json = json_settings_fields();
        foreach ($this->settings as $key => $value) {
            Setting::updateOrCreate(['tec_key' => $key], ['tec_value' => in_array($key, $json) ? json_encode($value ?? '') : $value]);
        }

        cache()->forget('shop_seo');
        cache()->forget('shop_settings');

        $this->dispatch('notify',
            type: 'success',
            content: __(':model has been :action', ['model' => __('Settings'), 'action' => __('updated')]),
        );
    }

    public function uploadFile()
    {
        $path = $this->currentFile->store('temp', 'asset');
        $this->dispatch('uploaded', ['path' => $path, 'url' => '/asset/' . $path]);
    }

    protected function rules()
    {
        return [
            'settings.general'          => 'required|array',
            'settings.general.name'     => 'required|string|max:60',
            'settings.general.phone'    => 'required',
            'settings.general.email'    => 'required|email',
            'settings.general.store_id' => 'required|exists:stores,id',
            'settings.general.logo'     => ['nullable', function ($attribute, $value, $fail) {
                if (! is_string($value) && ! ($value instanceof UploadedFile)) {
                    $fail('The ' . $attribute . ' must either be a url or file.');
                }
            }],
            'settings.general.logo_dark' => ['nullable', function ($attribute, $value, $fail) {
                if (! is_string($value) && ! ($value instanceof UploadedFile)) {
                    $fail('The ' . $attribute . ' must either be a url or file.');
                }
            }],
            'settings.general.products_per_page'              => 'nullable',
            'settings.general.shop_mode'                      => 'nullable',
            'settings.general.hide_price'                     => 'nullable',
            'settings.general.disable_cart'                   => 'nullable',
            'settings.general.guest_checkout'                 => 'nullable',
            'settings.general.max_unpaid_orders'              => 'nullable',
            'settings.general.user_registration'              => 'nullable',
            'settings.general.new_account_email_confirmation' => 'nullable',
            'settings.general.captcha'                        => 'nullable',
            'settings.general.recaptcha_key'                  => 'nullable',
            'settings.general.recaptcha_secret'               => 'nullable',
            'settings.general.turnstile_key'                  => 'nullable',
            'settings.general.turnstile_secret'               => 'nullable',
            'settings.seo'                                    => 'required|array',
            'settings.seo.title'                              => 'required|string|max:60',
            'settings.seo.products_title'                     => 'required|string|max:60',
            'settings.seo.description'                        => 'required|string|max:160',
            'settings.seo.products_description'               => 'required|string|max:160',
            'settings.notification'                           => 'nullable|array',
            'settings.notification.message'                   => 'nullable|string|max:160',
            'settings.notification.button_text'               => 'nullable|string|max:25',
            'settings.notification.button_link'               => 'nullable|url',
            'settings.shop_slider'                            => 'required|array|max:6',
            'settings.shop_slider.*.image'                    => ['required', function ($attribute, $value, $fail) {
                if (! is_string($value) && ! ($value instanceof UploadedFile)) {
                    $fail('The ' . $attribute . ' must either be a url or file.');
                }
            }],
            'settings.shop_slider.*.bg_image' => ['nullable', function ($attribute, $value, $fail) {
                if (! is_string($value) && ! ($value instanceof UploadedFile)) {
                    $fail('The ' . $attribute . ' must either be a url or file.');
                }
            }],
            'settings.shop_slider.*.heading'     => 'required|string',
            'settings.shop_slider.*.description' => 'required|string',
            'settings.shop_slider.*.button_text' => 'required|string',
            'settings.shop_slider.*.button_link' => 'required|url',
            'settings.shop_cta'                  => 'required|array',
            'settings.shop_cta.bg_image'         => ['nullable', function ($attribute, $value, $fail) {
                if (! is_string($value) && ! ($value instanceof UploadedFile)) {
                    $fail('The ' . $attribute . ' must either be a url or file.');
                }
            }],
            'settings.shop_cta.heading'                     => 'required|string|max:60',
            'settings.shop_cta.description'                 => 'required|string|max:190',
            'settings.shop_cta.button_text'                 => 'required|string|max:30',
            'settings.shop_cta.button_link'                 => 'required|url',
            'settings.shop_footer'                          => 'required|array',
            'settings.shop_footer.sections'                 => 'required|array',
            'settings.shop_footer.sections'                 => 'required|array',
            'settings.shop_footer.sections.*.title'         => 'required|string|max:35',
            'settings.shop_footer.sections.*.menus'         => 'required|array',
            'settings.shop_footer.sections.*.menus.*.label' => 'required|string|max:30',
            'settings.shop_footer.sections.*.menus.*.link'  => 'required|url',
            'settings.page_menus'                           => 'required|array|max:10',
            'settings.page_menus.*.label'                   => 'required|string|max:30',
            'settings.page_menus.*.link'                    => 'required|url',
            'settings.social_links'                         => 'required|array',
            'settings.newsletter_input'                     => 'required|boolean',
            'settings.brands_article'                       => 'required|boolean',
            'settings.shipping_policy'                      => 'nullable|string',
            'settings.return_policy'                        => 'nullable|string',
        ];
    }
}
