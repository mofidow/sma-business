<?php

namespace App\Http\Controllers\Sma\Search;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\Sma\Order\Sale;
use Nnjeim\World\Models\Country;
use App\Models\Sma\Order\Purchase;
use App\Models\Sma\Product\Serial;
use App\Models\Sma\People\Customer;
use App\Models\Sma\People\Supplier;
use App\Models\Sma\Product\Product;
use App\Http\Controllers\Controller;
use App\Models\Sma\Product\Category;
use App\Models\Sma\Setting\CustomField;

class SearchController extends Controller
{
    public $limit = 15;

    public function categories(Request $request)
    {
        $categories = Category::query()->select(['id', 'name']);

        if ($request->search) {
            $categories->search($request->input('search'));
        }

        return $categories->take($this->limit)->get();
    }

    public function products(Request $request)
    {
        $products = Product::query()->select(['id', 'name', 'tax_included', 'type'])
            ->with('storeStock');

        if ($request->id) {
            $ids = is_array($request->id) ? $request->input('id') : Arr::wrap($request->input('id'));

            return $products->whereIn('id', $ids)->get();
        }

        if ($request->barcode) {
            $products->selectRaw('price, barcode_symbology');
        }

        if (in_array($request->type, ['Combo', 'Recipe'])) {
            $products->selectRaw('cost, price, min_price, max_price, unit_id')->whereNotIn('type', ['Combo', 'Recipe']);
        }

        if (in_array($request->type, ['adjustment', 'purchase'])) {
            $products->ofType('Standard');
        }

        if ($request->exact) {
            $products->where('code', $request->input('search'));
        } elseif ($request->search) {
            $products->search($request->input('search'));
        }

        return $products->take($this->limit)->get();
    }

    public function customers(Request $request)
    {
        $customers = Customer::query()->select(['id', 'company', 'name', 'phone', 'customer_group_id', 'price_group_id']);

        if ($request->id) {
            $ids = is_array($request->id) ? $request->input('id') : Arr::wrap($request->input('id'));

            $customers->whereIn('id', $ids);
        }

        if ($request->search) {
            $customers->search($request->input('search'));
        }

        return $customers->take($this->limit)->get();
    }

    public function suppliers(Request $request)
    {
        $suppliers = Supplier::query()->select(['id', 'company', 'name']);

        if ($request->id) {
            $ids = is_array($request->id) ? $request->input('id') : Arr::wrap($request->input('id'));

            $suppliers->whereIn('id', $ids);
        }

        if ($request->search) {
            $suppliers->search($request->input('search'));
        }

        return $suppliers->take($this->limit)->get();
    }

    public function sale(Request $request)
    {
        $user = $request->user();
        if ($user && $user->can('read-sales')) {
            $ref = $request->input('ref');
            $sale = Sale::with(['items.product', 'items.variations'])
                ->where('id', $ref)->orWhere('reference', $ref)->first();
            if ($sale && ($user->hasRole('Super Admin') || $sale->user_id == $user->id)) {
                return $sale;
            }
        }

        return null;
    }

    public function purchase(Request $request)
    {
        $user = $request->user();
        if ($user && $user->can('read-purchases')) {
            $ref = $request->input('ref');
            $purchase = Purchase::with(['items.product', 'items.variations'])
                ->where('id', $ref)->orWhere('reference', $ref)->first();
            if ($purchase && ($user->hasRole('Super Admin') || $purchase->user_id == $user->id)) {
                return $purchase;
            }
        }

        return null;
    }

    public function countries()
    {
        return Country::with('states:id,name,country_id')->get();
    }

    public function serials(Request $request)
    {
        $serials = Serial::query()->select(['id', 'number', 'product_id']);

        if ($request->id) {
            $ids = is_array($request->id) ? $request->input('id') : Arr::wrap($request->input('id'));

            return $serials->whereIn('id', $ids)->get();
        }

        $serials->where('product_id', $request->input('product_id'))->available();

        if ($request->search) {
            $serials->where('number', 'like', '%' . $request->input('search') . '%');
        }

        return $serials->orderBy('number')->take($request->limit ?? 50)->get();
    }

    public function fields(Request $request)
    {
        $type = $request->input('type');
        $types = CustomField::$types;
        if ($type && in_array($type, $types)) {
            return CustomField::ofModel($type)->get();
        }

        return [];
    }
}
