<?php

namespace App\Actions\Orders;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class SettleOrdersFromWalletAction
{
    public function __construct(
        private CreateOrderTransactionAction $createOrderTransaction,
    )
    {
    }

    /**
     * @param Customer $customer
     * @return void
     * @throws Throwable
     */
    public function execute(Customer $customer): void
    {
        DB::transaction(function () use ($customer) {
            $customer->refresh();
            $customerBalance = $customer->balance;

            if ($customerBalance <= 0) {
                return;
            }

            $unpaidOrders = $customer->orders()
                ->unpaid()
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            $remainingBalance = $customerBalance;

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
