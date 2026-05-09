<?php

namespace App\Http\Controllers\Sma\Order;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Order\Sale;
use App\Tec\Scopes\OfStore;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Order\Payment;
use App\Models\Sma\Order\CreditInstallment;
use App\Models\Sma\Setting\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    /**
     * List all credit sales with filters.
     */
    public function index(Request $request)
    {
        $filters = array_merge($request->input('filters') ?? [], ['credit' => 1]);

        return Inertia::render('Sma/Order/Credit/Index', [
            'stores'  => Store::all(['id as value', 'name as label']),
            'users'   => User::employee()->get(['id as value', 'name as label']),

            'pagination' => new Collection(
                Sale::withoutGlobalScope(OfStore::class)
                    ->with(['customer:id,name,company', 'store:id,name', 'user:id,name', 'creditInstallments'])
                    ->filter($filters)
                    ->orderByDesc('date')
                    ->paginate()
                    ->withQueryString()
            ),
        ]);
    }

    /**
     * Convert an existing sale to a credit sale with installment plan.
     */
    public function store(Request $request, Sale $sale)
    {
        $request->validate([
            'installments'              => 'required|array|min:1',
            'installments.*.amount'     => 'required|numeric|min:0.01',
            'installments.*.due_date'   => 'required|date|after_or_equal:today',
        ]);

        DB::transaction(function () use ($request, $sale) {
            $sale->update([
                'is_credit'     => true,
                'credit_status' => 'pending',
            ]);

            // Remove any previously created installments
            $sale->creditInstallments()->delete();

            foreach ($request->installments as $row) {
                $sale->creditInstallments()->create([
                    'customer_id' => $sale->customer_id,
                    'store_id'    => $sale->store_id,
                    'user_id'     => auth()->id(),
                    'amount'      => $row['amount'],
                    'due_date'    => $row['due_date'],
                    'notes'       => $row['notes'] ?? null,
                    'status'      => 'pending',
                ]);
            }

            $this->recalculateCreditStatus($sale);
        });

        return back()->with('message', __('Sale has been converted to credit (Deyn).'));
    }

    /**
     * Show a single credit sale with its installment timeline.
     */
    public function show(Sale $sale)
    {
        abort_unless($sale->is_credit, 404);

        $sale->load([
            'customer:id,name,company,phone,email',
            'store:id,name',
            'user:id,name',
            'items.product:id,name,code',
            'payments:id,date,amount,method,reference',
            'creditInstallments' => fn ($q) => $q->orderBy('due_date'),
        ]);

        return Inertia::render('Sma/Order/Credit/View', ['sale' => $sale]);
    }

    /**
     * Update installment schedule for a credit sale.
     */
    public function update(Request $request, Sale $sale)
    {
        abort_unless($sale->is_credit, 404);

        $request->validate([
            'installments'              => 'required|array|min:1',
            'installments.*.amount'     => 'required|numeric|min:0.01',
            'installments.*.due_date'   => 'required|date',
        ]);

        DB::transaction(function () use ($request, $sale) {
            // Only delete unpaid installments
            $sale->creditInstallments()->where('status', '!=', 'paid')->delete();

            foreach ($request->installments as $row) {
                $sale->creditInstallments()->create([
                    'customer_id' => $sale->customer_id,
                    'store_id'    => $sale->store_id,
                    'user_id'     => auth()->id(),
                    'amount'      => $row['amount'],
                    'due_date'    => $row['due_date'],
                    'notes'       => $row['notes'] ?? null,
                    'status'      => 'pending',
                ]);
            }

            $this->recalculateCreditStatus($sale);
        });

        return back()->with('message', __('Credit installment schedule updated.'));
    }

    /**
     * Record a payment against a specific installment.
     */
    public function recordPayment(Request $request, Sale $sale, CreditInstallment $installment)
    {
        abort_unless($sale->is_credit && $installment->sale_id === $sale->id, 404);
        abort_if($installment->status === 'paid', 422, 'This installment is already paid.');

        $request->validate([
            'amount'  => 'required|numeric|min:0.01|max:' . $installment->amount,
            'method'  => 'required|string|max:50',
            'date'    => 'nullable|date',
            'notes'   => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $sale, $installment) {
            $amount = (float) $request->amount;
            $date   = $request->date ?? now()->toDateString();

            // Create a payment record on the sale (using existing payment system)
            $payment = Payment::create([
                'reference'    => 'CRD-' . $installment->id . '-' . now()->format('YmdHis'),
                'date'         => $date,
                'amount'       => $amount,
                'method'       => $request->method,
                'received'     => true,
                'payment_for'  => 'Customer',
                'sale_id'      => $sale->id,
                'customer_id'  => $sale->customer_id,
                'store_id'     => $sale->store_id,
                'user_id'      => auth()->id(),
            ]);

            // Attach to sale via polymorphic pivot
            $sale->payments()->attach($payment->id, ['amount' => $amount]);

            // Mark installment paid
            $installment->update([
                'paid_amount' => $amount,
                'paid_date'   => $date,
                'status'      => 'paid',
                'notes'       => $request->notes,
            ]);

            // Update sale paid column
            $sale->increment('paid', $amount);

            $this->recalculateCreditStatus($sale->fresh());
        });

        return back()->with('message', __('Installment payment recorded successfully.'));
    }

    /**
     * Soft-delete a credit sale (removes credit flag too).
     */
    public function destroy(Sale $sale)
    {
        abort_unless($sale->is_credit, 404);

        if ($sale->delete()) {
            return to_route('credits.index')->with('message', __('Credit sale has been deleted.'));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function restore(Sale $sale)
    {
        $sale->restore();

        return back()->with('message', __('Credit sale has been restored.'));
    }

    public function destroyPermanently(Sale $sale)
    {
        abort_unless($sale->is_credit, 404);

        if ($sale->forceDelete()) {
            return to_route('credits.index')->with('message', __('Credit sale has been permanently deleted.'));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    /**
     * Recalculate and persist the credit_status on the sale.
     */
    private function recalculateCreditStatus(Sale $sale): void
    {
        $installments = $sale->creditInstallments()->withTrashed(false)->get();

        $totalAmount = $installments->sum('amount');
        $paidAmount  = $installments->where('status', 'paid')->sum('amount');
        $hasOverdue  = $installments->filter(fn ($i) => $i->status !== 'paid' && $i->due_date?->lt(now()))->count() > 0;

        if ($paidAmount >= $totalAmount && $totalAmount > 0) {
            $status = 'paid';
        } elseif ($hasOverdue) {
            $status = 'overdue';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        } else {
            $status = 'pending';
        }

        $sale->update(['credit_status' => $status]);
    }
}
