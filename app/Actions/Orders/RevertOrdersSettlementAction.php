<?php

namespace App\Actions\Orders;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class RevertOrdersSettlementAction
{
    public function __construct(
        private CreateOrderTransactionAction $createOrderTransaction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(Customer $customer): void
    {
        DB::transaction(function () use ($customer) {
            $customer->refresh();
            $customerBalance = $customer->balance;

            if ($customerBalance >= 0) {
                return;
            }

            $neededAmount = -$customerBalance;

            $paidOrders = $customer->orders()
                ->where('paid_amount', '>', 0)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get();

            $remainingNeeded = $neededAmount;

            $paidOrders->each(function ($order) use (&$remainingNeeded) {
                if ($remainingNeeded <= 0) {
                    return false;
                }

                $revertAmount = min($remainingNeeded, (float)$order->paid_amount);

                if ($revertAmount > 0) {
                    $this->createOrderTransaction->execute(
                        $order,
                        $revertAmount,
                        "Estorno de baixa no pedido #{$order->id}"
                    );

                    $remainingNeeded -= $revertAmount;
                }

                return true;
            });
        });
    }
}
