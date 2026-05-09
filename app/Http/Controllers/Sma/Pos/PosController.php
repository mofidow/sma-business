<?php

namespace App\Http\Controllers\Sma\Pos;

use Inertia\Inertia;
use App\Models\Sma\Pos\Hall;
use Illuminate\Http\Request;
use App\Models\Sma\Pos\Order;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Setting\Tax;
use Nnjeim\World\Models\Country;
use App\Models\Sma\People\Customer;
use App\Models\Sma\Product\Product;
use App\Http\Controllers\Controller;
use App\Models\Sma\Product\Category;
use Illuminate\Support\Facades\Hash;
use App\Models\Sma\Setting\CustomField;

class PosController extends Controller
{
    /**
     * Display a POS screen.
     */
    public function index(Request $request)
    {
        if (! $request->session()->get('selected_store_id', false)) {
            $request->session()->flash('select_store', true);
            $request->session()->put('select_store_from', $request->fullUrl());
            $request->session()->flash('error', __('Please select a store first!'));

            return back();
        }
        $sale = null;
        $order = null;
        $default_category = get_settings('default_category');
        $customer = $request->customer_id ? Customer::find($request->input('customer_id')) : null;
        if ($request->sale) {
            $sale = Sale::withCount('returnOrders')->with([
                'attachments', 'items.variations', 'items.product',
                'store', 'customer', 'payments:id,date,amount,method,reference',
                'items.unit:id,code,name', 'user:id,name', 'hall:id,name', 'table:id,hall_id,name,seats',
            ])->find($request->input('sale'));
        }
        if ($request->order) {
            $order = Order::find($request->input('order'));
        }

        $design = get_settings('pos_design') ?? 'Modern';

        return Inertia::render('Sma/Pos/' . ($design == 'Modern' ? 'Modern' : 'Simple'), [
            'sale'            => $sale,
            'order'           => $order,
            'customer'        => $customer,
            'taxes'           => Tax::all(),
            'custom_fields'   => CustomField::ofModel('sale')->get(),
            'payment_fields'  => CustomField::ofModel('payment')->get(),
            'customer_fields' => CustomField::ofModel('customer')->get(),
            'countries'       => Country::with('states:id,name,country_id')->get(),
            'categories'      => Category::select(['id', 'name', 'photo'])->withCount('products')->onlyParent()->with(['children' => fn ($q) => $q->select(['id', 'name', 'photo', 'category_id'])->has('childProducts')->withCount('childProducts')])->orderBy('order')->get(),
            'products'        => Product::with('storeStock')->whereRelation('category', 'id', $default_category)->get(),
            'halls'           => get_settings('restaurant') ? Hall::with('tables:id,hall_id,name,seats')->ofStore()->active()->ordered()->get(['id', 'name']) : [],
        ]);
    }

    public function customerView(Request $request)
    {
        // $design = get_settings('pos_design') ?? 'Modern';

        return Inertia::render('Sma/Pos/CustomerView', [
            'pos_settings' => [
                'customer_view_heading' => get_settings('customer_view_heading'),
                'customer_view_message' => __(get_settings('customer_view_message') ?? '', [
                    'company' => get_settings('name'),
                    'user'    => '<span class="font-bold">' . $request->user()?->name . '</span>',
                ]),
            ],
        ]);
    }

    public function orders(Request $request)
    {
        return response()->json($request->user()->orders()->ofStore()->posOrders()->oldest()->paginate());
    }

    public function qrOrders(Request $request)
    {
        return response()->json(
            Order::withoutGlobalScope('mine')
                ->ofStore()->qrOrders()
                ->whereIn('status', ['pending', 'processing'])->oldest()->paginate()
        );
    }

    public function qrOrdersCount(Request $request)
    {
        $count = Order::withoutGlobalScope('mine')
            ->ofStore()->qrOrders()->pending()->count();

        return response()->json(['count' => $count]);
    }

    public function acceptQrOrder(Request $request, Order $order)
    {
        if (! $order->isQrOrder() || $order->isCompleted()) {
            return response()->json(['success' => false, 'message' => __('This order cannot be accepted.')], 422);
        }

        $order->update([
            'user_id'     => $request->user()->id,
            'register_id' => session('open_register_id') ?: $request->user()?->openedRegister?->id,
            'status'      => 'processing',
        ]);

        return response()->json([
            'success' => true,
            'message' => __('{record} has been {action}.', ['record' => 'QR Order', 'action' => 'accepted']),
            'order'   => $order->fresh(),
        ]);
    }

    public function products($category)
    {
        return response()->json(Product::with('storeStock')->where('category_id', $category)->orWhere('subcategory_id', $category)->get());
    }

    public function product(Product $product)
    {
        return response()->json($product->load('selectedStore'));
    }

    public function verifyPinCode(Request $request)
    {
        $pin = get_settings('pin_code');
        if (! $pin || ! $request->pin_code || ! Hash::check($request->pin_code, $pin)) {
            return response()->json(['success' => false, 'message' => __('Invalid PIN code!')], 422);
        }

        return response()->json(['success' => true, 'message' => __('PIN code verified successfully!')]);
    }
}
