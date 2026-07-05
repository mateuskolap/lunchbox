<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SalesReportController extends Controller
{
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

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

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
                $query->where('name', 'ilike', "%{$customerName}%");
            });

        $totalsQuery = Order::query()
            ->where($orderFilters)
            ->when($customerName, fn($q) =>
                $q->whereHas('customer', fn($q) => $q->where('name', 'ilike', "%{$customerName}%"))
            );

        $totalSales = $totalsQuery->sum('total_amount');
        $totalPaid = $totalsQuery->sum('paid_amount');

        $customers = $query->orderByDesc('orders_sum_total_amount')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Reports/SalesByCustomer', [
            'customers' => $customers,
            'total_sales' => $totalSales,
            'total_paid' => $totalPaid,
            'total_balance' => $totalSales - $totalPaid,
            'filters' => $request->only(['start_date', 'end_date', 'customer']),
        ]);
    }
}
