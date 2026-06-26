<?php

namespace App\Actions\Orders;

use App\Actions\Payments\SettleOrdersFromWalletAction;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CancelOrderAction
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
        if ($order->status === OrderStatusEnum::CANCELED) {
            return;
        }

        DB::transaction(function () use ($order) {
            $paidAmount = $order->paid_amount;

            $order->cancel();

            if ($paidAmount > 0) {
                $this->createOrderTransaction->execute($order, $paidAmount, "Estorno por cancelamento do pedido #{$order->id}");
                $this->settleOrdersFromWallet->execute($order->customer);
            }
        });
    }
}
