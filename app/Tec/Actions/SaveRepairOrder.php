<?php

namespace App\Tec\Actions;

use App\Models\Sma\Setting\Tax;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Sma\Repair\RepairOrder;
use App\Models\Sma\Repair\RepairOrderAttachment;

class SaveRepairOrder
{
    /**
     * Save repair orders with relationships
     *
     * @param  array<string, string>  $data
     */
    public function execute(array $data, RepairOrder $repairOrder = new RepairOrder): RepairOrder
    {
        DB::transaction(function () use ($data, &$repairOrder) {
            $taxes = $data['taxes'] ?? [];
            $attachments = $data['attachments'] ?? [];
            unset($data['attachments'], $data['taxes']);

            $repairOrder->fill($data)->save();

            // Sync taxes with calculated amounts
            if (isset($taxes)) {
                $taxData = [];
                if (! empty($taxes)) {
                    $taxModels = Tax::whereIn('id', $taxes)->get();
                    $baseCost = $data['price'] ?? $repairOrder->price ?? 0;

                    foreach ($taxModels as $tax) {
                        $taxAmount = ($baseCost * $tax->rate) / 100;
                        $taxData[$tax->id] = ['amount' => $taxAmount];
                    }
                }

                $repairOrder->taxes()->sync($taxData);
            }

            // Process attachments
            if ($attachments) {
                foreach ($attachments as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('repair_orders/' . $repairOrder->id, $filename, 'public');

                    RepairOrderAttachment::create([
                        'repair_order_id'   => $repairOrder->id,
                        'filename'          => $filename,
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type'         => $file->getMimeType(),
                        'path'              => $path,
                        'size'              => $file->getSize(),
                        'uploaded_by'       => Auth::id(),
                    ]);
                }
            }
        });

        return $repairOrder->refresh();
    }
}
