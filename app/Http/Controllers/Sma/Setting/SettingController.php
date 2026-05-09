<?php

namespace App\Http\Controllers\Sma\Setting;

use Inertia\Inertia;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Tax;
use App\Tec\Rules\AddressState;
use Illuminate\Validation\Rule;
use Nnjeim\World\Models\Country;
use App\Models\Sma\Setting\Store;
use Nnjeim\World\Models\Currency;
use Nnjeim\World\Models\Timezone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Sma\Accounting\Account;
use Illuminate\Support\Facades\Storage;
use Plugins\FiscalServices\FiscalServicePlugin;

class SettingController extends Controller
{
    public function index()
    {
        $fiscalOptions = collect(FiscalServicePlugin::options());
        $fieldDefinitions = collect(FiscalServicePlugin::fields());

        $fiscalServices = $fiscalOptions
            ->reject(fn (array $option) => $option['value'] === 'example')
            ->map(fn (array $option) => [
                'value'       => $option['value'],
                'label'       => $option['label'],
                'description' => $option['description'],
            ])->values();

        $fiscalServiceDrivers = $fiscalServices->pluck('value')->all();

        $fiscalServiceFields = $fieldDefinitions
            ->only($fiscalServiceDrivers)
            ->map(function (array $fields) {
                return collect($fields)->map(fn (array $field) => [
                    'key'           => $field['key'],
                    'label'         => __($field['label']),
                    'type'          => $field['type'],
                    'component'     => $field['component'],
                    'required'      => $field['required'] ?? false,
                    'placeholder'   => $field['placeholder'] ?? null,
                    'help'          => $field['help'] ? __($field['help']) : null,
                    'rows'          => $field['rows'] ?? null,
                    'options'       => $field['options'] ?? [],
                    'default_value' => $field['options'] ?? [],
                ])->values()->all();
            })->all();

        return Inertia::render('Sma/Setting/Index', [
            'taxes'           => Tax::all(),
            'current'         => get_settings(),
            'timezones'       => Timezone::all(),
            'laravel_version' => app()->version(),
            'currencies'      => Currency::all(['id', 'name', 'symbol_native', 'code'])
                ->map(function ($currency) {
                    return [
                        'value' => $currency->id,
                        'label' => $currency->name . ': ' . $currency->code . ' (' . $currency->symbol_native . ')',
                    ];
                }),
            'countries'             => Country::with('states:id,name,country_id')->get(),
            'stores'                => Store::active()->get(['id as value', 'name as label']),
            'accounts'              => Account::active()->get(['id as value', 'title as label', 'reference']),
            'fiscal_services'       => $fiscalServices,
            'fiscal_service_fields' => $fiscalServiceFields,
        ]);
    }

    public function store(Request $request)
    {
        $fiscalOptions = collect(FiscalServicePlugin::options())->reject(fn (array $option) => $option['value'] === 'example');
        $fiscalServiceDrivers = $fiscalOptions->pluck('value')->all();
        $fieldDefinitions = collect(FiscalServicePlugin::fields($fiscalServiceDrivers))->only($fiscalServiceDrivers);

        $fiscalDriverRule = ['nullable', 'string'];

        if ($fiscalServiceDrivers !== []) {
            $fiscalDriverRule[] = Rule::in($fiscalServiceDrivers);
        }

        $rules = [
            'icon'      => 'nullable|image|mimes:jpeg,png,jpg|max:200|dimensions:min_width=48,min_height=48|dimensions:ratio=1/1',
            'icon_dark' => 'nullable|image|mimes:jpeg,png,jpg|max:200|dimensions:min_width=48,min_height=48|dimensions:ratio=1/1',
            'logo'      => 'nullable|image|mimes:jpg,jpeg,png,avif,webp|max:300|dimensions:min_width=180,min_height=60|dimensions:ratio=3/1',
            'logo_dark' => 'nullable|image|mimes:jpg,jpeg,png,avif,webp|max:300|dimensions:min_width=180,min_height=60|dimensions:ratio=3/1',

            'name'            => 'required|string',
            'short_name'      => 'required|string',
            'company'         => 'required|string',
            'reg_no'          => 'nullable|string',
            'email'           => 'required|string',
            'phone'           => 'required|string',
            'country_id'      => 'required|exists:countries,id',
            'state_id'        => [new AddressState],
            'lot_no'          => 'nullable|string',
            'street'          => 'nullable|string',
            'address_line_1'  => 'nullable|string',
            'address_line_2'  => 'nullable|string',
            'city'            => 'nullable|string',
            'postal_code'     => 'nullable|string',
            'currency_id'     => 'nullable|exists:currencies,id',
            'timezone_id'     => 'required|exists:timezones,id',
            'default_account' => 'required|exists:accounts,id',
            'default_store'   => 'required|exists:stores,id',

            'theme'              => 'required|string',
            'hide_id'            => 'required|boolean',
            'rows_per_page'      => 'required|numeric|min:10|max:100',
            'language'           => 'required|string',
            'rtl_support'        => 'nullable|boolean',
            'date_number_locale' => 'nullable|string',
            'fraction'           => 'required|numeric|min:0|max:4',
            'quantity_fraction'  => 'required|numeric|min:0|max:4',
            'max_discount'       => 'required|numeric|min:0|max:100',
            // 'quick_cash'            => 'required|string',
            // 'confirmation'          => 'required|string',
            'dimension_unit'        => 'required|string',
            'weight_unit'           => 'required|string',
            'product_taxes'         => 'nullable|array',
            'inclusive_tax_formula' => 'required|in:inclusive,exclusive',
            'reference'             => 'required|string',
            'inventory_accounting'  => 'required|string|in:FIFO,LIFO,AVCO,EXPF',
            'sale_template'         => 'required|string|in:Minimal,One,Two,Three,Four,Five,Six',
            'search_delay'          => 'nullable|numeric',
            'stock'                 => 'nullable|boolean',
            'overselling'           => 'nullable|boolean',
            'impersonation'         => 'nullable|boolean',
            'hide_out_of_stock'     => 'nullable|boolean',
            // 'play_sound'            => 'nullable|boolean',
            // 'pos_server'            => 'nullable|boolean',
            // 'auto_open_order'       => 'nullable|boolean',
            // 'print_dialog'          => 'nullable|boolean',
            'restaurant'       => 'nullable|boolean',
            'require_country'  => 'nullable|boolean',
            'show_image'       => 'nullable|boolean',
            'show_tax'         => 'nullable|boolean',
            'show_tax_summary' => 'nullable|boolean',
            'show_discount'    => 'nullable|boolean',
            'show_zero_taxes'  => 'nullable|boolean',
            'loyalty'          => 'nullable|array',
            'sale_header'      => 'nullable|string|max:1000',
            'sale_footer'      => 'nullable|string|max:1000',
            'purchase_header'  => 'nullable|string|max:1000',
            'purchase_footer'  => 'nullable|string|max:1000',
            'payment_header'   => 'nullable|string|max:1000',
            'payment_footer'   => 'nullable|string|max:1000',
            'quotation_header' => 'nullable|string|max:1000',
            'quotation_footer' => 'nullable|string|max:1000',
            'date_format'      => 'required|in:js,php',

            'support_links'          => 'nullable|boolean',
            'dark_topbar'            => 'nullable|boolean',
            'dark_sidebar'           => 'nullable|boolean',
            'sidebar_dropdown'       => 'nullable|boolean',
            'sidebar_scroll_to_view' => 'nullable|boolean',
            'captcha_provider'       => 'nullable|string|in:local,recaptcha,trunstile',
            'captcha_site_key'       => 'nullable|string|required_if:captcha_provider,recaptcha|required_if:captcha_provider,trunstile',
            'captcha_secret_key'     => 'nullable|string|required_if:captcha_provider,recaptcha|required_if:captcha_provider,trunstile',
            'fiscal_service_driver'  => $fiscalDriverRule,

            'fiscal_service_settings' => 'nullable|array',

            'telegram_bot_token'     => 'nullable|string',
            'twilio_account_sid'     => 'nullable|string',
            'twilio_auth_token'      => 'nullable|string|required_with:twilio_account_sid',
            'twilio_from'            => 'nullable|string|required_with:twilio_auth_token',
            'twilio_sms_service_sid' => 'nullable|string',

            'fiscal_service_settings.fiscal_service_when_paid'  => 'nullable|boolean',
            'fiscal_service_settings.fiscal_service_end_of_day' => 'nullable|boolean',
        ];

        $selectedFiscalDriver = $request->input('fiscal_service_driver');

        foreach ($fieldDefinitions as $driver => $fields) {
            $rules['fiscal_service_settings.' . $driver] = ['nullable', 'array'];

            foreach ($fields as $field) {
                $key = 'fiscal_service_settings.' . $driver . '.' . $field['key'];

                if ($field['required']) {
                    $rules[$key] = [Rule::requiredIf(fn () => $selectedFiscalDriver === $driver)];

                    continue;
                }

                $rules[$key] = ['nullable', 'string'];
            }
        }

        $form = $request->validate($rules, [
            'icon.dimensions'      => __('The icon must be at least 48x48 pixels with a 1:1 ratio.'),
            'icon_dark.dimensions' => __('The dark icon must be at least 48x48 pixels with a 1:1 ratio.'),
            'logo.dimensions'      => __('The logo must be at least 180x60 pixels with a 3:1 ratio.'),
            'logo_dark.dimensions' => __('The dark logo must be at least 180x60 pixels with a 3:1 ratio.'),
        ]);

        if (demo()) {
            $form['language'] = 'en';
            $form['fiscal_service_driver'] = null;
            unset($form['icon']);
            unset($form['icon_dark']);
            unset($form['logo']);
            unset($form['logo_dark']);
        } else {
            $form['fiscal_service_settings'] = $this->prepareFiscalServiceSettings(
                $form['fiscal_service_settings'] ?? [],
                $fieldDefinitions
            );

            if (empty($form['fiscal_service_driver'] ?? null)) {
                $form['fiscal_service_driver'] = null;
            }

            if ($request->has('icon') && $request->icon) {
                $form['icon'] = Storage::disk('asset')->url($request->icon->store('/images', 'asset'));
                if ($icon = get_settings('icon')) {
                    Storage::disk('asset')->delete($icon);
                }
            } else {
                unset($form['icon']);
            }
            if ($request->has('icon_dark') && $request->icon_dark) {
                $form['icon_dark'] = Storage::disk('asset')->url($request->icon_dark->store('/images', 'asset'));
                if ($icon_dark = get_settings('icon_dark')) {
                    Storage::disk('asset')->delete($icon_dark);
                }
            } else {
                unset($form['icon_dark']);
            }
            if ($request->has('logo') && $request->logo) {
                $form['logo'] = Storage::disk('asset')->url($request->logo->store('/images', 'asset'));
                if ($logo = get_settings('logo')) {
                    Storage::disk('asset')->delete($logo);
                }
            } else {
                unset($form['logo']);
            }
            if ($request->has('logo_dark') && $request->logo_dark) {
                $form['logo_dark'] = Storage::disk('asset')->url($request->logo_dark->store('/images', 'asset'));
                if ($logo_dark = get_settings('logo_dark')) {
                    Storage::disk('asset')->delete($logo_dark);
                }
            } else {
                unset($form['logo_dark']);
            }
        }

        DB::transaction(function () use ($form) {
            $json = json_settings_fields();
            foreach ($form as $key => $value) {
                Setting::updateOrCreate(['tec_key' => $key], ['tec_value' => in_array($key, $json) ? json_encode($value ?? '') : $value]);
            }
        });
        cache()->forget('shop_fraction');
        cache()->forget('default_currency');

        return back()
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Settings'),
                'action' => __('saved'),
            ]));
    }

    private function prepareFiscalServiceSettings(array $settings, Collection $fieldDefinitions): array
    {
        return $fieldDefinitions->mapWithKeys(function ($fields, string $driver) use ($settings) {
            if (! is_iterable($fields)) {
                return [];
            }

            $values = $settings[$driver] ?? [];

            if (! is_array($values)) {
                $values = [];
            }

            $sanitized = collect($fields)
                ->filter(fn ($field) => is_array($field) && isset($field['key']))
                ->mapWithKeys(function (array $field) use ($values) {
                    $key = $field['key'];
                    $value = $values[$key] ?? null;

                    if ($value === null || $value === '') {
                        return [$key => null];
                    }

                    return [$key => is_string($value) ? trim($value) : $value];
                })
                ->filter(fn ($value) => $value !== null)
                ->all();

            return $sanitized === [] ? [] : [$driver => $sanitized];
        })
            ->put('fiscal_service_when_paid', $settings['fiscal_service_when_paid'] ?? null)
            ->put('fiscal_service_end_of_day', $settings['fiscal_service_end_of_day'] ?? null)
            ->all();
    }

    public function supportLinks(Request $request)
    {
        $request->validate([
            'value' => 'required|boolean',
        ]);

        Setting::updateOrCreate(['tec_key' => 'support_links'], ['tec_value' => $request->value ? true : false]);

        return back();
    }
}
