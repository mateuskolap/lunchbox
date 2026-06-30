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

        $query = Customer::query()
            ->withCount(['orders' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])
                    ->where('status', '!=', OrderStatusEnum::CANCELED);
            }])
            ->withSum(['orders' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])
                    ->where('status', '!=', OrderStatusEnum::CANCELED);
            }], 'total_amount')
            ->withSum(['orders' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])
                    ->where('status', '!=', OrderStatusEnum::CANCELED);
            }], 'paid_amount')
            ->when($customerName, function ($query, $customerName) {
                $query->whereRaw('LOWER(name) like ?', ['%'.mb_strtolower($customerName).'%']);
            });

        $totalsQuery = Order::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', '!=', OrderStatusEnum::CANCELED)
            ->when($customerName, function ($query, $customerName) {
                $query->whereHas('customer', function ($q) use ($customerName) {
                    $q->whereRaw('LOWER(name) like ?', ['%'.mb_strtolower($customerName).'%']);
                });
            });

        $totalSales = $totalsQuery->sum('total_amount');
        $totalPaid = $totalsQuery->sum('paid_amount');

        $customers = $query->orderByDesc('orders_sum_total_amount')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Reports/SalesByCustomer', [
            'customers' => $customers,
            'total_sales' => (float) $totalSales,
            'total_paid' => (float) $totalPaid,
            'total_balance' => (float) ($totalSales - $totalPaid),
            'filters' => [
                'start_date' => $request->input('start_date', $startDate->toDateString()),
                'end_date' => $request->input('end_date', $endDate->toDateString()),
                'customer' => $request->input('customer'),
            ],
        ]);
    }
}
