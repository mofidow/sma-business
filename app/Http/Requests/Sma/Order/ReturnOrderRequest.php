<?php

namespace App\Http\Requests\Sma\Order;

use App\Tec\Rules\ExtraAttributes;
use Illuminate\Support\Facades\DB;
use App\Models\Sma\Product\Product;
use App\Tec\Rules\ProductVariation;
use App\Tec\Services\OrderCalculator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class ReturnOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date'        => 'required|date',
            'type'        => 'required|in:Sale,Purchase',
            'type_ref'    => 'nullable|string',
            'reference'   => 'nullable|string|max:36',
            'sale_id'     => 'nullable|exists:sales,id',
            'purchase_id' => 'nullable|exists:purchases,id',
            'customer_id' => 'nullable|required_if:type,Sale|exists:customers,id',
            'supplier_id' => 'nullable|required_if:type,Purchase|exists:suppliers,id',
            'details'     => 'nullable',
            'surcharge'   => 'nullable|numeric',

            'return_payment_amount' => 'nullable|numeric|min:0',
            'return_payment_method' => 'nullable|string|max:25',

            'items'                         => 'required|array|min:1',
            'items.*.id'                    => 'nullable',
            'items.*.discount'              => 'nullable',
            'items.*.product_id'            => 'required|exists:products,id',
            'items.*.sale_item_id'          => 'nullable|exists:sale_items,id',
            'items.*.purchase_item_id'      => 'nullable|exists:purchase_items,id',
            'items.*.quantity'              => 'required|numeric|min:0',
            'items.*.unit_id'               => 'nullable|exists:units,id',
            'items.*.price'                 => 'nullable|required_if:type,Sale|numeric',
            'items.*.cost'                  => 'nullable|required_if:type,Purchase|numeric',
            'items.*.taxes'                 => 'nullable|array',
            'items.*.variations'            => new ProductVariation,
            'items.*.variations.*.id'       => new ProductVariation,
            'items.*.variations.*.quantity' => 'required_with:items.*.variations|numeric|min:0',
            'items.*.variations.*.unit_id'  => 'nullable|exists:units,id',
            'items.*.variations.*.cost'     => 'nullable',
            'items.*.variations.*.price'    => 'nullable',
            'items.*.variations.*.discount' => 'nullable',
            'items.*.variations.*.taxes'    => 'nullable|array',

            'extra_attributes' => ['nullable', new ExtraAttributes('return_order')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $saleId = $this->input('sale_id');
            $purchaseId = $this->input('purchase_id');
            $items = $this->input('items', []);
            $currentId = $this->route('return_order')?->id;

            if ($saleId && ! empty($items)) {
                $this->validateReturnedQuantities($validator, $items, 'sale_item_id', 'sale_id', (int) $saleId, $currentId, 'sale_items');
            }

            if ($purchaseId && ! empty($items)) {
                $this->validateReturnedQuantities($validator, $items, 'purchase_item_id', 'purchase_id', (int) $purchaseId, $currentId, 'purchase_items');
            }
        });
    }

    /**
     * Validate that items being returned have not already been fully returned
     * in other return orders for the same sale or purchase.
     */
    private function validateReturnedQuantities(
        Validator $validator,
        array $items,
        string $itemFk,
        string $orderFk,
        int $orderId,
        ?int $excludeId,
        string $itemsTable
    ): void {
        $itemIds = collect($items)->pluck($itemFk)->filter()->unique()->values();

        if ($itemIds->isEmpty()) {
            return;
        }

        $productIds = collect($items)->pluck('product_id')->filter()->unique();
        $products = Product::with('unit.subunits')->whereIn('id', $productIds)->get(['id', 'unit_id', 'name']);

        /** @var array<int, float> $submittedBaseQtys */
        $submittedBaseQtys = [];

        foreach ($items as $item) {
            $fkId = $item[$itemFk] ?? null;

            if (! $fkId) {
                continue;
            }

            $product = $products->firstWhere('id', $item['product_id'] ?? null);
            $productUnit = $product?->unit;
            $unitId = $item['unit_id'] ?? null;
            $quantity = (float) ($item['quantity'] ?? 0);
            $baseQty = ($unitId && $productUnit) ? convert_to_base_unit($productUnit, $unitId, $quantity) : $quantity;
            $submittedBaseQtys[$fkId] = ($submittedBaseQtys[$fkId] ?? 0) + $baseQty;
        }

        $originalQtys = DB::table($itemsTable)->whereIn('id', $itemIds)->pluck('base_quantity', 'id');

        $alreadyReturnedQtys = DB::table('return_order_items')
            ->join('return_orders', 'return_orders.id', '=', 'return_order_items.return_order_id')
            ->where('return_orders.' . $orderFk, $orderId)
            ->whereNull('return_orders.deleted_at')
            ->whereNull('return_order_items.deleted_at')
            ->whereIn('return_order_items.' . $itemFk, $itemIds)
            ->when($excludeId, fn ($q) => $q->where('return_orders.id', '!=', $excludeId))
            ->selectRaw('return_order_items.' . $itemFk . ', SUM(return_order_items.base_quantity) as returned_qty')
            ->groupBy('return_order_items.' . $itemFk)
            ->pluck('returned_qty', $itemFk);

        foreach ($submittedBaseQtys as $fkId => $newBaseQty) {
            $alreadyReturned = (float) ($alreadyReturnedQtys[$fkId] ?? 0);
            $original = (float) ($originalQtys[$fkId] ?? 0);

            if ($alreadyReturned + $newBaseQty > $original) {
                $productName = collect($items)
                    ->filter(fn ($i) => ($i[$itemFk] ?? null) == $fkId)
                    ->map(fn ($i) => $products->firstWhere('id', $i['product_id'] ?? null)?->name ?? __('Unknown'))
                    ->first() ?? __('Unknown');

                $remaining = max(0, $original - $alreadyReturned);
                $validator->errors()->add('items', __(':product has already been partially or fully returned. Remaining returnable quantity: :remaining.', [
                    'product'   => $productName,
                    'remaining' => format_decimal_qty($remaining),
                ]));
            }
        }
    }

    /**
     * Get the validated data from the request.
     *
     * @param  array|int|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        return OrderCalculator::calculate($data, $data['type'] == 'Purchase' ? 'cost' : 'price');
    }
}
