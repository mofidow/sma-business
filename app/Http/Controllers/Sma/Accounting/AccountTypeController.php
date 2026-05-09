<?php

namespace App\Http\Controllers\Sma\Accounting;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Accounting\AccountType;
use App\Http\Requests\Sma\Accounting\AccountTypeRequest;

class AccountTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Inertia::render('Sma/Accounting/AccountType/Index', [
            'pagination' => new Collection(
                AccountType::filter($request->input('filters'))
                    ->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountTypeRequest $request)
    {
        AccountType::create($request->validated());

        return redirect()->route('account-types.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Account Type'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(AccountType $accountType)
    {
        return $accountType;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountTypeRequest $request, AccountType $accountType)
    {
        $accountType->update($request->validated());

        return redirect()->route('account-types.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Account Type'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountType $accountType)
    {
        if ($accountType->{$accountType->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Account Type'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Account Type'),
            'action' => __('deleted'),
        ]));
    }
}
