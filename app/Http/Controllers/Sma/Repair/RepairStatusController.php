<?php

namespace App\Http\Controllers\Sma\Repair;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Sma\Repair\RepairOrder;

class RepairStatusController extends Controller
{
    /**
     * Display the public status check page
     */
    public function index(Request $request)
    {
        $result = null;

        if ($request->has('hash')) {
            $hash = $request->input('hash');

            $repairOrder = RepairOrder::with(['customer', 'serviceType', 'store'])->where('hash', $hash)->first();

            if (! $repairOrder) {
                return back()->with('error', __('No repair order found with the provided information.'));
            }

            $result = [
                'reference'      => $repairOrder->reference,
                'serial_no'      => $repairOrder->serial_no,
                'service_type'   => $repairOrder->serviceType->name,
                'status'         => $repairOrder->status,
                'priority'       => $repairOrder->priority,
                'received_date'  => $repairOrder->received_date,
                'due_date'       => $repairOrder->due_date,
                'completed_date' => $repairOrder->completed_date,
                'price'          => $repairOrder->price,
                'customer_notes' => $repairOrder->customer_notes,
                'store_name'     => $repairOrder->store->name,
                'store_phone'    => $repairOrder->store->phone,
                'status_color'   => $repairOrder->status_color,
                'priority_color' => $repairOrder->priority_color,
            ];
        }

        return Inertia::render('Public/RepairStatus', [
            'result' => $result,
            'hash'   => $request->input('hash'),
        ]);
    }
}
