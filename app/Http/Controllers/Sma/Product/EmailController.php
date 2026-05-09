<?php

namespace App\Http\Controllers\Sma\Product;

use App\Http\Controllers\Controller;
use App\Models\Sma\Product\Transfer;
use App\Tec\Notifications\Product\TransferNotification;

class EmailController extends Controller
{
    public function transfer(Transfer $transfer)
    {
        $transfer->toStore->notify(new TransferNotification($transfer));

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Transfer', 'action' => 'emailed']));
    }
}
