<?php

namespace App\Http\Controllers\Sma\Pos;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\Sma\Pos\Order;
use App\Models\Sma\Pos\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Models\Sma\People\Customer;
use App\Models\Sma\Product\Product;
use App\Models\Sma\Product\Category;
use Illuminate\Http\RedirectResponse;
use App\Tec\Services\OrderItemCalculator;

class QrMenuController extends Controller
{
    /**
     * Display the public QR menu page for a table.
     */
    public function show(string $token, Request $request): View|RedirectResponse
    {
        $table = Table::where('qr_token', $token)
            ->with('hall:id,name', 'store:id,name')
            ->active()->firstOrFail();

        if (! $request->query('order')) {
            $activeOrder = Order::where('table_id', $table->id)
                ->whereIn('status', ['pending', 'processing'])
                ->qrOrders()->latest()->first();

            if ($activeOrder) {
                return redirect()->route('qr-menu.confirmed', [$token, 'order' => $activeOrder->number]);
            }
        }

        $categories = Category::select(['id', 'name', 'photo'])
            ->onlyParent()->active()
            ->withCount(['products' => fn ($q) => $q->whereHas('stores', fn ($s) => $s->where('store_id', $table->store_id))])
            ->with(['children' => fn ($q) => $q->select(['id', 'name', 'photo', 'category_id'])
                ->active()
                ->withCount(['childProducts' => fn ($p) => $p->whereHas('stores', fn ($s) => $s->where('store_id', $table->store_id))]),
            ])
            ->where(fn ($q) => $q
                ->whereHas('products', fn ($p) => $p->whereHas('stores', fn ($s) => $s->where('store_id', $table->store_id)))
                ->orWhereHas('children', fn ($c) => $c->active()->whereHas('childProducts', fn ($p) => $p->whereHas('stores', fn ($s) => $s->where('store_id', $table->store_id))))
            )
            ->orderBy('order')
            ->get();

        $menuSettings = [
            'name'          => get_settings('name'),
            'logo'          => get_settings('logo'),
            'logo_dark'     => get_settings('logo_dark'),
            'currency'      => get_settings('default_currency'),
            'inclusive_tax' => get_settings('inclusive_tax_formula') === 'inclusive',
        ];

        $existingOrder = null;
        if ($orderNumber = $request->query('order')) {
            $existingOrder = Order::where('number', $orderNumber)
                ->where('table_id', $table->id)
                ->qrOrders()
                ->whereIn('status', ['pending', 'processing'])
                ->first();
        }

        return view('qr-menu.menu', compact('table', 'categories', 'menuSettings', 'existingOrder'));
    }

    /**
     * Load products for a category via AJAX.
     */
    public function products(string $token, int $categoryId): JsonResponse
    {
        $table = Table::where('qr_token', $token)
            ->where('active', true)->firstOrFail();

        $products = Product::query()
            ->where(fn ($q) => $q->where('category_id', $categoryId)->orWhere('subcategory_id', $categoryId))
            ->whereHas('stores', fn ($q) => $q->where('store_id', $table->store_id))
            ->with([
                'stores' => fn ($q) => $q->where('store_id', $table->store_id),
                'taxes:id,rate',
                'variations' => fn ($q) => $q->select(['id', 'product_id', 'sku', 'code', 'meta', 'price']),
            ])
            ->get(['id', 'name', 'photo', 'category_id', 'subcategory_id', 'has_variants', 'variants', 'tax_included']);

        return response()->json($products->map(function ($p) {
            $price = $p->stores->first()?->pivot?->price ?? $p->price;
            $taxIds = $p->taxes->pluck('id');

            $calculated = OrderItemCalculator::calculateTotal([
                'price'        => $price,
                'tax_included' => $p->tax_included,
                'taxes'        => $taxIds,
                'quantity'     => 1,
            ]);

            $data = [
                'id'           => $p->id,
                'name'         => $p->name,
                'photo'        => $p->photo,
                'price'        => $price,
                'has_variants' => (bool) $p->has_variants,
                'net_price'    => (float) $calculated['net_price'],
                'unit_price'   => (float) $calculated['unit_price'],
                'tax_amount'   => (float) $calculated['tax_amount'],
            ];

            if ($p->has_variants) {
                $data['variants'] = $p->variants ?? [];
                $data['variations'] = $p->variations->map(function ($v) use ($p, $taxIds, $price) {
                    $vPrice = $v->price ?? $price;

                    $vCalculated = OrderItemCalculator::calculateTotal([
                        'price'        => $vPrice,
                        'tax_included' => $p->tax_included,
                        'taxes'        => $taxIds,
                        'quantity'     => 1,
                    ]);

                    return [
                        'id'         => $v->id,
                        'sku'        => $v->sku,
                        'code'       => $v->code,
                        'meta'       => $v->meta ?? [],
                        'price'      => $vPrice,
                        'net_price'  => (float) $vCalculated['net_price'],
                        'unit_price' => (float) $vCalculated['unit_price'],
                        'tax_amount' => (float) $vCalculated['tax_amount'],
                    ];
                });
            }

            return $data;
        }));
    }

    /**
     * Submit a QR order from the public menu page.
     */
    public function store(string $token, Request $request): RedirectResponse
    {
        $table = Table::with('hall')
            ->active()->where('qr_token', $token)->firstOrFail();

        $validated = $request->validate([
            'customer_name'          => 'nullable|string|max:255',
            'items'                  => 'required|array|min:1',
            'items.*.id'             => 'required|integer',
            'items.*.name'           => 'required|string',
            'items.*.price'          => 'required|numeric|min:0',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.variation_id'   => 'nullable|integer',
            'items.*.variation_meta' => 'nullable|string',
            'notes'                  => 'nullable|string|max:1000',
            'order_number'           => 'nullable|string|max:20',
        ]);

        $groupedItems = [];
        foreach ($validated['items'] as $key => $value) {
            $product = Product::with([
                'taxes', 'variations',
                'stores' => fn ($q) => $q->where('store_id', $table->store_id),
            ])->where('id', $value['id'])->first();

            if (! $product) {
                return back()->withErrors(['items.' . $key => __('The selected product is invalid.')])->withInput();
            }

            $storeProduct = $product->stores()->where('store_id', $table->store_id)->first();

            if ($product->type == 'Standard' && ! $storeProduct) {
                return back()->withErrors(['items.' . $key . '.quantity' => __('The :product is out of stock.', ['product' => $product->name])])->withInput();
            }

            $product->price = $storeProduct?->pivot?->price ?? $product->price;

            $variation = null;
            if (! empty($value['variation_id']) && $product->has_variants) {
                $variation = $product->variations->firstWhere('id', $value['variation_id']);
            }

            $price = $variation?->price ?? $product->price;

            if ($variation) {
                $variationData = [
                    'id'       => $variation->id,
                    'sku'      => $variation->sku,
                    'code'     => $variation->code,
                    'meta'     => $variation->meta,
                    'price'    => $price,
                    'cost'     => $variation->cost ?? $product->cost,
                    'quantity' => $value['quantity'],
                ];

                if (isset($groupedItems[$product->id])) {
                    $existingVariation = collect($groupedItems[$product->id]['variations'])
                        ->firstWhere('id', $variation->id);

                    if ($existingVariation) {
                        $groupedItems[$product->id]['variations'] = collect($groupedItems[$product->id]['variations'])
                            ->map(fn ($v) => $v['id'] === $variation->id
                                ? array_merge($v, ['quantity' => $v['quantity'] + $value['quantity']])
                                : $v
                            )->all();
                    } else {
                        $groupedItems[$product->id]['variations'][] = $variationData;
                    }

                    $groupedItems[$product->id]['quantity'] += $value['quantity'];
                } else {
                    $groupedItems[$product->id] = [
                        'id'           => null,
                        'type'         => $product->type,
                        'name'         => $product->name,
                        'cost'         => $product->cost,
                        'price'        => $price,
                        'tax_included' => $product->tax_included,
                        'taxes'        => $product->taxes->pluck('id'),
                        'quantity'     => $value['quantity'],
                        'total'        => $value['quantity'] * $price,
                        'product_id'   => $product->id,
                        'product'      => $product,
                        'variations'   => [$variationData],
                    ];
                }
            } else {
                if (isset($groupedItems[$product->id]) && empty($groupedItems[$product->id]['variations'])) {
                    $groupedItems[$product->id]['quantity'] += $value['quantity'];
                } else {
                    $groupedItems[$product->id] = [
                        'id'           => null,
                        'type'         => $product->type,
                        'name'         => $product->name,
                        'cost'         => $product->cost,
                        'price'        => $price,
                        'tax_included' => $product->tax_included,
                        'taxes'        => $product->taxes->pluck('id'),
                        'quantity'     => $value['quantity'],
                        'total'        => $value['quantity'] * $price,
                        'product_id'   => $product->id,
                        'product'      => $product,
                    ];
                }
            }
        }

        $items = collect(array_values($groupedItems))->map(
            fn ($item) => OrderItemCalculator::calculateTotal($item)
        );
        $existingOrder = null;

        if (! empty($validated['order_number'])) {
            $existingOrder = Order::where('number', $validated['order_number'])
                ->where('table_id', $table->id)->qrOrders()
                ->whereIn('status', ['pending', 'processing'])
                ->first();
        }

        if ($existingOrder) {
            $existingItems = collect($existingOrder->data['items'] ?? []);

            foreach ($items as $newItem) {
                $found = false;
                $hasVariations = ! empty($newItem['variations']);

                $existingItems = $existingItems->map(function ($existing) use ($newItem, $hasVariations, &$found) {
                    if ($existing['product_id'] != $newItem['product_id']) {
                        return $existing;
                    }

                    $existingHasVariations = ! empty($existing['variations']);

                    if ($hasVariations && $existingHasVariations) {
                        $existingVariations = collect($existing['variations']);
                        foreach ($newItem['variations'] as $newVariation) {
                            $match = $existingVariations->firstWhere('id', $newVariation['id']);
                            if ($match) {
                                $existingVariations = $existingVariations->map(
                                    fn ($v) => $v['id'] === $newVariation['id']
                                        ? array_merge($v, ['quantity' => $v['quantity'] + $newVariation['quantity']])
                                        : $v
                                );
                            } else {
                                $existingVariations->push($newVariation);
                            }
                        }
                        $existing['variations'] = $existingVariations->values()->all();
                        $existing['quantity'] = collect($existing['variations'])->sum('quantity');
                        $found = true;
                    } elseif (! $hasVariations && ! $existingHasVariations) {
                        $existing['quantity'] += $newItem['quantity'];
                        $found = true;
                    }

                    return $existing;
                });

                if (! $found) {
                    $existingItems->push($newItem);
                }
            }

            $allItems = $existingItems->values()->all();
            $total = collect($allItems)->sum(fn ($item) => $item['price'] * $item['quantity']);

            $data = $existingOrder->data;
            $data['items'] = $allItems;
            $data['customer_name'] = $validated['customer_name'] ?? $existingOrder->customer_name;
            $data['notes'] = $validated['notes'] ?? $existingOrder->notes;

            $existingOrder->update([
                'customer_name'  => $validated['customer_name'] ?? $existingOrder->customer_name,
                'data'           => $data,
                'total'          => $total,
                'total_items'    => count($allItems),
                'total_quantity' => collect($allItems)->sum('quantity'),
                'notes'          => $validated['notes'] ?? $existingOrder->notes,
            ]);

            return redirect()->route('qr-menu.confirmed', [$token, 'order' => $existingOrder->number]);
        }

        $total = $items->sum(fn ($item) => $item['total']);
        $number = 'QR' . now()->format('ymd') . '-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $reference = 'QR-' . now()->format('M.d') . '.' . str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);

        $customer = Customer::find(get_settings('default_customer'));
        $data = [
            'date'          => now()->format('Y-m-d'),
            'number'        => $number,
            'reference'     => $reference,
            'items'         => $items,
            'customer_name' => $validated['customer_name'] ?? $customer->company ?? null,
            'notes'         => $validated['notes'] ?? null,
            'table_id'      => $table->id,
            'hall_id'       => $table->hall_id,
            'table'         => $table,
            'hall'          => $table->hall,
        ];

        $order = new Order;
        $order->number = $number;
        $order->reference = $reference;
        $order->source = 'qr';
        $order->status = 'pending';
        $order->table_id = $table->id;
        $order->hall_id = $table->hall_id;
        $order->store_id = $table->store_id;
        $order->customer_name = $validated['customer_name'] ?? $customer->company ?? null;
        $order->data = $data;
        $order->total = $total;
        $order->total_items = $items->count();
        $order->total_quantity = $items->sum('quantity');
        $order->notes = $validated['notes'] ?? null;
        $order->save();

        return redirect()->route('qr-menu.confirmed', [$token, 'order' => $order->number]);
    }

    /**
     * Show order confirmation page with order details.
     */
    public function confirmed(string $token, Request $request): View|RedirectResponse
    {
        $table = Table::with('hall:id,name', 'store:id,name')
            ->where('qr_token', $token)->active()->firstOrFail();

        $menuSettings = [
            'name'      => get_settings('name'),
            'logo'      => get_settings('logo'),
            'logo_dark' => get_settings('logo_dark'),
            'currency'  => get_settings('default_currency'),
        ];

        $order = null;
        if ($orderNumber = $request->query('order')) {
            $order = Order::where('number', $orderNumber)
                ->where('table_id', $table->id)
                ->qrOrders()->first();
        }

        if (! $order) {
            return to_route('qr-menu.show', $token)->withErrors(['order' => __('Order not found.')]);
        }

        return view('qr-menu.confirmed', compact('table', 'menuSettings', 'order'));
    }
}
