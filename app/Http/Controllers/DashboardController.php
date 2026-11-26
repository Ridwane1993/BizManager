<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'period' => ['sometimes', 'in:week,month,quarter'],
        ]);
        $period = $validated['period'] ?? 'week';
        $currentMonth = now();
        $previousMonth = $currentMonth->copy()->subMonth();

        $totalSales = Sale::where('status', 'completed')
            ->sum('total_amount');

        $currentMonthSales = Sale::where('status', 'completed')
            ->whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->sum('total_amount');

        $previousMonthSales = Sale::where('status', 'completed')
            ->whereMonth('sale_date', $previousMonth->month)
            ->whereYear('sale_date', $previousMonth->year)
            ->sum('total_amount');

        $salesGrowth = null;

        if ($previousMonthSales > 0) {
            $salesGrowth = (($currentMonthSales - $previousMonthSales) / $previousMonthSales) * 100;
        }

        $totalClients = Client::count();
        $currentMonthClients = Client::whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->count();

        $previousMonthClients = Client::whereMonth('created_at', $previousMonth->month)
            ->whereYear('created_at', $previousMonth->year)
            ->count();

        $clientsGrowth = null;

        if ($previousMonthClients > 0) {
            $clientsGrowth = (($currentMonthClients - $previousMonthClients) / $previousMonthClients) * 100;
        }

        $totalProducts = Product::count();

        $unpaidInvoices = Invoice::query()
            ->where('payment_status', 'unpaid')
            ->selectRaw('COUNT(*) AS invoice_count, COALESCE(SUM(total_amount), 0) AS invoice_total')
            ->first();

        $unpaidInvoiceCount = (int) $unpaidInvoices->invoice_count;
        $unpaidInvoiceTotal = (float) $unpaidInvoices->invoice_total;

        $currentMonthExpenses = Expense::whereMonth('expense_date', $currentMonth->month)
            ->whereYear('expense_date', $currentMonth->year)
            ->sum('amount');
        $totalExpenses = $currentMonthExpenses;

        $expenseBreakdown = Expense::query()
            ->whereMonth('expense_date', $currentMonth->month)
            ->whereYear('expense_date', $currentMonth->year)
            ->select('category')
            ->selectRaw('SUM(amount) AS total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(static fn (Expense $expense): array => [
                'label' => $expense->category,
                'total' => (float) $expense->total,
            ])
            ->values();

        $previousMonthExpenses = Expense::whereMonth('expense_date', $previousMonth->month)
            ->whereYear('expense_date', $previousMonth->year)
            ->sum('amount');

        $expensesGrowth = null;

        if ($previousMonthExpenses > 0) {
            $expensesGrowth = (($currentMonthExpenses - $previousMonthExpenses) / $previousMonthExpenses) * 100;
        }

        $currentMonthProducts = Product::whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->count();

        $previousMonthProducts = Product::whereMonth('created_at', $previousMonth->month)
            ->whereYear('created_at', $previousMonth->year)
            ->count();

        $productsGrowth = null;

        if ($previousMonthProducts > 0) {
            $productsGrowth = (($currentMonthProducts - $previousMonthProducts) / $previousMonthProducts) * 100;
        }

        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'stock_alert_threshold')
            ->orderBy('stock')
            ->get();

        $recentSales = Sale::with('client')
            ->latest()
            ->take(5)
            ->get();

        $topProducts = Sale::query()
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereColumn('sale_items.company_id', 'sales.company_id')
            ->whereColumn('products.company_id', 'sales.company_id')
            ->where('sales.status', 'completed')
            ->whereDate('sales.sale_date', '>=', $currentMonth->copy()->startOfMonth())
            ->whereDate('sales.sale_date', '<=', $currentMonth->toDateString())
            ->select('products.name')
            ->selectRaw(
                'SUM(sale_items.quantity) AS quantity_sold, '
                .'SUM(sale_items.subtotal) AS total_revenue'
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('quantity_sold')
            ->limit(5)
            ->get()
            ->map(static fn (Sale $product): array => [
                'name' => $product->name,
                'quantity_sold' => (int) $product->quantity_sold,
                'total_revenue' => (float) $product->total_revenue,
            ])
            ->values();

        $salesChart = $this->buildSalesChart($period);

        return view('dashboard', compact(
            'totalSales',
            'totalClients',
            'totalProducts',
            'unpaidInvoiceCount',
            'unpaidInvoiceTotal',
            'salesChart',
            'lowStockProducts',
            'recentSales',
            'topProducts',
            'currentMonthSales',
            'currentMonthClients',
            'currentMonthProducts',
            'currentMonthExpenses',
            'expenseBreakdown',
            'previousMonthSales',
            'salesGrowth',
            'clientsGrowth',
            'productsGrowth',
            'totalExpenses',
            'expensesGrowth',
            'period'

        ));
    }

    public function salesChart(Request $request)
    {
        $validated = $request->validate([
            'period' => ['sometimes', 'in:week,month,quarter'],
        ]);
        $period = $validated['period'] ?? 'week';
        $salesChart = $this->buildSalesChart($period);

        return response()->json([
            'period' => $period,
            'data' => $salesChart,
        ]);
    }

    private function buildSalesChart(string $period): array
    {
        $today = Carbon::today();

        if ($period === 'week') {
            $start = $today->copy()->subDays(6);
            $end = $today;
        } elseif ($period === 'month') {
            $start = $today->copy()->startOfMonth();
            $end = $today->copy()->endOfMonth();
        } else {
            $start = $today->copy()->subMonths(2)->startOfMonth();
            $end = $today->copy()->endOfMonth();
        }

        $dailyTotals = Sale::query()
            ->where('status', 'completed')
            ->whereDate('sale_date', '>=', $start->toDateString())
            ->whereDate('sale_date', '<=', $end->toDateString())
            ->selectRaw('DATE(sale_date) AS sale_day, SUM(total_amount) AS total')
            ->groupBy('sale_day')
            ->pluck('total', 'sale_day');

        if ($period === 'quarter') {
            $monthlyTotals = [];

            foreach ($dailyTotals as $date => $total) {
                $month = substr((string) $date, 0, 7);
                $monthlyTotals[$month] = ($monthlyTotals[$month] ?? 0)
                    + (float) $total;
            }

            $salesChart = [];

            for (
                $month = $start->copy()->startOfMonth();
                $month->lte($end);
                $month->addMonth()
            ) {
                $date = $month->format('Y-m');
                $salesChart[] = [
                    'label' => $month->translatedFormat('M'),
                    'date' => $date,
                    'total' => (float) ($monthlyTotals[$date] ?? 0),
                ];
            }

            return $salesChart;
        }

        $salesChart = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateKey = $date->toDateString();
            $salesChart[] = [
                'label' => $period === 'week'
                    ? $date->translatedFormat('D')
                    : $date->format('d'),
                'date' => $dateKey,
                'total' => (float) ($dailyTotals[$dateKey] ?? 0),
            ];
        }

        return $salesChart;
    }
}
