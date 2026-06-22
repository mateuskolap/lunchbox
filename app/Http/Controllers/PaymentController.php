<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PaymentController extends Controller
{
    public function customerIndex(Customer $customer): Response
    {
        return Inertia::render('Payments/CustomerIndex', [
            'payments' => $customer->payments()
                ->paginate(25),
            'customer' => $customer,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'method' => ['required', new Enum(PaymentMethodEnum::class)],
            'value' => ['required', 'numeric:2'],
            'paid_at' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($customer, $validated) {
            $customer->payments()->create([
                ...$validated,
                'status' => PaymentStatusEnum::CONFIRMED,
                'paid_at' => $validated['paid_at'] ?? now(),
            ]);

            $orders = Order::fromSub(
                $customer->orders()
                    ->select(['id', 'total_amount', 'paid_amount', 'date'])
                    ->selectRaw('SUM(total_amount - paid_amount) OVER (ORDER BY date ASC, id ASC) AS cumulative_debt')
                    ->unpaid(),
                'orders_with_cumulative'
            )
                ->whereRaw(DB::raw('cumulative_debt - (total_amount - paid_amount) < ?'), [$validated['value']])
                ->orderBy('date')
                ->orderBy('id')
                ->get();
        });
    }
}
