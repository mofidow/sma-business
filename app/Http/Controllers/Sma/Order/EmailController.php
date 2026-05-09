<?php

namespace App\Http\Controllers\Sma\Order;

use App\Models\Sma\Order\Sale;
use App\Models\Sma\Order\Payment;
use App\Models\Sma\Order\Purchase;
use App\Models\Sma\Order\Quotation;
use App\Http\Controllers\Controller;
use App\Models\Sma\Order\ReturnOrder;
use App\Tec\Notifications\Order\SaleNotification;
use App\Tec\Notifications\Order\PaymentNotification;
use App\Tec\Notifications\Order\QuotationNotification;
use App\Tec\Notifications\Order\ReturnOrderNotification;

class EmailController extends Controller
{
    public function sale(Sale $sale)
    {
        $sale->customer->notify(new SaleNotification($sale));

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Sale', 'action' => 'emailed']));
    }

    public function payment(Payment $payment)
    {
        $payment->customer->notify(new PaymentNotification($payment));

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Payment', 'action' => 'emailed']));
    }

    public function purchase(Purchase $purchase)
    {
        $purchase->customer->notify(new PurchaseNotification($purchase));

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Purchase', 'action' => 'emailed']));
    }

    public function quotation(Quotation $quotation)
    {
        $quotation->customer->notify(new QuotationNotification($quotation));

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Quotation', 'action' => 'emailed']));
    }

    public function returnOrder(ReturnOrder $return_order)
    {
        $return_order->customer->notify(new ReturnOrderNotification($return_order));

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Return order', 'action' => 'emailed']));
    }
}
