<?php

namespace App\Http\Controllers\Sma\Pos;

use Inertia\Inertia;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Sma\Product\Category;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    public function index()
    {
        $pinCode = get_settings('pin_code') ? true : false;

        return Inertia::render('Sma/Pos/Setting', [
            'current' => get_settings([
                'pos_design', 'default_category', 'default_customer', 'quick_cash',
                'play_sound', 'pos_server', 'auto_open_order', 'print_dialog', 'restaurant',
                'customer_view_heading', 'customer_view_message', 'allow_sale_without_payment',
                'show_order_by_default', 'onscreen_keyboard', 'receipt_header', 'receipt_footer',
            ]) + ['pin_code' => $pinCode],
            'categories' => Category::active()->onlyParent()->get(['id as value', 'name as label']), ]);
    }

    public function store(Request $request)
    {
        $form = $request->validate([
            'pos_design'                 => 'required|in:Modern,Simple',
            'pin_code'                   => 'nullable|string|min:4|max:8',
            'default_category'           => 'required|exists:categories,id',
            'default_customer'           => 'required|exists:customers,id',
            'quick_cash'                 => 'nullable|array',
            'play_sound'                 => 'nullable|boolean',
            'pos_server'                 => 'nullable|boolean',
            'auto_open_order'            => 'nullable|boolean',
            'print_dialog'               => 'nullable|boolean',
            'restaurant'                 => 'nullable|boolean',
            'allow_sale_without_payment' => 'nullable|boolean',
            'show_order_by_default'      => 'nullable|boolean',
            'onscreen_keyboard'          => 'nullable|boolean',
            'customer_view_heading'      => 'nullable|string|max:190',
            'customer_view_message'      => 'nullable|string|max:1000',
            'receipt_header'             => 'nullable|string|max:1000',
            'receipt_footer'             => 'nullable|string|max:1000',
        ]);

        $pin = $form['pin_code'] ?? null;
        unset($form['pin_code']);

        $json = json_settings_fields();
        foreach ($form as $key => $value) {
            Setting::updateOrCreate(['tec_key' => $key], ['tec_value' => in_array($key, $json) ? json_encode($value ?? '') : $value]);
        }

        if (! empty($pin)) {
            Setting::updateOrCreate(['tec_key' => 'pin_code'], ['tec_value' => Hash::make($pin)]);
        }

        return back()->with('message', __('POS settings successfully saved.'));
    }
}
