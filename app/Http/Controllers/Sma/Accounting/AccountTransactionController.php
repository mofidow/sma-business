<?php

namespace App\Http\Controllers\Sma\Accounting;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Accounting\Account;
use App\Models\Sma\Accounting\AccountTransaction;
use App\Http\Requests\Sma\Accounting\AccountTransactionRequest;

class AccountTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Inertia::render('Sma/Accounting/AccountTransaction/Index', [
            'accounts' => Account::active()->orderBy('title')->get(['id', 'title']),

            'pagination' => new Collection(
                AccountTransaction::with('account:id,title', 'user:id,name')
                    ->filter($request->input('filters'))
                    ->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountTransactionRequest $request)
    {
        AccountTransaction::create($request->validated());

        return redirect()->route('account-transactions.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Transaction'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(AccountTransaction $accountTransaction)
    {
        return $accountTransaction->load('account:id,title', 'user:id,name');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountTransaction $accountTransaction)
    {
        if ($accountTransaction->{$accountTransaction->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Transaction'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Transaction'),
            'action' => __('deleted'),
        ]));
    }
}
