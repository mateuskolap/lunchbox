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

                $this->createOrderTransaction->execute($order, -$allocatedAmount, "Baixa no pedido #{$order->id}", $customer);

                $remainingBalance -= $allocatedAmount;

                return true;
            });
        });
    }
}
