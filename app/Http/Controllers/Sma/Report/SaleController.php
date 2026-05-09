<?php

namespace App\Http\Controllers\Sma\Report;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Tec\Scopes\OfStore;
use Illuminate\Http\Request;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        if (! ($filters['store_id'] ?? null) && session('selected_store_id', null)) {
            $filters['store_id'] = session('selected_store_id');
        }

        $totals = Sale::withoutGlobalScope(OfStore::class)
            ->selectRaw('COUNT(id) as count, SUM(grand_total) as total, SUM(total_tax_amount) as tax, SUM(total_cost) as cost, SUM(paid) as paid')
            ->filter($filters)->first();

        return Inertia::render('Sma/Report/Sale', [
            'totals' => $totals,
            'stores' => Store::all(['id as value', 'name as label']),
            'users'  => User::employee()->get(['id as value', 'name as label']),

            'pagination' => new Collection(
                Sale::withoutGlobalScope(OfStore::class)
                    ->with([
                        'store:id,name', 'user:id,name',
                        'customer:id,name,company', 'delivery',
                    ])
                    ->withCount('returnOrders')->filter($filters)
                    ->orderByDesc('date')->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $sale = Sale::withoutGlobalScope(OfStore::class)->withCount('returnOrders')->with([
            'attachments', 'items.variations', 'items.product',
            'store', 'customer', 'payments:id,date,amount,method,reference',
            'returnOrders:id,reference,date,grand_total,return_payment_amount,return_payment_method,sale_id',
        ])->findOrFail($id);

        return response()->json($sale);
    }

    /**
     * Display daily sales calendar
     */
    public function dailySales(Request $request, $month = null, $year = null)
    {
        $now = now();
        $year = $year ?: $now->format('Y');
        $month = $month ?: $now->format('n');

        $filters = $request->input('filters') ?? [];
        $filters['end_date'] = Carbon::parse($year . '-' . $month . '-01')->endOfMonth();
        $filters['start_date'] = Carbon::parse($year . '-' . $month . '-01')->startOfMonth();

        if (! ($filters['store_id'] ?? null) && session('selected_store_id', null)) {
            $filters['store_id'] = session('selected_store_id');
        }

        $prev_date = $filters['start_date']->copy()->subDays(7);
        $prev_month_link = route('daily_sales.report', ['month' => $prev_date->format('m'), 'year' => $prev_date->format('Y')]);
        $next_date = $filters['end_date']->copy()->addDays(7);
        $next_month_link = route('daily_sales.report', ['month' => $next_date->format('m'), 'year' => $next_date->format('Y')]);
        // $store_id = session('selected_store_id', null);

        $data = Sale::withoutGlobalScope(OfStore::class)->selectRaw('date, SUM(total) as total, SUM(total_tax_amount) as total_tax_amount, SUM(grand_total) as grand_total, SUM(total_cost) as cost, SUM(paid) as paid')
            ->filter($filters)
            // ->whereBetween('date', [$start_date, $end_date])
            // ->when($store_id, fn ($query, $store_id) => $query->where('store_id', $store_id))
            ->groupBy('date')->orderBy('date')->get()
            ->groupBy(fn ($item) => Carbon::parse($item->date)->format('Y-m-d'))->transform(fn ($item) => $item[0])->all();

        $startOfCalendar = $filters['start_date']->copy()->firstOfMonth()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $filters['start_date']->copy()->lastOfMonth()->endOfWeek(Carbon::SUNDAY);

        $current_month = ['month' => $month - 1, 'year' => $year];

        $html = '<div class="calendar">';
        $html .= '<div class="days">';

        $dayLabels = [];
        $wd = Carbon::now()->startOfWeek();
        $dayLabels[] = $wd->getTranslatedShortDayName('D');

        for ($i = 0; $i < 6; $i++) {
            $dayLabels[] = $wd->addDay()->getTranslatedShortDayName('D');
        }

        foreach ($dayLabels as $key => $dayLabel) {
            $html .= '<div class="day-label label-' . $key . '">' . $dayLabel . '</div>';
        }

        $r = 1;
        while ($startOfCalendar <= $endOfCalendar) {
            $extraClass = $startOfCalendar->format('m') != $filters['start_date']->format('m') ? 'dull' : '';
            $extraClass .= $startOfCalendar->isToday() ? ' today' : '';
            $date = $startOfCalendar->format('Y-m-d');
            $html .= '<div class="day ' . $extraClass . ' day-' . $r . '"><div class="content">' . $startOfCalendar->format('j') . '</div>' . (isset($data[$date]) ? '<div class="amount ' . $date . ' data" data-date="' . $date . '">' . format_number($data[$date]['grand_total']) . '</div>' : '') . '</div>';
            $startOfCalendar->addDay();
            $r++;
        }
        $html .= '</div></div>';

        return Inertia::render('Sma/Report/DailySales', [
            'html'            => $html,
            'data'            => $data,
            'current_month'   => $current_month,
            'prev_month_link' => $prev_month_link,
            'next_month_link' => $next_month_link,
        ]);
    }

    /**
     * Display monthly sales calendar
     */
    public function monthlySales($year = null)
    {
        $now = now();
        $year = $year ?: $now->format('Y');
        $end_date = Carbon::parse($year . '-01-01')->endOfYear();
        $start_date = Carbon::parse($year . '-12-01')->startOfYear();

        $prev_date = $start_date->copy()->subDays(7);
        $prev_year_link = route('monthly_sales.report', ['year' => $prev_date->format('Y')]);
        $next_date = $end_date->copy()->addDays(7);
        $next_year_link = route('monthly_sales.report', ['year' => $next_date->format('Y')]);
        $store_id = session('selected_store_id', null);

        $data = Sale::withoutGlobalScope(OfStore::class)->selectRaw(DB::Raw('MONTH(date) as month, YEAR(date) as year, SUM(total) as total, SUM(total_tax_amount) as total_tax_amount, SUM(grand_total) as grand_total, SUM(total_cost) as cost, SUM(paid) as paid'))
            ->whereBetween('date', [$start_date, $end_date])
            ->when($store_id, fn ($query, $store_id) => $query->where('store_id', $store_id))
            ->groupBy(DB::raw('YEAR(date), MONTH(date)'))
            ->orderBy('year')->orderBy('month')->get()
            ->groupBy(fn ($item) => $item->month)
            ->transform(fn ($item) => $item[0])->all();

        $startOfCalendar = $start_date->copy();
        $endOfCalendar = $end_date->copy();
        $current_year = $start_date->format('Y');

        $html = '<div class="calendar">';
        $html .= '<div class="months">';

        $r = 1;
        while ($startOfCalendar <= $endOfCalendar) {
            $extraClass = $startOfCalendar->isCurrentMonth() ? ' current-month' : '';
            $month = $startOfCalendar->format('n');
            $html .= '<div class="month ' . $extraClass . ' month-' . $r . '"><div class="content">' . $startOfCalendar->getTranslatedShortMonthName('M') . '</div>' . (isset($data[$month]) ? '<div class="amount ' . $startOfCalendar->format('m') . ' data" data-month="' . $month . '">' . format_number($data[$month]['grand_total']) . '</div>' : '') . '</div>';
            $startOfCalendar->addMonth();
            $r++;
        }
        $html .= '</div></div>';

        return Inertia::render('Sma/Report/MonthlySales', [
            'html'           => $html,
            'data'           => $data,
            'current_year'   => $current_year,
            'prev_year_link' => $prev_year_link,
            'next_year_link' => $next_year_link,
        ]);
    }
}
