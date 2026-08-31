<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SalesReportController extends Controller
{
    public function generalReport(Request $request): Response
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $startDate = ($validated['start_date'] ?? null)
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : now()->startOfYear()->startOfDay();

        $endDate = ($validated['end_date'] ?? null)
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : now()->endOfYear()->endOfDay();

        $diffInDays = max(1, (int) $startDate->diffInDays($endDate) + 1);
        $prevEndDate = $startDate->copy()->subSecond();
        $prevStartDate = $startDate->copy()->subDays($diffInDays)->startOfDay();

        $orderQuery = Order::query()
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->where('status', '!=', OrderStatusEnum::CANCELED);

        $prevOrderQuery = Order::query()
            ->where('date', '>=', $prevStartDate)
            ->where('date', '<=', $prevEndDate)
            ->where('status', '!=', OrderStatusEnum::CANCELED);

        // KPI Calculations
        $totalSales = (float) (clone $orderQuery)->sum('total_amount');
        $prevTotalSales = (float) (clone $prevOrderQuery)->sum('total_amount');

        $totalPaid = (float) (clone $orderQuery)->sum('paid_amount');
        $prevTotalPaid = (float) (clone $prevOrderQuery)->sum('paid_amount');

        $totalBalance = max(0.0, $totalSales - $totalPaid);
        $prevTotalBalance = max(0.0, $prevTotalSales - $prevTotalPaid);

        $totalOrders = (int) (clone $orderQuery)->count();
        $prevTotalOrders = (int) (clone $prevOrderQuery)->count();

        $averageTicket = $totalOrders > 0 ? ($totalSales / $totalOrders) : 0.0;
        $prevAverageTicket = $prevTotalOrders > 0 ? ($prevTotalSales / $prevTotalOrders) : 0.0;

        $activeCustomers = (int) (clone $orderQuery)->distinct('customer_id')->count('customer_id');
        $prevActiveCustomers = (int) (clone $prevOrderQuery)->distinct('customer_id')->count('customer_id');

        $unpaidOrdersCount = (int) (clone $orderQuery)->whereColumn('paid_amount', '<', 'total_amount')->count();
        $defaultRate = $totalOrders > 0 ? round(($unpaidOrdersCount / $totalOrders) * 100, 1) : 0.0;
        $prevUnpaidOrdersCount = (int) (clone $prevOrderQuery)->whereColumn('paid_amount', '<', 'total_amount')->count();
        $prevDefaultRate = $prevTotalOrders > 0 ? round(($prevUnpaidOrdersCount / $prevTotalOrders) * 100, 1) : 0.0;

        $calculateVariation = function (float|int $current, float|int $previous): ?float {
            if ($previous == 0) {
                return $current > 0 ? 100.0 : ($current == 0 ? 0.0 : -100.0);
            }

            return round((($current - $previous) / $previous) * 100, 1);
        };

        // Monthly Sales (Line Chart)
        $periodMonths = [];
        $monthCursor = $startDate->copy()->startOfMonth();
        $endMonth = $endDate->copy()->startOfMonth();

        $monthLabelsPt = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
            5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
        ];

        while ($monthCursor <= $endMonth) {
            $key = $monthCursor->format('Y-m');
            $mNum = (int) $monthCursor->format('n');
            $yrShort = $monthCursor->format('y');
            $label = ($monthLabelsPt[$mNum] ?? $monthCursor->format('M')).'/'.$yrShort;
            $periodMonths[$key] = [
                'key' => $key,
                'label' => $label,
                'total_sales' => 0.0,
                'total_paid' => 0.0,
            ];
            $monthCursor->addMonth();
        }

        $ordersInPeriod = (clone $orderQuery)
            ->select(['date', 'total_amount', 'paid_amount'])
            ->get();

        foreach ($ordersInPeriod as $order) {
            $monthKey = Carbon::parse($order->date)->format('Y-m');
            if (isset($periodMonths[$monthKey])) {
                $periodMonths[$monthKey]['total_sales'] += (float) $order->total_amount;
                $periodMonths[$monthKey]['total_paid'] += (float) $order->paid_amount;
            }
        }
        $monthlySales = array_values($periodMonths);

        // Heatmap (Daily Sales for entire filtered period)
        $dailySales = $ordersInPeriod
            ->groupBy(fn ($order) => Carbon::parse($order->date)->format('Y-m-d'))
            ->map(fn ($orders) => [
                'total' => (float) $orders->sum('total_amount'),
                'count' => $orders->count(),
            ]);

        $heatmapDays = [];
        $dayCursor = $startDate->copy();
        while ($dayCursor <= $endDate) {
            $dateStr = $dayCursor->format('Y-m-d');
            $dayData = $dailySales->get($dateStr);
            $heatmapDays[] = [
                'date' => $dateStr,
                'day_of_week' => (int) $dayCursor->format('w'), // 0 = Sunday, 1 = Monday, ...
                'total' => $dayData ? $dayData['total'] : 0.0,
                'count' => $dayData ? $dayData['count'] : 0,
            ];
            $dayCursor->addDay();
        }

        // Weekday Sales (Bar Chart)
        $weekdayMap = [
            1 => ['day' => 'Segunda-feira', 'short' => 'Seg', 'total' => 0.0, 'count' => 0],
            2 => ['day' => 'Terça-feira', 'short' => 'Ter', 'total' => 0.0, 'count' => 0],
            3 => ['day' => 'Quarta-feira', 'short' => 'Qua', 'total' => 0.0, 'count' => 0],
            4 => ['day' => 'Quinta-feira', 'short' => 'Qui', 'total' => 0.0, 'count' => 0],
            5 => ['day' => 'Sexta-feira', 'short' => 'Sex', 'total' => 0.0, 'count' => 0],
            6 => ['day' => 'Sábado', 'short' => 'Sáb', 'total' => 0.0, 'count' => 0],
            0 => ['day' => 'Domingo', 'short' => 'Dom', 'total' => 0.0, 'count' => 0],
        ];

        foreach ($ordersInPeriod as $order) {
            $dow = (int) Carbon::parse($order->date)->format('w');
            if (isset($weekdayMap[$dow])) {
                $weekdayMap[$dow]['total'] += (float) $order->total_amount;
                $weekdayMap[$dow]['count'] += 1;
            }
        }

        $weekdaySales = [
            $weekdayMap[1],
            $weekdayMap[2],
            $weekdayMap[3],
            $weekdayMap[4],
            $weekdayMap[5],
            $weekdayMap[6],
            $weekdayMap[0],
        ];

        // Top 5 Products
        $topProducts = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.date', '>=', $startDate)
            ->where('orders.date', '<=', $endDate)
            ->where('orders.status', '!=', OrderStatusEnum::CANCELED)
            ->whereNull('orders.deleted_at')
            ->whereNull('order_items.deleted_at')
            ->selectRaw('products.id, products.name, SUM(order_items.quantity) as total_quantity, SUM(order_items.total_amount) as total_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'quantity' => (float) $item->total_quantity,
                'revenue' => (float) $item->total_revenue,
            ]);

        // Top 5 Customers
        $topCustomers = Customer::query()
            ->join('orders', 'customers.id', '=', 'orders.customer_id')
            ->where('orders.date', '>=', $startDate)
            ->where('orders.date', '<=', $endDate)
            ->where('orders.status', '!=', OrderStatusEnum::CANCELED)
            ->whereNull('orders.deleted_at')
            ->whereNull('customers.deleted_at')
            ->selectRaw('customers.id, customers.name, SUM(orders.total_amount) as total_amount, COUNT(orders.id) as orders_count')
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get()
            ->map(fn ($cust) => [
                'id' => $cust->id,
                'name' => $cust->name,
                'total_amount' => (float) $cust->total_amount,
                'orders_count' => (int) $cust->orders_count,
            ]);

        // Payment Methods & Summary
        $paymentsQuery = Payment::query()
            ->where('status', PaymentStatusEnum::CONFIRMED)
            ->where('paid_at', '>=', $startDate)
            ->where('paid_at', '<=', $endDate);

        $paymentSummaryRaw = (clone $paymentsQuery)
            ->selectRaw('method, COUNT(id) as count, SUM(value) as total_value')
            ->groupBy('method')
            ->get();

        $totalPaymentsValue = (float) (clone $paymentsQuery)->sum('value');
        $totalPaymentsCount = (int) (clone $paymentsQuery)->count();

        $methodLabels = [
            'pix' => 'Pix',
            'cash' => 'Dinheiro',
            'credit_card' => 'Cartão de Crédito',
            'debit_card' => 'Cartão de Débito',
            'food_voucher' => 'Vale Refeição',
            'other' => 'Outro',
        ];

        $paymentMethods = [];
        foreach ($paymentSummaryRaw as $p) {
            $methodKey = is_object($p->method) ? $p->method->value : (string) $p->method;
            $val = (float) $p->total_value;
            $cnt = (int) $p->count;
            $paymentMethods[] = [
                'method' => $methodKey,
                'label' => $methodLabels[$methodKey] ?? ucfirst($methodKey),
                'count' => $cnt,
                'total_value' => $val,
                'percentage' => $totalPaymentsValue > 0 ? round(($val / $totalPaymentsValue) * 100, 1) : 0.0,
            ];
        }
        usort($paymentMethods, fn ($a, $b) => $b['total_value'] <=> $a['total_value']);

        return Inertia::render('Reports/GeneralReport', [
            'kpis' => [
                'total_sales' => [
                    'value' => $totalSales,
                    'prev_value' => $prevTotalSales,
                    'variation' => $calculateVariation($totalSales, $prevTotalSales),
                ],
                'total_paid' => [
                    'value' => $totalPaid,
                    'prev_value' => $prevTotalPaid,
                    'variation' => $calculateVariation($totalPaid, $prevTotalPaid),
                ],
                'total_balance' => [
                    'value' => $totalBalance,
                    'prev_value' => $prevTotalBalance,
                    'variation' => $calculateVariation($totalBalance, $prevTotalBalance),
                ],
                'total_orders' => [
                    'value' => $totalOrders,
                    'prev_value' => $prevTotalOrders,
                    'variation' => $calculateVariation($totalOrders, $prevTotalOrders),
                ],
                'average_ticket' => [
                    'value' => $averageTicket,
                    'prev_value' => $prevAverageTicket,
                    'variation' => $calculateVariation($averageTicket, $prevAverageTicket),
                ],
                'active_customers' => [
                    'value' => $activeCustomers,
                    'prev_value' => $prevActiveCustomers,
                    'variation' => $calculateVariation($activeCustomers, $prevActiveCustomers),
                ],
                'default_rate' => [
                    'value' => $defaultRate,
                    'prev_value' => $prevDefaultRate,
                    'variation' => $calculateVariation($defaultRate, $prevDefaultRate),
                ],
            ],
            'monthly_sales' => $monthlySales,
            'heatmap_days' => $heatmapDays,
            'payment_methods' => $paymentMethods,
            'total_payments_value' => $totalPaymentsValue,
            'total_payments_count' => $totalPaymentsCount,
            'weekday_sales' => $weekdaySales,
            'top_products' => $topProducts,
            'top_customers' => $topCustomers,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    public function salesByCustomer(Request $request): Response
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'customer' => ['nullable', 'string'],
        ]);

        $startDate = ($validated['start_date'] ?? null)
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $endDate = ($validated['end_date'] ?? null)
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : now()->endOfDay();

        $customerName = trim($validated['customer'] ?? '');

        $orderFilters = function ($query) use ($startDate, $endDate) {
            $query->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate)
                ->where('status', '!=', OrderStatusEnum::CANCELED);
        };

        $query = Customer::query()
            ->whereHas('orders', $orderFilters)
            ->withCount(['orders' => $orderFilters])
            ->withSum(['orders' => $orderFilters], 'total_amount')
            ->withSum(['orders' => $orderFilters], 'paid_amount')
            ->when($customerName, function ($query, $customerName) {
                $query->whereLike('name', "%{$customerName}%");
            });

        $totalsQuery = Order::query()
            ->where($orderFilters)
            ->when($customerName, fn ($q) => $q->whereHas('customer', fn ($q) => $q->whereLike('name', "%{$customerName}%")));

        $totalSales = $totalsQuery->sum('total_amount');
        $totalPaid = $totalsQuery->sum('paid_amount');

        return Inertia::render('Reports/SalesByCustomer', [
            'customers' => $query->orderBy('balance')->paginate(25)->withQueryString(),
            'total_sales' => $totalSales,
            'total_paid' => $totalPaid,
            'total_balance' => $totalSales - $totalPaid,
            'filters' => $request->only(['start_date', 'end_date', 'customer']),
        ]);
    }
}
