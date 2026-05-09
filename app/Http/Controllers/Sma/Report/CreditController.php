<?php

namespace App\Http\Controllers\Sma\Report;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Tec\Scopes\OfStore;
use App\Models\User;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Setting\Store;
use App\Models\Sma\People\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreditController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];
        $storeId = $filters['store_id'] ?? session('selected_store_id');

        $baseQuery = Sale::withoutGlobalScope(OfStore::class)
            ->where('is_credit', true)
            ->when($storeId, fn ($q) => $q->where('store_id', $storeId))
            ->when($filters['customer_id'] ?? null, fn ($q, $v) => $q->where('customer_id', $v))
            ->when($filters['credit_status'] ?? null, fn ($q, $v) => $q->where('credit_status', $v))
            ->when($filters['start_date'] ?? null, fn ($q, $v) => $q->where('date', '>=', $v))
            ->when($filters['end_date'] ?? null, fn ($q, $v) => $q->where('date', '<=', $v));

        // Totals
        $totals = (clone $baseQuery)
            ->selectRaw('COUNT(id) as count, SUM(grand_total) as total, SUM(paid) as paid, SUM(grand_total - COALESCE(paid,0)) as outstanding')
            ->first();

        // Aging buckets — based on oldest unpaid installment per sale
        $today = now()->toDateString();
        $aging = DB::table('credit_installments as ci')
            ->join('sales as s', 's.id', '=', 'ci.sale_id')
            ->whereNull('ci.deleted_at')
            ->where('ci.status', '!=', 'paid')
            ->where('s.is_credit', true)
            ->when($storeId, fn ($q) => $q->where('s.store_id', $storeId))
            ->selectRaw("
                SUM(CASE WHEN DATEDIFF(?, ci.due_date) BETWEEN 0 AND 30  THEN ci.amount - ci.paid_amount ELSE 0 END) as current_30,
                SUM(CASE WHEN DATEDIFF(?, ci.due_date) BETWEEN 31 AND 60 THEN ci.amount - ci.paid_amount ELSE 0 END) as days_31_60,
                SUM(CASE WHEN DATEDIFF(?, ci.due_date) BETWEEN 61 AND 90 THEN ci.amount - ci.paid_amount ELSE 0 END) as days_61_90,
                SUM(CASE WHEN DATEDIFF(?, ci.due_date) > 90             THEN ci.amount - ci.paid_amount ELSE 0 END) as over_90
            ", [$today, $today, $today, $today])
            ->first();

        // Customer cluster
        $cluster = Sale::withoutGlobalScope(OfStore::class)
            ->where('is_credit', true)
            ->when($storeId, fn ($q) => $q->where('store_id', $storeId))
            ->when($filters['customer_id'] ?? null, fn ($q, $v) => $q->where('customer_id', $v))
            ->selectRaw('customer_id, COUNT(id) as sale_count, SUM(grand_total) as total_credit, SUM(COALESCE(paid,0)) as total_paid, SUM(grand_total - COALESCE(paid,0)) as outstanding')
            ->groupBy('customer_id')
            ->orderByDesc('outstanding')
            ->with('customer:id,name,company,phone')
            ->get();

        // Paginated installment detail
        $installments = DB::table('credit_installments as ci')
            ->join('sales as s', 's.id', '=', 'ci.sale_id')
            ->join('customers as c', 'c.id', '=', 'ci.customer_id')
            ->whereNull('ci.deleted_at')
            ->where('s.is_credit', true)
            ->when($storeId, fn ($q) => $q->where('s.store_id', $storeId))
            ->when($filters['credit_status'] ?? null, fn ($q, $v) => $q->where('ci.status', $v))
            ->when($filters['customer_id'] ?? null, fn ($q, $v) => $q->where('ci.customer_id', $v))
            ->select(
                'ci.id', 'ci.status', 'ci.amount', 'ci.paid_amount', 'ci.due_date', 'ci.paid_date',
                's.reference as sale_reference', 's.grand_total',
                'c.name as customer_name', 'c.phone as customer_phone',
                DB::raw("DATEDIFF(CURDATE(), ci.due_date) as days_overdue")
            )
            ->orderBy('ci.due_date')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Sma/Report/Credit', [
            'totals'       => $totals,
            'aging'        => $aging,
            'cluster'      => $cluster,
            'installments' => $installments,
            'stores'       => Store::all(['id as value', 'name as label']),
            'customers'    => Customer::select('id as value', 'name as label')->orderBy('name')->get(),
        ]);
    }
}
