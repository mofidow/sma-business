<?php

namespace App\Tec\Services;

use App\Models\Sma\Order\Sale;
use App\Models\Sma\Order\Purchase;
use App\Models\Sma\Order\SaleItem;
use App\Models\Sma\Order\ReturnOrder;
use App\Models\Sma\Order\PurchaseItem;
use App\Models\Sma\Order\CostAllocation;

class CostAllocationService
{
    public function getCostingMethod(): string
    {
        return get_settings('inventory_accounting') ?? 'FIFO';
    }

    public function allocateCostForSale(Sale $sale): void
    {
        $sale->loadMissing(['items.product.products', 'items.product.recipes.ingredient', 'items.variations']);

        foreach ($sale->items as $item) {
            if ($item->product->type === 'Standard') {
                $this->allocateForSaleItem($item, $sale->store_id, $item->product_id, $item->base_quantity);
            } elseif ($item->product->type === 'Combo') {
                $totalCost = 0;
                foreach ($item->product->products as $comboProduct) {
                    $comboQuantity = $item->base_quantity * $comboProduct->pivot->quantity;
                    $totalCost += $this->allocateForSaleItem($item, $sale->store_id, $comboProduct->id, $comboQuantity, null, false);
                }
                $this->updateSaleItemCost($item, $totalCost);
            } elseif ($item->product->type === 'Recipe') {
                $totalCost = 0;
                foreach ($item->product->recipes as $recipe) {
                    $recipeQuantity = $item->base_quantity * $recipe->quantity;
                    $totalCost += $this->allocateForSaleItem($item, $sale->store_id, $recipe->ingredient_id, $recipeQuantity, null, false);
                }
                $this->updateSaleItemCost($item, $totalCost);
            }
        }

        $sale->update([
            'total_cost' => $sale->items()->sum('total_cost'),
        ]);
    }

    public function reverseCostAllocationsForSale(Sale $sale): void
    {
        $sale->loadMissing('items');

        foreach ($sale->items as $item) {
            $allocations = CostAllocation::query()
                ->ofSaleItem($item->id)
                ->ofType('sale')
                ->get();

            foreach ($allocations as $allocation) {
                if ($allocation->purchase_item_id) {
                    $purchaseItem = PurchaseItem::query()->find($allocation->purchase_item_id);
                    if ($purchaseItem) {
                        $purchaseItem->increment('balance', $allocation->quantity);

                        if ($allocation->variation_id) {
                            $purchaseItem->variations()
                                ->updateExistingPivot($allocation->variation_id, [
                                    'balance' => \DB::raw("balance + {$allocation->quantity}"),
                                ]);
                        }
                    }
                }

                $allocation->forceDelete();
            }
        }
    }

    public function initializePurchaseBalances(Purchase $purchase): void
    {
        $purchase->loadMissing('items.variations');

        foreach ($purchase->items as $item) {
            $item->update(['balance' => $item->base_quantity]);

            foreach ($item->variations as $variation) {
                $item->variations()->updateExistingPivot($variation->id, [
                    'balance' => $variation->pivot->base_quantity,
                ]);
            }
        }
    }

    public function reversePurchaseBalances(Purchase $purchase): void
    {
        $purchase->loadMissing('items.variations');

        foreach ($purchase->items as $item) {
            $item->update(['balance' => 0]);

            foreach ($item->variations as $variation) {
                $item->variations()->updateExistingPivot($variation->id, [
                    'balance' => 0,
                ]);
            }
        }
    }

    public function handleSaleReturnCosting(ReturnOrder $returnOrder): void
    {
        $returnOrder->loadMissing('items.variations');

        foreach ($returnOrder->items as $item) {
            if (! $item->sale_item_id) {
                continue;
            }

            $originalAllocations = CostAllocation::query()
                ->ofSaleItem($item->sale_item_id)
                ->ofType('sale')
                ->orderBy('id')
                ->get();

            $remainingQuantity = $item->base_quantity;

            foreach ($originalAllocations as $allocation) {
                if ($remainingQuantity <= 0) {
                    break;
                }

                $restoreQuantity = min($remainingQuantity, $allocation->quantity);

                if ($allocation->purchase_item_id) {
                    $purchaseItem = PurchaseItem::query()->find($allocation->purchase_item_id);
                    if ($purchaseItem) {
                        $purchaseItem->increment('balance', $restoreQuantity);

                        if ($allocation->variation_id) {
                            $purchaseItem->variations()
                                ->updateExistingPivot($allocation->variation_id, [
                                    'balance' => \DB::raw("balance + {$restoreQuantity}"),
                                ]);
                        }
                    }
                }

                CostAllocation::query()->create([
                    'sale_item_id'         => $item->sale_item_id,
                    'purchase_item_id'     => $allocation->purchase_item_id,
                    'variation_id'         => $allocation->variation_id,
                    'return_order_item_id' => $item->id,
                    'product_id'           => $allocation->product_id,
                    'store_id'             => $allocation->store_id,
                    'quantity'             => $restoreQuantity,
                    'unit_cost'            => $allocation->unit_cost,
                    'total_cost'           => format_decimal($restoreQuantity * $allocation->unit_cost, 4),
                    'type'                 => 'sale_return',
                ]);

                $remainingQuantity -= $restoreQuantity;
            }
        }
    }

    public function reverseSaleReturnCosting(ReturnOrder $returnOrder): void
    {
        $returnOrder->loadMissing('items');

        foreach ($returnOrder->items as $item) {
            $allocations = CostAllocation::query()
                ->where('return_order_item_id', $item->id)
                ->ofType('sale_return')
                ->get();

            foreach ($allocations as $allocation) {
                if ($allocation->purchase_item_id) {
                    $purchaseItem = PurchaseItem::query()->find($allocation->purchase_item_id);
                    if ($purchaseItem) {
                        $purchaseItem->decrement('balance', $allocation->quantity);

                        if ($allocation->variation_id) {
                            $purchaseItem->variations()
                                ->updateExistingPivot($allocation->variation_id, [
                                    'balance' => \DB::raw("balance - {$allocation->quantity}"),
                                ]);
                        }
                    }
                }

                $allocation->forceDelete();
            }
        }
    }

    public function handlePurchaseReturnCosting(ReturnOrder $returnOrder): void
    {
        $returnOrder->loadMissing('items.variations');

        foreach ($returnOrder->items as $item) {
            if (! $item->purchase_item_id) {
                continue;
            }

            $purchaseItem = PurchaseItem::query()->find($item->purchase_item_id);
            if (! $purchaseItem) {
                continue;
            }

            $purchaseItem->decrement('balance', $item->base_quantity);

            if ($item->variations->count()) {
                foreach ($item->variations as $variation) {
                    $purchaseItem->variations()
                        ->updateExistingPivot($variation->id, [
                            'balance' => \DB::raw("balance - {$variation->pivot->base_quantity}"),
                        ]);
                }
            }

            CostAllocation::query()->create([
                'purchase_item_id'     => $item->purchase_item_id,
                'return_order_item_id' => $item->id,
                'product_id'           => $item->product_id,
                'store_id'             => $returnOrder->store_id,
                'quantity'             => $item->base_quantity,
                'unit_cost'            => $purchaseItem->unit_cost,
                'total_cost'           => format_decimal($item->base_quantity * $purchaseItem->unit_cost, 4),
                'type'                 => 'purchase_return',
            ]);
        }
    }

    public function reversePurchaseReturnCosting(ReturnOrder $returnOrder): void
    {
        $returnOrder->loadMissing('items.variations');

        foreach ($returnOrder->items as $item) {
            $allocations = CostAllocation::query()
                ->where('return_order_item_id', $item->id)
                ->ofType('purchase_return')
                ->get();

            foreach ($allocations as $allocation) {
                if ($allocation->purchase_item_id) {
                    $purchaseItem = PurchaseItem::query()->find($allocation->purchase_item_id);
                    if ($purchaseItem) {
                        $purchaseItem->increment('balance', $allocation->quantity);

                        if ($item->variations->count()) {
                            foreach ($item->variations as $variation) {
                                $purchaseItem->variations()
                                    ->updateExistingPivot($variation->id, [
                                        'balance' => \DB::raw("balance + {$variation->pivot->base_quantity}"),
                                    ]);
                            }
                        }
                    }
                }

                $allocation->forceDelete();
            }
        }
    }

    /**
     * Allocate cost for a single sale item and return total cost allocated.
     */
    private function allocateForSaleItem(SaleItem $saleItem, int $storeId, int $productId, float $quantity, ?int $variationId = null, bool $updateCost = true): float
    {
        if ($saleItem->variations->count() && $variationId === null) {
            $totalCost = 0;
            foreach ($saleItem->variations as $variation) {
                $totalCost += $this->allocateForSaleItem(
                    $saleItem, $storeId, $productId,
                    $variation->pivot->base_quantity, $variation->id, false
                );
            }
            if ($updateCost) {
                $this->updateSaleItemCost($saleItem, $totalCost);
            }

            return $totalCost;
        }

        $allocations = $this->allocate($productId, $storeId, $quantity, $variationId);
        $totalCost = 0;

        foreach ($allocations as $alloc) {
            CostAllocation::query()->create([
                'sale_item_id'     => $saleItem->id,
                'purchase_item_id' => $alloc['purchase_item_id'],
                'variation_id'     => $variationId,
                'product_id'       => $productId,
                'store_id'         => $storeId,
                'quantity'         => $alloc['quantity'],
                'unit_cost'        => $alloc['unit_cost'],
                'total_cost'       => format_decimal($alloc['quantity'] * $alloc['unit_cost'], 4),
                'type'             => 'sale',
            ]);

            $totalCost += $alloc['quantity'] * $alloc['unit_cost'];
        }

        if ($updateCost) {
            $this->updateSaleItemCost($saleItem, $totalCost);
        }

        return $totalCost;
    }

    private function updateSaleItemCost(SaleItem $saleItem, float $totalCost): void
    {
        $unitCost = $saleItem->base_quantity > 0
            ? format_decimal($totalCost / $saleItem->base_quantity, 4)
            : 0;

        $saleItem->update([
            'cost'       => $unitCost,
            'total_cost' => format_decimal($totalCost, 4),
        ]);
    }

    /**
     * Core allocation logic: consume purchase lots based on costing method.
     *
     * @return array<int, array{purchase_item_id: int|null, quantity: float, unit_cost: float}>
     */
    private function allocate(int $productId, int $storeId, float $quantity, ?int $variationId = null): array
    {
        $method = $this->getCostingMethod();
        $allocations = [];
        $remainingQuantity = $quantity;

        if ($method === 'AVCO') {
            return $this->allocateAvco($productId, $storeId, $quantity, $variationId);
        }

        $query = PurchaseItem::query()
            ->available()
            ->ofStore($storeId)
            ->where('product_id', $productId);

        if ($variationId) {
            $query->whereHas('variations', fn ($q) => $q->where('variations.id', $variationId)->where('purchase_item_variation.balance', '>', 0));
        }

        $query = match ($method) {
            'LIFO'  => $query->orderBy('created_at', 'desc'),
            'EXPF'  => $query->orderBy('expiry_date', 'asc')->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'asc'), // FIFO
        };

        $purchaseItems = $query->get();

        foreach ($purchaseItems as $purchaseItem) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $availableBalance = $variationId
                ? (float) $purchaseItem->variations->where('id', $variationId)->first()?->pivot->balance ?? 0
                : (float) $purchaseItem->balance;

            if ($availableBalance <= 0) {
                continue;
            }

            $consumeQuantity = min($remainingQuantity, $availableBalance);

            $purchaseItem->decrement('balance', $consumeQuantity);

            if ($variationId) {
                $purchaseItem->variations()
                    ->updateExistingPivot($variationId, [
                        'balance' => \DB::raw("balance - {$consumeQuantity}"),
                    ]);
            }

            $allocations[] = [
                'purchase_item_id' => $purchaseItem->id,
                'quantity'         => $consumeQuantity,
                'unit_cost'        => (float) $purchaseItem->unit_cost,
            ];

            $remainingQuantity -= $consumeQuantity;
        }

        if ($remainingQuantity > 0) {
            $fallbackCost = $this->getLastPurchaseCost($productId, $storeId);

            $allocations[] = [
                'purchase_item_id' => null,
                'quantity'         => $remainingQuantity,
                'unit_cost'        => $fallbackCost,
            ];
        }

        return $allocations;
    }

    /**
     * AVCO: calculate weighted average cost, then decrement balances in FIFO order.
     *
     * @return array<int, array{purchase_item_id: int|null, quantity: float, unit_cost: float}>
     */
    private function allocateAvco(int $productId, int $storeId, float $quantity, ?int $variationId = null): array
    {
        $query = PurchaseItem::query()
            ->available()
            ->ofStore($storeId)
            ->where('product_id', $productId);

        if ($variationId) {
            $query->whereHas('variations', fn ($q) => $q->where('variations.id', $variationId)->where('purchase_item_variation.balance', '>', 0));
        }

        $availableLots = $query->get();

        $totalBalance = 0;
        $totalCostValue = 0;

        foreach ($availableLots as $lot) {
            $balance = $variationId
                ? (float) ($lot->variations->where('id', $variationId)->first()?->pivot->balance ?? 0)
                : (float) $lot->balance;
            $totalBalance += $balance;
            $totalCostValue += $balance * (float) $lot->unit_cost;
        }

        $weightedAvgCost = $totalBalance > 0
            ? $totalCostValue / $totalBalance
            : $this->getLastPurchaseCost($productId, $storeId);

        $allocations = [];
        $remainingQuantity = $quantity;

        $fifoLots = $availableLots->sortBy('created_at');

        foreach ($fifoLots as $purchaseItem) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $availableBalance = $variationId
                ? (float) ($purchaseItem->variations->where('id', $variationId)->first()?->pivot->balance ?? 0)
                : (float) $purchaseItem->balance;

            if ($availableBalance <= 0) {
                continue;
            }

            $consumeQuantity = min($remainingQuantity, $availableBalance);

            $purchaseItem->decrement('balance', $consumeQuantity);

            if ($variationId) {
                $purchaseItem->variations()
                    ->updateExistingPivot($variationId, [
                        'balance' => \DB::raw("balance - {$consumeQuantity}"),
                    ]);
            }

            $allocations[] = [
                'purchase_item_id' => $purchaseItem->id,
                'quantity'         => $consumeQuantity,
                'unit_cost'        => (float) format_decimal($weightedAvgCost, 4),
            ];

            $remainingQuantity -= $consumeQuantity;
        }

        if ($remainingQuantity > 0) {
            $allocations[] = [
                'purchase_item_id' => null,
                'quantity'         => $remainingQuantity,
                'unit_cost'        => (float) format_decimal($weightedAvgCost, 4),
            ];
        }

        return $allocations;
    }

    /**
     * Get the last purchase cost for a product in a store (fallback for oversold).
     */
    private function getLastPurchaseCost(int $productId, int $storeId): float
    {
        $lastPurchaseItem = PurchaseItem::query()
            ->where('product_id', $productId)
            ->where('store_id', $storeId)
            ->orderBy('created_at', 'desc')
            ->first();

        return (float) ($lastPurchaseItem?->unit_cost ?? 0);
    }
}
