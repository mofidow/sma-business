<?php

namespace App\Tec\Actions;

use App\Tec\Events\SaleEvent;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Product\Serial;
use Illuminate\Support\Facades\DB;
use Plugins\FiscalServices\FiscalServiceJob;

class SaveSale
{
    /**
     * Save sales with relationships
     *
     * @param  array<string, string>  $input
     * @param  Sale  $input
     */
    public function execute(array $data, Sale $sale = new Sale): Sale
    {
        // logger()->info('Sale form data: ', $data);

        $oldSale = null;
        if ($sale?->id) {
            $oldSale = $sale->load([
                'store', 'customer', 'items.product', 'items.variations',
            ])->replicateQuietly();
            $oldSale->id = $sale->id;
        }

        DB::transaction(function () use ($data, &$sale) {
            $items = $data['items'];
            $taxes = $data['taxes'] ?? [];
            $payments = $data['payments'] ?? [];
            $attachments = $data['attachments'] ?? [];
            unset($data['attachments'], $data['items'], $data['taxes'], $data['payments']);

            // OrderCalculator outputs item-level naming; parent table uses different names
            $data['sub_total']      = $data['subtotal'] ?? $data['sub_total'] ?? 0;
            $data['total_tax']      = $data['total_tax_amount'] ?? $data['total_tax'] ?? 0;
            $data['total_discount'] = $data['total_discount_amount'] ?? $data['total_discount'] ?? 0;
            unset($data['subtotal'], $data['total_tax_amount'], $data['total_discount_amount'],
                  $data['total'], $data['total_items'], $data['total_quantity'], $data['total_cost']);

            $sale->fill($data)->save();
            $sale->taxes()->sync($taxes);

            foreach ($items as $item) {
                $taxes = $item['taxes'] ?? [];
                $serials = $item['serials'] ?? null;
                $variations = $item['variations'] ?? null;
                unset($item['taxes'], $item['serials'], $item['variations'], $item['old_quantity'], $item['tax_included']);
                $item['quantity'] = $variations ? collect($variations)->sum('quantity') : $item['quantity'];

                if (($item['id'] ?? null) && $ii = $sale->items->where('id', $item['id'])->first()) {
                    $ii->update($item);
                    if ($variations ?? null) {
                        $variationIds = [];
                        $syncVariations = [];
                        foreach ($variations as $variation) {
                            $id = $variation['id'];
                            $variationIds[] = $id;
                            unset($variation['id'], $variation['old_quantity'], $variation['tax_included']);
                            $syncVariations[$id] = $variation;
                        }
                        $ii->variations()->sync($syncVariations);
                    }
                    $ii->taxes()->sync($taxes);
                    $this->syncSaleSerials($ii, $serials, $sale);
                } else {
                    $ii = $sale->items()->create($item);
                    if ($variations) {
                        $syncVariations = [];
                        foreach ($variations as $variation) {
                            $id = $variation['id'];
                            unset($variation['id'], $variation['old_quantity'], $variation['tax_included']);
                            $syncVariations[$id] = $variation;
                        }
                        $ii->variations()->sync($syncVariations);
                    }
                    $ii->taxes()->sync($taxes);
                    $this->syncSaleSerials($ii, $serials, $sale);
                }
            }

            $sale->saveAttachments($attachments);

            $sale->order?->delete();
            if (! empty($payments)) {
                foreach ($payments as $payment) {
                    if (($payment['amount'] ?? null) && ($payment['method'] ?? null)) {
                        $payment['date'] ??= now()->toDateString();
                        $payment['received'] = true;
                        $payment['payment_for'] = 'Customer';
                        $payment['sale_id'] = $sale->id;
                        $payment['customer_id'] = $sale->customer_id;
                        $sale->customer->payments()->create($payment);
                    }
                }
            }
        });

        $sale->refresh()->loadMissing([
            'store', 'customer', 'items.product', 'items.variations',
        ]);
        event(new SaleEvent($sale, $oldSale));

        if ($oldSale) {
            FiscalServiceJob::dispatchSaleUpdate($sale, $oldSale);
        } else {
            FiscalServiceJob::dispatchNewSale($sale);
        }

        return $sale;
    }

    /**
     * Sync serials for a sale item.
     */
    private function syncSaleSerials($saleItem, ?array $serialIds, Sale $sale): void
    {
        if ($serialIds === null) {
            return;
        }

        // Release old serials for this item
        $oldSerialIds = $saleItem->serials()->pluck('serials.id')->toArray();
        if (! empty($oldSerialIds)) {
            Serial::whereIn('id', $oldSerialIds)->update([
                'sold'         => null,
                'sale_id'      => null,
                'sale_item_id' => null,
            ]);
            $saleItem->serials()->detach();
        }

        // Mark selected serials as sold
        if (! empty($serialIds)) {
            Serial::whereIn('id', $serialIds)->update([
                'sold'         => true,
                'sale_id'      => $sale->id,
                'sale_item_id' => $saleItem->id,
            ]);
            $saleItem->serials()->sync($serialIds);
        }
    }
}
