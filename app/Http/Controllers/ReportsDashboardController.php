<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportsDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $startDate = $request->date('start_date')?->startOfDay() ?? now()->startOfYear()->startOfDay();
        $endDate = $request->date('end_date')?->endOfDay() ?? now()->endOfYear()->endOfDay();

        // --- Base Query de Pedidos Válidos no Período ---
        $ordersQuery = Order::query()
            ->where('status', '!=', OrderStatusEnum::CANCELED)
            ->whereBetween('date', [$startDate, $endDate]);

        // --- KPI Summary ---
        $totalSales = (clone $ordersQuery)->sum('total_amount');
        $totalPaid = Payment::query()
            ->where('status', PaymentStatusEnum::CONFIRMED)
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('value');
        $totalOrders = (clone $ordersQuery)->count();
        $averageTicket = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // --- Anos disponíveis com vendas no sistema ---
        $availableYears = Order::query()
            ->where('status', '!=', OrderStatusEnum::CANCELED)
            ->selectRaw('DISTINCT EXTRACT(YEAR FROM date)::int as year')
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->values();

        if ($availableYears->isEmpty()) {
            $availableYears = collect([(int) now()->year]);
        }

        // --- Faturamento Mensal (Line Chart) ---
        $monthlyRevenue = (clone $ordersQuery)
            ->selectRaw("TO_CHAR(date, 'YYYY-MM') as month, SUM(total_amount) as total")
            ->groupByRaw("TO_CHAR(date, 'YYYY-MM')")
            ->orderBy('month')
            ->pluck('total', 'month');

        // --- Pagamentos Mensais (Line Chart) ---
        $monthlyPayments = Payment::query()
            ->where('status', PaymentStatusEnum::CONFIRMED)
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->selectRaw("TO_CHAR(paid_at, 'YYYY-MM') as month, SUM(value) as total")
            ->groupByRaw("TO_CHAR(paid_at, 'YYYY-MM')")
            ->orderBy('month')
            ->pluck('total', 'month');

        // --- Faturamento Diário (Heatmap) ---
        $dailyRevenue = (clone $ordersQuery)
            ->selectRaw('date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        // --- Pagamentos Diários (Heatmap) ---
        $dailyPayments = Payment::query()
            ->where('status', PaymentStatusEnum::CONFIRMED)
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->selectRaw('DATE(paid_at) as date, SUM(value) as total')
            ->groupByRaw('DATE(paid_at)')
            ->orderByRaw('DATE(paid_at)')
            ->pluck('total', 'date');

        // --- Distribuição por Método de Pagamento ---
        $paymentsByMethod = Payment::query()
            ->where('status', PaymentStatusEnum::CONFIRMED)
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->selectRaw('method, SUM(value) as total, COUNT(*) as count')
            ->groupBy('method')
            ->get();

        // --- Top 10 Clientes (Eloquent) ---
        $orderPeriodFilter = function ($query) use ($startDate, $endDate) {
            $query->where('status', '!=', OrderStatusEnum::CANCELED)
                ->whereBetween('date', [$startDate, $endDate]);
        };

        $topCustomers = Customer::query()
            ->whereHas('orders', $orderPeriodFilter)
            ->withSum(['orders as total_sales' => $orderPeriodFilter], 'total_amount')
            ->withSum(['orders as total_paid' => $orderPeriodFilter], 'paid_amount')
            ->withCount(['orders as orders_count' => $orderPeriodFilter])
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get(['id', 'name']);

        // --- Top 10 Produtos (Eloquent) ---
        $topProducts = OrderItem::query()
            ->whereHas('order', $orderPeriodFilter)
            ->with('product:id,name')
            ->selectRaw('product_id, SUM(quantity) as total_quantity, SUM(total_amount) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->product_id,
                'name' => $item->product?->name ?? 'Produto não encontrado',
                'total_quantity' => (float) $item->total_quantity,
                'total_revenue' => (float) $item->total_revenue,
            ]);

        return Inertia::render('Reports/Dashboard', [
            // KPIs
            'total_sales' => (float) $totalSales,
            'total_paid' => (float) $totalPaid,
            'total_balance' => (float) ($totalSales - $totalPaid),
            'average_ticket' => round((float) $averageTicket, 2),
            'total_orders' => $totalOrders,
            'available_years' => $availableYears,

            // Charts
            'monthly_revenue' => $monthlyRevenue,
            'monthly_payments' => $monthlyPayments,
            'daily_revenue' => $dailyRevenue,
            'daily_payments' => $dailyPayments,
            'payments_by_method' => $paymentsByMethod,

            // Rankings
            'top_customers' => $topCustomers,
            'top_products' => $topProducts,

            // Filters
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }
}
