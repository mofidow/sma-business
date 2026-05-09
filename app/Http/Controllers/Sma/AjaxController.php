<?php

namespace App\Http\Controllers\Sma;

use Illuminate\Http\Request;
use App\Models\Sma\Setting\Store;
use App\Models\Sma\People\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class AjaxController extends Controller
{
    public function alerts(Request $request)
    {
        $data = [];
        $user = auth()->user();
        $store_id = session('selected_store_id', null);

        $data['customers'] = Customer::whereHasBalanceAbove('due_limit')->count();

        if (! $store_id && ! $user->hasRole('Super Admin') && $user->cant('read-all')) {
            if (! $user->store_id) {
                return $data;
            }
            $store_id = $user->store_id;
        }

        $user = $request->user();
        if ($user->hasRole('Super Admin') || $user->can('read-all')) {
            // Can read all
            $data['stores'] = Store::without(['state', 'country'])->select(['id', 'name'])->withCount([
                'sales as due_sales'             => fn ($q) => $q->due(),
                'sales as unpaid_sales'          => fn ($q) => $q->unpaid(),
                'purchases as unpaid_purchases'  => fn ($q) => $q->unpaid(),
                'payments as unreceived_payment' => fn ($q) => $q->pending(),
                'stocks as reorder_stock'        => fn ($q) => $q->whereHasBalanceBelow('alert_quantity'),
            ])->when($store_id, fn ($q) => $q->where('id', $store_id))->active()->get();
        } else {
            // Can read only own
            $data['stores'] = Store::without(['state', 'country'])->select(['id', 'name'])->withCount([
                'sales as due_sales'             => fn ($q) => $q->due()->ofUser(),
                'sales as unpaid_sales'          => fn ($q) => $q->unpaid()->ofUser(),
                'purchases as unpaid_purchases'  => fn ($q) => $q->unpaid()->ofUser(),
                'payments as unreceived_payment' => fn ($q) => $q->pending()->ofUser(),
                'stocks as reorder_stock'        => fn ($q) => $q->whereHasBalanceBelow('alert_quantity'),
            ])->when($store_id, fn ($q) => $q->where('id', $store_id))->active()->get();
        }

        return response()->json($data);
    }

    public function language(Request $request, $language)
    {
        if ($language === 'toggle_rtl') {
            $currentRtl = session('rtl_support', $request->cookie('rtl_support', get_settings('rtl_support')));
            $newRtl = $currentRtl === '1' ? '0' : '1';
            session(['rtl_support' => $newRtl]);
            cookie()->queue(cookie()->forever('rtl_support', $newRtl));

            return back()->with('message', __('RTL support has been changed.'));
        }

        $langFiles = collect(json_decode(File::get(base_path('lang/languages.json')))->available)->pluck('value')->all();
        if (! in_array($language, $langFiles)) {
            return back()->with('error', __('Language is not available yet.'));
        }
        app()->setlocale($language);
        session(['language' => $language]);
        cookie()->queue(cookie()->forever('language', $language));

        return back()->with('message', __('Language has been changed.'));
    }

    public function langFile(Request $request, string $language)
    {
        $available = collect(json_decode(File::get(base_path('lang/languages.json')))->available)->pluck('value')->all();
        abort_unless(in_array($language, $available, true), 404);

        $path = base_path("lang/{$language}.json");
        abort_unless(File::exists($path), 404);

        $response = response()->file($path, [
            'Content-Type'  => 'application/json',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
        $response->setLastModified(\DateTime::createFromFormat('U', (string) File::lastModified($path)));
        $response->isNotModified($request);

        return $response;
    }

    public function selectStore($store)
    {
        if (! $store) {
            session()->forget('selected_store_id');

            return back()->with('message', __('Store has been unselected.'));
        }
        session(['selected_store_id' => $store]);

        if (session('select_store_from', null)) {
            $url = session('select_store_from');
            session()->forget('select_store_from');

            return redirect()->to($url)->with('message', __('Store has been selected.'));
        }

        return back()->with('message', __('Store has been selected.'));
    }
}
