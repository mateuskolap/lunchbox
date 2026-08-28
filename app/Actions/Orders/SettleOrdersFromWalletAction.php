<?php

namespace App\Actions\Orders;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class SettleOrdersFromWalletAction
{
    /**
     * @throws Throwable
     */
    public function execute(Customer $customer): void
    {
        DB::transaction(function () use ($customer) {
            $customer->refresh();

            $unpaidOrders = $customer->orders()
                ->unpaid()
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            $totalPending = $unpaidOrders->sum(fn ($order) => $order->total_amount - $order->paid_amount);
            $remainingBalance = $customer->balance + $totalPending;

            if ($remainingBalance <= 0) {
                return;
            }

            $unpaidOrders->each(function ($order) use ($customer, &$remainingBalance) {
                if ($remainingBalance <= 0) {
                    return false;
                }

                $pendingAmount = $order->total_amount - $order->paid_amount;
                $allocatedAmount = min($remainingBalance, $pendingAmount);

                $order->update([
                    'paid_amount' => $order->paid_amount + $allocatedAmount,
                ]);

                $order->transactions()->create([
                    'customer_id' => $customer->id,
                    'amount' => 0.00,
                    'description' => "Baixa no pedido #{$order->id}",
                    'customer_balance' => $customer->balance,
                ]);

                $remainingBalance -= $allocatedAmount;

                return true;
            });
        });
    }
}
