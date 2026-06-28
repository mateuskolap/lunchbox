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
                $this->createOrderTransaction->execute($order, -$order->total_amount, "Valor referente ao pedido #{$order->id}");
            }

            $order->reopen();

            $this->settleOrdersFromWallet->execute($order->customer);
        });
    }
}
