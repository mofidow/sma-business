<?php

namespace App\Http\Controllers\Sma\Order;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Order\Income;
use Nnjeim\World\Models\Country;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Accounting\Account;
use App\Models\Sma\Setting\CustomField;
use App\Http\Requests\Sma\Order\IncomeRequest;

class IncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Order/Income/Index', [
            'custom_fields'   => CustomField::ofModel('income')->get(),
            'customer_fields' => CustomField::ofModel('customer')->get(),
            'countries'       => Country::with('states:id,name,country_id')->get(),
            'accounts'        => Account::active()->get(['id as value', 'title as label']),

            'pagination' => new Collection(
                Income::with(['customer:id,name,company', 'user:id,name', 'account:id,title'])->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IncomeRequest $request)
    {
        Income::create($request->validated());

        return to_route('incomes.index')->with('message', __('{record} has been {action}.', ['record' => 'Income', 'action' => 'created']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Income $income)
    {
        $income->load(['store', 'customer', 'account:id,title', 'user:id,name']);

        if ($request->json) {
            return response()->json($income);
        }

        return Inertia::render('Sma/Order/Income/Details', ['income' => $income]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(IncomeRequest $request, Income $income)
    {
        $income->update($request->validated());
        session()->flash('message', __('{record} has been {action}.', ['record' => 'Income', 'action' => 'updated']));

        return $request->listing == 'yes' ? to_route('incomes.index') : back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Income $income)
    {
        if ($income->{$income->deleted_at ? 'forceDelete' : 'delete'}()) {
            return to_route('incomes.index')->with('message', __('{record} has been {action}.', ['record' => 'Income', 'action' => 'deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function restore(Income $income)
    {
        $income->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Income', 'action' => 'restored']));
    }

    public function destroyPermanently(Income $income)
    {
        if ($income->forceDelete()) {
            return to_route('incomes.index')->with('message', __('{record} has been {action}.', ['record' => 'Income', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
