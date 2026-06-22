<?php

namespace App\Actions\Payments;

use App\Models\Customer;
use App\Models\OrderPayment;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class SettleOrdersForCustomerByPaymentAction

{
    /**
     * @param Customer $customer
     * @param Payment $payment
     * @return Collection<int, OrderPayment>
     * @throws Throwable
     */
    public function execute(Customer $customer, Payment $payment): Collection
    {
        return DB::transaction(function () use ($customer, $payment) {
            $unpaidOrders = $customer->orders()
                ->unpaid()
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            $remainingValue = $payment->value;
            $orderPayments = collect();

            while ($remainingValue > 0 && !$unpaidOrders->isEmpty()) {
                $order = $unpaidOrders->shift();

                $pendingAmount = $order->total_amount - $order->paid_amount;
                $paidAmount = min($remainingValue, $pendingAmount);

                $order->update([
                    'paid_amount' => $order->paid_amount + $paidAmount,
                ]);

                $orderPayment = $order->payments()->create([
                    'payment_id' => $payment->id,
                    'allocated_amount' => $paidAmount,
                ]);

                $remainingValue -= $paidAmount;

                $orderPayments->push($orderPayment);
            }

            return $orderPayments;
        });
    }
}
