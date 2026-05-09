<?php

// Get Settings

use App\Models\Setting;
use App\Models\Sma\Setting\Store;
use App\Models\Sma\Setting\CustomField;

if (! function_exists('get_settings')) {
    function get_settings($keys = null)
    {
        $json = json_settings_fields();
        if (! empty($keys)) {
            $single = ! is_array($keys) || count($keys) == 1;

            if ($single) {
                $key = is_array($keys) ? $keys[0] : $keys;
                $value = optional(Setting::where('tec_key', $key)->first())->tec_value;

                return in_array($key, $json) ? json_decode($value, true) : $value;
            }

            return Setting::whereIn('tec_key', $keys)->get()->mapWithKeys(function ($row) use ($json) {
                return [$row['tec_key'] => in_array($row['tec_key'], $json) ? json_decode($row['tec_value'], true) : $row['tec_value']];
            })->all();
        }

        return Setting::all()->pluck('tec_value', 'tec_key')
            ->merge(['baseUrl' => url('/')])->transform(function ($value, $key) use ($json) {
                return in_array($key, $json) ? json_decode($value, true) : $value;
            })->all();
    }
}

// Get public settings
if (! function_exists('get_public_settings')) {
    function get_public_settings()
    {
        return settings_remove_private_fields(get_settings());
    }
}

// Settings fields those need to cast as json
if (! function_exists('json_settings_fields')) {
    function json_settings_fields()
    {
        return [
            'scale_barcode', 'mail', 'payment', 'loyalty', 'product_taxes',
            'fiscal_service_settings',  'fiscal_service_token', 'quick_cash',
            'general', 'seo', 'shop_slider', 'shop_cta', 'shop_footer', 'page_menus', 'social_links', 'notification',
        ];
    }
}

// Settings fields those need to cast as json
if (! function_exists('settings_remove_private_fields')) {
    function settings_remove_private_fields($settings)
    {
        $safe = collect($settings)->forget([
            'mail', 'payment', 'captcha_secret_key',
            'telegram_bot_token', 'twilio_account_sid',
            'twilio_auth_token', 'twilio_from', 'twilio_sms_service_sid',
        ])->all();

        // Set payment public fields
        $safe['payment']['gateway'] = $settings['payment']['gateway'] ?? null;
        $safe['payment']['default_currency'] = $settings['payment']['default_currency'] ?? 'USD';
        $safe['payment']['stripe_terminal'] = $settings['payment']['stripe_terminal'] ?? false;
        $safe['payment']['static_payment_methods'] = $settings['payment']['static_payment_methods'] ?? [];
        $safe['payment']['services']['paypal']['enabled'] = $settings['payment']['services']['paypal']['enabled'] ?? false;
        $safe['payment']['services']['paypal']['client_id'] = $settings['payment']['services']['paypal']['client_id'] ?? null;
        $safe['payment']['services']['stripe']['key'] = $settings['payment']['services']['stripe']['key'] ?? null;

        return $safe;
    }
}

// default currency
if (! function_exists('default_currency')) {
    function default_currency()
    {
        return cache()->flexible('default_currency', [15, 30], function () {
            $code = get_settings('payment')['default_currency'] ?? null;

            return $code ? Nnjeim\World\Models\Currency::where('code', $code)->first() : Nnjeim\World\Models\Currency::where('code', 'USD')->first();
        });
    }
}

// disable feature
if (! function_exists('disable_feature')) {
    function disable_feature($name)
    {
        Laravel\Pennant\Feature::flushCache();

        return Laravel\Pennant\Feature::deactivateForEveryone($name);
    }
}

// enable feature
if (! function_exists('enable_feature')) {
    function enable_feature($name, $all = false)
    {
        Laravel\Pennant\Feature::flushCache();

        if ($all) {
            return Laravel\Pennant\Feature::activateForEveryone($name);
        }

        return Laravel\Pennant\Feature::activate($name);
    }
}

// Check if feature is enabled
if (! function_exists('feature_enabled')) {
    function feature_enabled($name)
    {
        try {
            return Laravel\Pennant\Feature::active($name);
        } catch (Throwable $th) {
            return false;
        }
    }
}

// Log Activity
if (! function_exists('log_activity')) {
    function log_activity($activity, $properties = null, $model = null, $name = null)
    {
        return activity($name)->performedOn($model)->withProperties($properties)->log($activity);
    }
}

// Format Decimal
if (! function_exists('format_decimal')) {
    function format_decimal($number, $decimals = null, $ds = '.', $ts = '')
    {
        if (! is_numeric($decimals)) {
            $decimals = (int) get_settings('fraction') ?? 2;
        }

        return number_format($number, $decimals, $ds, $ts);
    }
}

// Format Decimal for Quantity
if (! function_exists('format_decimal_qty')) {
    function format_decimal_qty($number, $decimals = null, $ds = '.', $ts = '')
    {
        if (! is_numeric($decimals)) {
            $decimals = (int) get_settings('quantity_fraction') ?? 2;
        }

        return number_format($number, $decimals, $ds, $ts);
    }
}

// Format Number
if (! function_exists('format_number')) {
    function format_number($number, $decimals = null, $ds = '.', $ts = ',')
    {
        if (! is_numeric($number)) {
            $decimals = get_settings('fraction') ?? 2;
        }

        return number_format($number, $decimals, $ds, $ts);
    }
}

// Get countries
if (! function_exists('get_countries')) {
    function get_countries()
    {
        return Nnjeim\World\Models\Country::with('states:id,name,country_id')->get()->toArray();
    }
}

// Get states
if (! function_exists('get_states')) {
    function get_states($country_id)
    {
        $country = Nnjeim\World\Models\Country::with('states:id,name,country_id')->find($country_id);

        return $country ? $country->states->toArray() : [];
    }
}

// Is Demo Enabled
if (! function_exists('demo')) {
    function demo()
    {
        return env('DEMO', false);
    }
}

// Json translation with choice replace
if (! function_exists('__choice')) {
    function __choice($key, array $replace = [], $number = null)
    {
        return trans_choice($key, $number, $replace);
    }
}

// Get UUID v4
if (! function_exists('uuid4')) {
    function uuid4()
    {
        return Ramsey\Uuid\Uuid::uuid4();
    }
}

// Get ULID
if (! function_exists('ulid')) {
    function ulid()
    {
        return (string) Ulid\Ulid::generate(true);
    }
}

// Get get next id
if (! function_exists('get_next_id')) {
    function get_next_id($model)
    {
        return collect(Illuminate\Support\Facades\DB::select("show table status like '{$model->getTable()}'"))->first()->Auto_increment;
    }
}

// Get reference
if (! function_exists('get_reference')) {
    function get_reference($model)
    {
        $format = get_settings('reference');

        return match ($format) {
            'ai'     => get_next_id($model),
            'ulid'   => ulid(),
            'uuid'   => uuid4(),
            'uniqid' => uniqid(),
            default  => get_next_reference($model, $format),
        };
    }
}

// Get reference
if (! function_exists('get_next_reference')) {
    function get_next_reference($model, $format)
    {
        $key = get_class($model);
        $key = str($key)->replace('App\\Models\\', '')->snake()->append('_reference')->toString();
        $prefix_key = str($key)->replace('_reference', '_prefix')->toString();

        $prefix = get_settings($prefix_key) ?? '';
        $reference_per_store = get_settings('reference_per_store') ?? '';

        if ($reference_per_store && $model->store_id) {
            $store = Store::find($model->store_id);
            $store->references->{$key} = (int) ($store->references->{$key} ?? 0) + 1;
            $store->update(['references' => $store->references]);

            return $prefix . (($format ? now()->format($format) : '') . $store->references->{$key});
        }

        $next_reference = get_settings($key);
        $reference = (int) ($next_reference ?? 0) + 1;
        Setting::updateOrCreate(
            ['tec_key' => $key], ['tec_value' => $reference]
        );

        return $prefix . (($format ? now()->format($format) : '') . $reference);
    }
}

// Order Custom Fields
if (! function_exists('viewable_custom_fields')) {
    function viewable_custom_fields($data, $ofModel)
    {
        $extra_attributes = [];
        $fields = CustomField::where('show_on_details_view', 1)->ofModel($ofModel)->pluck('name');
        if ($fields) {
            foreach ($fields as $field) {
                $extra_attributes[$field] = $data['extra_attributes'][$field] ?? '';
            }
        }

        return $extra_attributes;
    }
}

// Calculate Gateway Fees
if (! function_exists('calculate_gateway_fees')) {
    function calculate_gateway_fees($amount, $data, $same_country)
    {
        $fees = 0;
        if ($data?->fixed ?? null) {
            $fees += $data->fixed;
        }
        if ($same_country && ($data?->same_country ?? null)) {
            $fees += $amount * ($data->same_country / 100);
        }
        if (! $same_country && ($data?->other_countries ?? null)) {
            $fees += $amount * ($data->other_countries / 100);
        }

        return $fees;
    }
}

// Convert to base unit value
if (! function_exists('convert_to_base_unit')) {
    function convert_to_base_unit($unit, $unit_id, $value)
    {
        if (! $unit || ! $unit_id || $unit->id == $unit_id) {
            return $value;
        }

        $subunit = $unit->subunits->where('id', $unit_id)->first();

        return match ($subunit?->operator) {
            '*'     => $value * $subunit->operation_value,
            '/'     => $value / $subunit->operation_value,
            '+'     => $value + $subunit->operation_value,
            '-'     => $value - $subunit->operation_value,
            default => $value,
        };
    }
}

// Is safe email
if (! function_exists('safe_email')) {
    function safe_email($email = '')
    {
        if (demo()) {
            return true;
        }
        $contains = str($email)->contains('@example.');

        return $email && ! $contains;
    }
}

// Get sql query
if (! function_exists('get_sql_query')) {
    function get_sql_query($query)
    {
        return vsprintf(str_replace('?', '%s', str_replace('?', "'?'", $query->toSql())), $query->getBindings());
    }
}
