<?php

namespace App\Http\Controllers\Sma\Report;

use App\Models\User;
use Inertia\Inertia;
use App\Tec\Scopes\OfStore;
use Illuminate\Http\Request;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Order\Expense;
use App\Models\Sma\Setting\Store;
use App\Models\Sma\Order\Purchase;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Sma\Order\ReturnOrder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProfitLossController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        if (! ($filters['store_id'] ?? null) && session('selected_store_id', null)) {
            $filters['store_id'] = session('selected_store_id');
        }

        return Inertia::render('Sma/Report/ProfitLoss', [
            'filters'    => $filters,
            'summary'    => $this->getSummary($filters),
            'pagination' => $this->getLedger($filters, $request),
            'stores'     => Store::all(['id as value', 'name as label']),
            'users'      => User::employee()->get(['id as value', 'name as label']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function getSummary(array $filters): array
    {
        $sales = Sale::withoutGlobalScope(OfStore::class)
            ->selectRaw('COALESCE(SUM(grand_total), 0) as revenue, COALESCE(SUM(total_cost), 0) as cost_of_goods, COALESCE(SUM(total_tax_amount), 0) as sales_tax')
            ->filter($filters)->first();

        $expenses = Expense::withoutGlobalScope(OfStore::class)
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->filter($filters)->first();

        $purchases = Purchase::withoutGlobalScope(OfStore::class)
            ->selectRaw('COALESCE(SUM(grand_total), 0) as total, COALESCE(SUM(total_tax_amount), 0) as purchase_tax')
            ->filter($filters)->first();

        $saleReturns = ReturnOrder::withoutGlobalScope(OfStore::class)
            ->selectRaw('COALESCE(SUM(grand_total), 0) as total')
            ->where('type', 'sale')->filter($filters)->first();

        $purchaseReturns = ReturnOrder::withoutGlobalScope(OfStore::class)
            ->selectRaw('COALESCE(SUM(grand_total), 0) as total')
            ->where('type', 'purchase')->filter($filters)->first();

        $revenue = (float) $sales->revenue;
        $costOfGoods = (float) $sales->cost_of_goods;
        $grossProfit = $revenue - $costOfGoods;

        $operatingExpenses = (float) $expenses->total;
        $saleReturnsTotal = (float) $saleReturns->total;

        $netProfit = $grossProfit - $operatingExpenses - $saleReturnsTotal;

        return [
            'revenue'            => $revenue,
            'cost_of_goods'      => $costOfGoods,
            'gross_profit'       => $grossProfit,
            'operating_expenses' => $operatingExpenses,
            'sale_returns'       => $saleReturnsTotal,
            'purchase_returns'   => (float) $purchaseReturns->total,
            'purchases_total'    => (float) $purchases->total,
            'sales_tax'          => (float) $sales->sales_tax,
            'purchase_tax'       => (float) $purchases->purchase_tax,
            'net_profit'         => $netProfit,
            'profit_margin'      => $revenue > 0 ? ($netProfit / $revenue) * 100 : 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function getLedger(array $filters, Request $request): array
    {
        $perPage = 25;
        $page = (int) $request->input('page', 1);

        $salesQuery = Sale::withoutGlobalScope(OfStore::class)
            ->selectRaw("date, 'Sale' as type, 'Daily Sales' as description, SUM(grand_total) as credit, 0 as debit, SUM(total_cost) as cost")
            ->filter($filters)->groupBy('date');

        $expensesQuery = Expense::withoutGlobalScope(OfStore::class)
            ->selectRaw("date, 'Expense' as type, 'Daily Expenses' as description, 0 as credit, SUM(amount) as debit, 0 as cost")
            ->filter($filters)->groupBy('date');

        $purchasesQuery = Purchase::withoutGlobalScope(OfStore::class)
            ->selectRaw("date, 'Purchase' as type, 'Daily Purchases' as description, 0 as credit, SUM(grand_total) as debit, 0 as cost")
            ->filter($filters)->groupBy('date');

        $saleReturnsQuery = ReturnOrder::withoutGlobalScope(OfStore::class)
            ->selectRaw("date, 'Sale Return' as type, 'Daily Sale Returns' as description, 0 as credit, SUM(grand_total) as debit, 0 as cost")
            ->where('type', 'sale')->filter($filters)->groupBy('date');

        $purchaseReturnsQuery = ReturnOrder::withoutGlobalScope(OfStore::class)
            ->selectRaw("date, 'Purchase Return' as type, 'Daily Purchase Returns' as description, SUM(grand_total) as credit, 0 as debit, 0 as cost")
            ->where('type', 'purchase')->filter($filters)->groupBy('date');

        $unionQuery = $salesQuery
            ->union($expensesQuery)
            ->union($purchasesQuery)
            ->union($saleReturnsQuery)
            ->union($purchaseReturnsQuery);

        $total = DB::table(DB::raw("({$unionQuery->toSql()}) as ledger"))
            ->mergeBindings($unionQuery->getQuery())
            ->count();

        $offset = ($page - 1) * $perPage;

        $items = DB::table(DB::raw("({$unionQuery->toSql()}) as ledger"))
            ->mergeBindings($unionQuery->getQuery())
            ->orderByDesc('date')->orderBy('type')
            ->offset($offset)->limit($perPage)->get();

        $data = $items->map(fn ($item) => [
            'date'        => $item->date,
            'type'        => $item->type,
            'description' => $item->description,
            'credit'      => (float) $item->credit,
            'debit'       => (float) $item->debit,
            'cost'        => (float) $item->cost,
        ])->all();

        $paginator = new LengthAwarePaginator($data, $total, $perPage, $page, [
            'path'  => $request->url(),
            'query' => $request->query(),
        ]);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from'         => $paginator->firstItem(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'to'           => $paginator->lastItem(),
                'total'        => $paginator->total(),
            ],
            'links' => $paginator->linkCollection()->toArray(),
        ];
    }
}
