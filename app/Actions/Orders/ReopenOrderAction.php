<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class ReopenOrderAction
{
    public function __construct(
        private SettleOrdersFromWalletAction $settleOrdersFromWallet,
        private CreateOrderTransactionAction $createOrderTransaction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(Order $order): void
    {
        if ($order->status === OrderStatusEnum::PENDING) {
            return;
        }

        DB::transaction(function () use ($order) {
            if ($order->status === OrderStatusEnum::CANCELED) {
                $customer = $order->customer;
                $balanceBefore = (float)$customer->balance;

                $this->createOrderTransaction->execute($order, -$order->total_amount, "Valor referente ao pedido #{$order->id}");

                $paidAmount = max(0.00, min((float)$order->total_amount, $balanceBefore));
                $order->update([
                    'paid_amount' => $paidAmount,
                ]);
            }

            $order->reopen();

            $this->settleOrdersFromWallet->execute($order->customer);
        });
    }
}
