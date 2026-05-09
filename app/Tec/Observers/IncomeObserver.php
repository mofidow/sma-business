<?php

namespace App\Tec\Observers;

use App\Models\Sma\Order\Income;
use App\Models\Sma\People\Customer;
use App\Models\Sma\Accounting\Account;

class IncomeObserver
{
    /**
     * Handle the Income "saved" event.
     */
    public function saved(Income $income): void
    {
        $incomeLink = '<a class="link" href="' . route('incomes.index', ['id' => $income->id], false) . '">' . (__('Income') . ' #' . $income->id) . '</a>';

        if ($income->customer_id && ($income->wasRecentlyCreated || $income->isDirty(['customer_id', 'amount']))) {
            $income->customer->increaseBalance($income->amount, [
                'reference'   => $income,
                'description' => __('Sync balance for {income}', ['income' => $incomeLink]),
            ]);
        }

        if ($income->account_id && ($income->wasRecentlyCreated || $income->isDirty(['account_id', 'amount']))) {
            $income->account->increaseBalance($income->amount, [
                'reference'   => $income,
                'description' => __('Sync balance for {income}', ['income' => $incomeLink]),
            ]);
        }
    }

    /**
     * Handle the Income "updated" event.
     */
    public function updating(Income $income): void
    {
        $incomeLink = '<a class="link" href="' . route('incomes.index', ['id' => $income->id], false) . '">' . (__('Income') . ' #' . $income->id) . '</a>';

        if ($income->getOriginal('customer_id') && $income->isDirty(['customer_id', 'amount'])) {
            $customer = Customer::find($income->getOriginal('customer_id'));
            $customer->decreaseBalance($income->getOriginal('amount'), [
                'reference'   => $income,
                'description' => __('Reset balance for {income}', ['income' => $incomeLink]),
            ]);
        }

        if ($income->getOriginal('account_id') && $income->isDirty(['account_id', 'amount'])) {
            $account = Account::find($income->getOriginal('account_id'));
            $account->decreaseBalance($income->getOriginal('amount'), [
                'reference'   => $income,
                'description' => __('Reset balance for {income}', ['income' => $incomeLink]),
            ]);
        }
    }

    /**
     * Handle the Income "deleted" event.
     */
    public function deleted(Income $income): void
    {
        if (! $income->isForceDeleting()) {
            $incomeLink = '<a class="link" href="' . route('incomes.index', ['id' => $income->id], false) . '">' . (__('Income') . ' #' . $income->id) . '</a>';

            if ($income->customer_id) {
                $income->customer->decreaseBalance($income->amount, [
                    'reference'   => $income,
                    'description' => __('Reset balance for {income}', ['income' => $incomeLink]),
                ]);
            }

            if ($income->account_id) {
                $income->account->decreaseBalance($income->amount, [
                    'reference'   => $income,
                    'description' => __('Reset balance for {income}', ['income' => $incomeLink]),
                ]);
            }
        }
    }

    /**
     * Handle the Income "restored" event.
     */
    public function restored(Income $income): void
    {
        $incomeLink = '<a class="link" href="' . route('incomes.index', ['id' => $income->id], false) . '">' . (__('Income') . ' #' . $income->id) . '</a>';

        if ($income->customer_id) {
            $income->customer->increaseBalance($income->amount, [
                'reference'   => $income,
                'description' => __('Sync balance for {income}', ['income' => $incomeLink]),
            ]);
        }

        if ($income->account_id) {
            $income->account->increaseBalance($income->amount, [
                'reference'   => $income,
                'description' => __('Sync balance for {income}', ['income' => $incomeLink]),
            ]);
        }
    }
}
