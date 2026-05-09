<?php

namespace App\Http\Controllers\Sma\Hr;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Hr\Payroll;
use App\Models\Sma\Hr\Employee;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sma\Hr\PayrollRequest;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Hr/Payroll/Index', [
            'stores'     => Store::get(['id as value', 'name as label']),
            'employees'  => Employee::with('user:id,name')->get(['id', 'user_id', 'store_id', 'basic_salary', 'working_days_per_month']),
            'pagination' => new Collection(
                Payroll::withCount('payslips')->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PayrollRequest $request)
    {
        $validated = $request->validated();
        $payslips = $validated['payslips'] ?? [];
        unset($validated['payslips']);

        $payroll = Payroll::create([
            ...$validated,
            'user_id'      => auth()->id(),
            'total_amount' => collect($payslips)->sum('net_salary'),
        ]);

        foreach ($payslips as $payslipData) {
            $items = $payslipData['items'] ?? [];
            unset($payslipData['items']);

            $payslip = $payroll->payslips()->create($payslipData);

            foreach ($items as $item) {
                $payslip->items()->create($item);
            }
        }

        return back()
            ->with('data', $payroll)
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Payroll'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource with its payslips.
     */
    public function show(Request $request, Payroll $payroll)
    {
        if ($request->wantsJson()) {
            return response()->json($payroll->load(['payslips.employee.user:id,name', 'payslips.items']));
        }

        return Inertia::render('Sma/Hr/Payroll/Show', [
            'payroll' => $payroll->load(['payslips.employee.user:id,name', 'payslips.items']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PayrollRequest $request, Payroll $payroll)
    {
        $validated = $request->validated();
        $payslips = $validated['payslips'] ?? [];
        unset($validated['payslips']);

        $payroll->update([
            ...$validated,
            'total_amount' => collect($payslips)->sum('net_salary'),
        ]);

        $payroll->payslips()->each(fn ($p) => $p->items()->delete());
        $payroll->payslips()->delete();

        foreach ($payslips as $payslipData) {
            $items = $payslipData['items'] ?? [];
            unset($payslipData['items']);

            $payslip = $payroll->payslips()->create($payslipData);

            foreach ($items as $item) {
                $payslip->items()->create($item);
            }
        }

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Payroll'),
            'action' => __('updated'),
        ]));
    }

    /**
     * Mark the payroll as paid.
     */
    public function markPaid(Payroll $payroll)
    {
        $payroll->update(['status' => 'paid']);
        $payroll->payslips()->update(['status' => 'paid', 'paid_at' => now()->toDateString()]);

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Payroll'),
            'action' => __('marked as paid'),
        ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payroll $payroll)
    {
        if ($payroll->{$payroll->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Payroll'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Payroll'),
            'action' => __('deleted'),
        ]));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Payroll::whereIn('id', $request->selection)->get() as $record) {
            $record->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : '';
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed - $count]));
    }

    public function restore(Payroll $payroll)
    {
        $payroll->restore();

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Payroll'),
            'action' => __('restored'),
        ]));
    }

    public function destroyPermanently(Payroll $payroll)
    {
        if ($payroll->forceDelete()) {
            return to_route('payrolls.index')->with('message', __('{record} has been {action}.', ['record' => 'Payroll', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
