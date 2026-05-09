<?php

namespace App\Http\Controllers\Sma\Repair;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Repair\Technician;
use App\Http\Requests\Sma\Repair\TechnicianRequest;

class TechnicianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters');

        return Inertia::render('Sma/Repair/Technician/Index', [
            'pagination' => new Collection(
                Technician::filter($filters)
                    ->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TechnicianRequest $request)
    {
        $technician = Technician::create($request->validated());

        return back()
            ->with('data', $technician)
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Technician'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Technician $technician)
    {
        return $technician->load('repairOrders');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TechnicianRequest $request, Technician $technician)
    {
        $technician->update($request->validated());

        return redirect()->route('technicians.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Technician'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Technician $technician)
    {
        // Check if technician has repair orders
        if ($technician->repairOrders()->exists()) {
            return back()->with('error', __('{model} cannot be {action}. It has associated repair orders.', [
                'model'  => __('Technician'),
                'action' => __('deleted'),
            ]));
        }

        if ($technician->{$technician->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Technician'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Technician'),
            'action' => __('deleted'),
        ]));
    }
}
