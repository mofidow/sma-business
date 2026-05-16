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
     * Route: POST /credits/{sale}/convert  → credits.convert
     * NOTE: uses {sale} not {credit} — parameter name matches.
     */
    public function store(Request $request, Sale $sale)
    {
        $request->validate([
            'installments'              => 'required|array|min:1',
            'installments.*.amount'     => 'required|numeric|min:0.01',
            'installments.*.due_date'   => 'required|date',
        ]);

        DB::transaction(function () use ($request, $sale) {
            $sale->update([
                'is_credit'     => true,
                'credit_status' => 'pending',
            ]);

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
     * Route: GET /credits/{credit}  → credits.show
     * Parameter name MUST be $credit to match route placeholder {credit}.
     */
    public function show(Sale $credit)
    {
        abort_unless($credit->is_credit, 404);

        $credit->load([
            'customer:id,name,company,phone,email',
            'store:id,name',
            'user:id,name',
            'items.product:id,name,code',
            'payments:id,date,amount,method,reference',
            'creditInstallments' => fn ($q) => $q->orderBy('due_date'),
        ]);

        return Inertia::render('Sma/Order/Credit/View', ['sale' => $credit]);
    }

    /**
     * Update installment schedule for a credit sale.
     * Route: PUT /credits/{credit}  → credits.update
     */
    public function update(Request $request, Sale $credit)
    {
        abort_unless($credit->is_credit, 404);

        $request->validate([
            'installments'              => 'required|array|min:1',
            'installments.*.amount'     => 'required|numeric|min:0.01',
            'installments.*.due_date'   => 'required|date',
        ]);

        DB::transaction(function () use ($request, $credit) {
            $credit->creditInstallments()->where('status', '!=', 'paid')->delete();

            foreach ($request->installments as $row) {
                $credit->creditInstallments()->create([
                    'customer_id' => $credit->customer_id,
                    'store_id'    => $credit->store_id,
                    'user_id'     => auth()->id(),
                    'amount'      => $row['amount'],
                    'due_date'    => $row['due_date'],
                    'notes'       => $row['notes'] ?? null,
                    'status'      => 'pending',
                ]);
            }

            $this->recalculateCreditStatus($credit);
        });

        return back()->with('message', __('Credit installment schedule updated.'));
    }

    /**
     * Record a payment against a specific installment.
     * Route: POST /credits/{sale}/pay/{installment}  → credits.pay
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

            // PaymentObserver::saved() handles attach + sale.paid increment
            Payment::create([
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

            $installment->update([
                'paid_amount' => $amount,
                'paid_date'   => $date,
                'status'      => 'paid',
                'notes'       => $request->notes,
            ]);

            $this->recalculateCreditStatus($sale->fresh());
        });

        return back()->with('message', __('Installment payment recorded successfully.'));
    }

    /**
     * Soft-delete a credit sale.
     * Route: DELETE /credits/{credit}  → credits.destroy
     */
    public function destroy(Sale $credit)
    {
        abort_unless($credit->is_credit, 404);

        if ($credit->delete()) {
            return to_route('credits.index')->with('message', __('Credit sale has been deleted.'));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    /**
     * Route: PUT /credits/{credit}/restore  → credits.restore
     */
    public function restore(Sale $credit)
    {
        $credit->restore();

        return back()->with('message', __('Credit sale has been restored.'));
    }

    /**
     * Route: DELETE /credits/{credit}/permanently  → credits.destroy.permanently
     */
    public function destroyPermanently(Sale $credit)
    {
        abort_unless($credit->is_credit, 404);

        if ($credit->forceDelete()) {
            return to_route('credits.index')->with('message', __('Credit sale has been permanently deleted.'));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

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
