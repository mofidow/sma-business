<?php

namespace App\Http\Controllers\Sma\Order;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Nnjeim\World\Models\Country;
use App\Models\Sma\Order\Expense;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Accounting\Account;
use App\Models\Sma\Setting\CustomField;
use App\Http\Requests\Sma\Order\ExpenseRequest;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Order/Expense/Index', [
            'custom_fields'   => CustomField::ofModel('expense')->get(),
            'supplier_fields' => CustomField::ofModel('supplier')->get(),
            'countries'       => Country::with('states:id,name,country_id')->get(),
            'accounts'        => Account::active()->get(['id as value', 'title as label']),

            'pagination' => new Collection(
                Expense::with(['supplier:id,name,company', 'user:id,name', 'account:id,title'])->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ExpenseRequest $request)
    {
        Expense::create($request->validated());

        return to_route('expenses.index')->with('message', __('{record} has been {action}.', ['record' => 'Expense', 'action' => 'created']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Expense $expense)
    {
        $expense->load(['store', 'supplier', 'account:id,title', 'user:id,name']);

        if ($request->json) {
            return response()->json($expense);
        }

        return Inertia::render('Sma/Order/Expense/Details', ['expense' => $expense]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());
        session()->flash('message', __('{record} has been {action}.', ['record' => 'Expense', 'action' => 'updated']));

        return $request->listing == 'yes' ? to_route('expenses.index') : back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        if ($expense->{$expense->deleted_at ? 'forceDelete' : 'delete'}()) {
            return to_route('expenses.index')->with('message', __('{record} has been {action}.', ['record' => 'Expense', 'action' => 'deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function restore(Expense $expense)
    {
        $expense->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Expense', 'action' => 'restored']));
    }

    public function destroyPermanently(Expense $expense)
    {
        if ($expense->forceDelete()) {
            return to_route('expenses.index')->with('message', __('{record} has been {action}.', ['record' => 'Expense', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
