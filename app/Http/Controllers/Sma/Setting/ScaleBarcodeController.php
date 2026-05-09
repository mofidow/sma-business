<?php

namespace App\Http\Controllers\Sma\Setting;

use Inertia\Inertia;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ScaleBarcodeController extends Controller
{
    public function index()
    {
        return Inertia::render('Sma/Setting/ScaleBarcode', ['current' => get_settings('scale_barcode')]);
    }

    public function store(Request $request)
    {
        $form = $request->validate([
            'type' => 'required|in:price,weight',

            'length'      => 'required|integer',
            'flag_length' => 'required|integer',
            'code_start'  => 'required|integer',
            'code_length' => 'required|integer',

            'price_start'     => 'nullable|required_if:type,price|integer',
            'price_length'    => 'nullable|required_if:type,price|integer',
            'price_divide_by' => 'nullable|required_if:type,price|integer',

            'weight_start'     => 'nullable|required_if:type,weight|integer',
            'weight_length'    => 'nullable|required_if:type,weight|integer',
            'weight_divide_by' => 'nullable|required_if:type,weight|integer',
        ]);

        Setting::updateOrCreate(['tec_key' => 'scale_barcode'], ['tec_value' => json_encode($form)]);

        return back()->with('message', __('Scale barcode settings successfully saved.'));
    }
}
