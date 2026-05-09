<?php

namespace App\Tec\Actions;

use App\Models\Sma\Order\Delivery;

class SaveDelivery
{
    /**
     * Save delivery with attachments
     *
     * @param  array<string, string>  $input
     * @param  Delivery  $input
     */
    public function execute(array $data, Delivery $delivery = new Delivery): Delivery
    {
        // logger()->info('Delivery form data: ', $data);

        $attachments = $data['attachments'] ?? [];
        unset($data['attachments']);

        $delivery->fill($data)->save();
        $delivery->saveAttachments($attachments);
        $delivery->sale->update(['address_id' => $delivery->address_id]);

        return $delivery;
    }
}
