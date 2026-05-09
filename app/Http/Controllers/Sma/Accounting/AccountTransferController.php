<?php

namespace App\Http\Controllers\Sma\Accounting;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Accounting\Account;
use App\Models\Sma\Accounting\AccountTransfer;
use App\Http\Requests\Sma\Accounting\AccountTransferRequest;

class AccountTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Inertia::render('Sma/Accounting/AccountTransfer/Index', [
            'accounts' => Account::active()->orderBy('title')->get(['id', 'title']),

            'pagination' => new Collection(
                AccountTransfer::with('fromAccount:id,title', 'toAccount:id,title', 'user:id,name')
                    ->filter($request->input('filters'))
                    ->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountTransferRequest $request)
    {
        AccountTransfer::create($request->validated());

        return redirect()->route('account-transfers.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Transfer'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(AccountTransfer $accountTransfer)
    {
        return $accountTransfer->load('fromAccount:id,title', 'toAccount:id,title', 'user:id,name');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountTransfer $accountTransfer)
    {
        if ($accountTransfer->{$accountTransfer->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Transfer'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Transfer'),
            'action' => __('deleted'),
        ]));
    }
}
