<?php

namespace App\Tec\Observers;

use App\Models\Sma\Order\Expense;
use App\Models\Sma\People\Supplier;
use App\Models\Sma\Accounting\Account;

class ExpenseObserver
{
    /**
     * Handle the Expense "saved" event.
     */
    public function saved(Expense $expense): void
    {
        $expenseLink = '<a class="link" href="' . route('expenses.index', ['id' => $expense->id], false) . '">' . (__('Expense') . ' #' . $expense->id) . '</a>';

        if ($expense->supplier_id && ($expense->wasRecentlyCreated || $expense->isDirty(['supplier_id', 'amount']))) {
            $expense->supplier->increaseBalance($expense->amount, [
                'reference'   => $expense,
                'description' => __('Sync balance for {expense}', ['expense' => $expenseLink]),
            ]);
        }

        if ($expense->account_id && ($expense->wasRecentlyCreated || $expense->isDirty(['account_id', 'amount']))) {
            $expense->account->decreaseBalance($expense->amount, [
                'reference'   => $expense,
                'description' => __('Sync balance for {expense}', ['expense' => $expenseLink]),
            ]);
        }
    }

    /**
     * Handle the Expense "updated" event.
     */
    public function updating(Expense $expense): void
    {
        $expenseLink = '<a class="link" href="' . route('expenses.index', ['id' => $expense->id], false) . '">' . (__('Expense') . ' #' . $expense->id) . '</a>';

        if ($expense->getOriginal('supplier_id') && $expense->isDirty(['supplier_id', 'amount'])) {
            $supplier = Supplier::find($expense->getOriginal('supplier_id'));
            $supplier->decreaseBalance($expense->getOriginal('amount'), [
                'reference'   => $expense,
                'description' => __('Reset balance for {expense}', ['expense' => $expenseLink]),
            ]);
        }

        if ($expense->getOriginal('account_id') && $expense->isDirty(['account_id', 'amount'])) {
            $account = Account::find($expense->getOriginal('account_id'));
            $account->increaseBalance($expense->getOriginal('amount'), [
                'reference'   => $expense,
                'description' => __('Reset balance for {expense}', ['expense' => $expenseLink]),
            ]);
        }
    }

    /**
     * Handle the Expense "deleted" event.
     */
    public function deleted(Expense $expense): void
    {
        if (! $expense->isForceDeleting()) {
            $expenseLink = '<a class="link" href="' . route('expenses.index', ['id' => $expense->id], false) . '">' . (__('Expense') . ' #' . $expense->id) . '</a>';

            if ($expense->supplier_id) {
                $expense->supplier->decreaseBalance($expense->amount, [
                    'reference'   => $expense,
                    'description' => __('Reset balance for {expense}', ['expense' => $expenseLink]),
                ]);
            }

            if ($expense->account_id) {
                $expense->account->increaseBalance($expense->amount, [
                    'reference'   => $expense,
                    'description' => __('Reset balance for {expense}', ['expense' => $expenseLink]),
                ]);
            }
        }
    }

    /**
     * Handle the Expense "restored" event.
     */
    public function restored(Expense $expense): void
    {
        $expenseLink = '<a class="link" href="' . route('expenses.index', ['id' => $expense->id], false) . '">' . (__('Expense') . ' #' . $expense->id) . '</a>';

        if ($expense->supplier_id) {
            $expense->supplier->increaseBalance($expense->amount, [
                'reference'   => $expense,
                'description' => __('Sync balance for {expense}', ['expense' => $expenseLink]),
            ]);
        }

        if ($expense->account_id) {
            $expense->account->decreaseBalance($expense->amount, [
                'reference'   => $expense,
                'description' => __('Sync balance for {expense}', ['expense' => $expenseLink]),
            ]);
        }
    }
}
