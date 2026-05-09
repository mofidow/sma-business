<?php

namespace App\Http\Controllers\Sma\Report;

use App\Models\User;
use Inertia\Inertia;
use App\Tec\Scopes\OfStore;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Store;
use App\Http\Controllers\Controller;
use App\Models\Sma\Order\CostAllocation;

class InventoryAccountingController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        if (! ($filters['store_id'] ?? null) && session('selected_store_id', null)) {
            $filters['store_id'] = session('selected_store_id');
        }

        return Inertia::render('Sma/Report/InventoryAccounting', [
            'filters'    => $filters,
            'method'     => get_settings('inventory_accounting') ?? 'FIFO',
            'summary'    => $this->getSummary($filters),
            'pagination' => $this->getAllocations($filters, $request),
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
        $query = CostAllocation::query()
            ->withoutGlobalScope(OfStore::class);

        $this->applyFilters($query, $filters);

        $totals = $query->selectRaw(
            'COUNT(*) as total_allocations, '
            . 'COALESCE(SUM(CASE WHEN type = \'sale\' THEN total_cost ELSE 0 END), 0) as total_cogs, '
            . 'COALESCE(SUM(CASE WHEN type = \'sale\' THEN quantity ELSE 0 END), 0) as total_sold_quantity, '
            . 'COALESCE(SUM(CASE WHEN type = \'sale_return\' THEN total_cost ELSE 0 END), 0) as total_sale_return_cost, '
            . 'COALESCE(SUM(CASE WHEN type = \'sale_return\' THEN quantity ELSE 0 END), 0) as total_returned_quantity, '
            . 'COALESCE(SUM(CASE WHEN type = \'purchase_return\' THEN total_cost ELSE 0 END), 0) as total_purchase_return_cost, '
            . 'COALESCE(SUM(CASE WHEN purchase_item_id IS NULL AND type = \'sale\' THEN total_cost ELSE 0 END), 0) as fallback_cost'
        )->first();

        return [
            'total_allocations'          => (int) $totals->total_allocations,
            'total_cogs'                 => (float) $totals->total_cogs,
            'total_sold_quantity'        => (float) $totals->total_sold_quantity,
            'total_sale_return_cost'     => (float) $totals->total_sale_return_cost,
            'total_returned_quantity'    => (float) $totals->total_returned_quantity,
            'total_purchase_return_cost' => (float) $totals->total_purchase_return_cost,
            'fallback_cost'              => (float) $totals->fallback_cost,
            'net_cogs'                   => (float) $totals->total_cogs - (float) $totals->total_sale_return_cost,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function getAllocations(array $filters, Request $request): array
    {
        $query = CostAllocation::query()
            ->withoutGlobalScope(OfStore::class)
            ->with([
                'product:id,name,code',
                'store:id,name',
                'purchaseItem:id,purchase_id,batch_no,expiry_date',
                'purchaseItem.purchase:id,reference,date',
                'saleItem:id,sale_id',
                'saleItem.sale:id,reference,date',
                'variation:id,code',
                'returnOrderItem:id,return_order_id',
                'returnOrderItem.returnOrder:id,reference,type',
            ]);

        $this->applyFilters($query, $filters);

        if ($filters['type'] ?? null) {
            $query->ofType($filters['type']);
        }

        if ($filters['product_id'] ?? null) {
            $query->where('product_id', $filters['product_id']);
        }

        $paginated = $query->orderByDesc('created_at')
            ->paginate(get_settings('rows_per_page') ?? 25)
            ->withQueryString();

        return [
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'from'         => $paginated->firstItem(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'to'           => $paginated->lastItem(),
                'total'        => $paginated->total(),
            ],
            'links' => $paginated->linkCollection()->toArray(),
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        $query->when($filters['store_id'] ?? null, fn ($q, $storeId) => $q->where('store_id', $storeId))
            ->when($filters['start_date'] ?? null, fn ($q, $date) => $q->where('cost_allocations.created_at', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($q, $date) => $q->where('cost_allocations.created_at', '<=', $date));
    }
}
