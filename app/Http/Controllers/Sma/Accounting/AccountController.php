<?php

namespace App\Http\Controllers\Sma\Accounting;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Accounting\Account;
use App\Models\Sma\Accounting\AccountType;
use App\Http\Requests\Sma\Accounting\AccountRequest;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Inertia::render('Sma/Accounting/Account/Index', [
            'types' => AccountType::active()->get(['id', 'name']),

            'pagination' => new Collection(
                Account::with('accountType:id,name')
                    ->filter($request->input('filters'))
                    ->latest()->orderBy('title')->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountRequest $request)
    {
        $account = Account::create($request->validated());

        return redirect()->route('accounts.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Account'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        return $account;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountRequest $request, Account $account)
    {
        $account->update($request->validated());

        return redirect()->route('accounts.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Account'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Display a usage statement of the resource.
     */
    public function statement(Account $account)
    {
        return Inertia::render('Sma/Accounting/Account/Statement', [
            'account'    => $account,
            'pagination' => new Collection(
                $account->tracks()->balance()
                    ->orderByDesc('id')->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        if ($account->{$account->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Account'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Account'),
            'action' => __('deleted'),
        ]));
    }
}
