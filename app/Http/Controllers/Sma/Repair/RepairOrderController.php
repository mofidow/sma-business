<?php

namespace App\Http\Controllers\Sma\Repair;

use Exception;
use Inertia\Inertia;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Setting\Tax;
use App\Http\Resources\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Tec\Actions\SaveRepairOrder;
use Illuminate\Support\Facades\Auth;
use App\Models\Sma\Repair\Technician;
use App\Models\Sma\Repair\RepairOrder;
use App\Models\Sma\Repair\ServiceType;
use App\Models\Sma\Setting\CustomField;
use Illuminate\Support\Facades\Storage;
use App\Tec\Services\OrderItemCalculator;
use App\Models\Sma\Repair\RepairOrderAttachment;
use App\Http\Requests\Sma\Repair\RepairOrderRequest;

class RepairOrderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];
        if (! ($filters['store_id'] ?? null) && session('selected_store_id', null)) {
            $filters['store_id'] = session('selected_store_id');
        }

        return Inertia::render('Sma/Repair/RepairOrder/Index', [
            'custom_fields' => CustomField::ofModel('repair')->get(),
            'serviceTypes'  => ServiceType::active()->ordered()->get(),
            'pagination'    => new Collection(
                RepairOrder::with(['customer', 'serviceType', 'technician', 'store', 'user'])
                    ->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    public function create()
    {
        return Inertia::render('Sma/Repair/RepairOrder/Form', [
            'taxes'           => Tax::all(),
            'serviceTypes'    => ServiceType::active()->ordered()->get(),
            'technicians'     => Technician::active()->get(['id', 'name']),
            'countries'       => Country::with('states:id,name,country_id')->get(),
            'custom_fields'   => CustomField::ofModel('repair')->get(),
            'customer_fields' => CustomField::ofModel('customer')->get(),
        ]);
    }

    public function store(RepairOrderRequest $request)
    {
        (new SaveRepairOrder)->execute($request->validated());

        return to_route('repair-orders.index')->with('message', __('{record} has been {action}.', [
            'record' => 'Repair Order',
            'action' => 'created',
        ]));
    }

    public function show(RepairOrder $repairOrder)
    {
        return $repairOrder->load([
            'customer', 'serviceType', 'technician', 'store',
            'user', 'invoice', 'attachments', 'attachments.uploader', 'taxes',
        ]);
    }

    public function edit(RepairOrder $repairOrder)
    {
        return Inertia::render('Sma/Repair/RepairOrder/Form', [
            'taxes'           => Tax::all(),
            'serviceTypes'    => ServiceType::active()->ordered()->get(),
            'technicians'     => Technician::active()->get(['id', 'name']),
            'countries'       => Country::with('states:id,name,country_id')->get(),
            'custom_fields'   => CustomField::ofModel('repair')->get(),
            'customer_fields' => CustomField::ofModel('customer')->get(),
            'current'         => $repairOrder->loadMissing(['attachments', 'attachments.uploader', 'taxes']),
        ]);
    }

    public function update(RepairOrderRequest $request, RepairOrder $repairOrder)
    {
        (new SaveRepairOrder)->execute($request->validated(), $repairOrder);
        session()->flash('message', __('{record} has been {action}.', ['record' => 'Repair Order', 'action' => 'updated']));

        return $request->listing == 'yes' ? to_route('repair-orders.index') : back();
    }

    public function destroy(RepairOrder $repairOrder)
    {
        if ($repairOrder->invoice_id) {
            return back()->with('error', __('{model} cannot be {action}. It has an associated invoice.', [
                'model'  => __('Repair Order'),
                'action' => __('deleted'),
            ]));
        }

        if ($repairOrder->{$repairOrder->deleted_at ? 'forceDelete' : 'delete'}()) {
            return to_route('repair-orders.index')->with('message', __('{record} has been {action}.', [
                'record' => 'Repair Order',
                'action' => 'deleted',
            ]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function generateInvoice(RepairOrder $repairOrder)
    {
        if (! $repairOrder->canGenerateInvoice()) {
            return back()->with('error', __('Invoice can only be generated for completed repair orders without existing invoices.'));
        }

        DB::beginTransaction();
        try {
            $repairOrder->load('taxes');
            $baseCost = $repairOrder->price;

            $data = [
                'customer_id'        => $repairOrder->customer_id,
                'store_id'           => $repairOrder->store_id,
                'repair_order_id'    => $repairOrder->id,
                'user_id'            => Auth::id(),
                'date'               => now(),
                'total_cost'         => $repairOrder->actual_cost ?: 0,
                'total_items'        => 1,
                'product_tax_amount' => 0,
                'total_tax_amount'   => 0,
                'subtotal'           => $baseCost,
                'grand_total'        => $baseCost,
                'details'            => "Repair Service: {$repairOrder->reference}\n{$repairOrder->problem_description}",
            ];

            $item = [
                'product_id'       => null,
                'comment'          => "Repair Service - {$repairOrder->serviceType->name}",
                'quantity'         => 1,
                'price'            => $baseCost,
                'net_price'        => $baseCost,
                'unit_price'       => $baseCost,
                'subtotal'         => $baseCost,
                'total'            => $baseCost,
                'tax_amount'       => 0,
                'total_tax_amount' => 0,
                'tax_included'     => $repairOrder->tax_included,
                'cost'             => $repairOrder->actual_cost ?: $baseCost,
                'total_cost'       => $repairOrder->actual_cost ?: $baseCost,
                'taxes'            => $repairOrder->taxes->pluck('id')->toArray(),
            ];

            if ($repairOrder->taxes->isNotEmpty()) {
                $item = OrderItemCalculator::calculateTotal($item, 'price');
            }

            $saleTaxes = $item['taxes'] ?? [];
            unset($item['tax_included'], $item['variations'], $item['taxes']);

            $data['subtotal'] = $item['subtotal'];
            $data['grand_total'] = $item['total'];
            $data['product_tax_amount'] = $item['total_tax_amount'];
            $data['total_tax_amount'] = $item['total_tax_amount'];

            $sale = Sale::create($data);
            $item = $sale->items()->create($item);
            $item->taxes()->attach($saleTaxes);
            $repairOrder->update(['invoice_id' => $sale->id]);

            DB::commit();

            return redirect()->route('sales.index', ['id' => $sale->id])
                ->with('message', __('Invoice has been successfully generated.'));
        } catch (Exception $e) {
            logger($e->getMessage(), ['stack' => $e->getTraceAsString()]);
            DB::rollBack();

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function downloadAttachment(RepairOrderAttachment $attachment)
    {
        return response()->download(Storage::disk('public')->path($attachment->path), $attachment->name);
    }

    public function deleteAttachment(RepairOrderAttachment $attachment)
    {
        $attachment->delete();

        return back()->with('message', __('Attachment has been successfully deleted.'));
    }
}
