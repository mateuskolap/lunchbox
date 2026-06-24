<?php

namespace App\Actions\Payments;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

class SettleOrdersFromWalletAction
{
    /**
     * @param Customer $customer
     * @return void
     * @throws Throwable
     */
    public function execute(Customer $customer): void
    {
        DB::transaction(function () use ($customer) {
            $customer->refresh();
            $walletBalance = $customer->wallet_balance;

            if ($walletBalance <= 0) {
                return;
            }

            $unpaidOrders = $customer->orders()
                ->unpaid()
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            $remainingBalance = $walletBalance;

            $unpaidOrders->each(function ($order) use ($customer, &$remainingBalance) {
                if ($remainingBalance <= 0) {
                    return;
                }

                $pendingAmount = $order->total_amount - $order->paid_amount;
                $allocatedAmount = min($remainingBalance, $pendingAmount);

                $customer->transactions()->create([
                    'amount' => -$allocatedAmount,
                    'description' => "Baixa no pedido #{$order->id}",
                    'transactionable_id' => $order->id,
                    'transactionable_type' => Order::class,
                ]);

                $remainingBalance -= $allocatedAmount;
            });
        });
    }
}
