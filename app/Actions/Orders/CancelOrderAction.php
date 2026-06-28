<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CancelOrderAction
{
    public function __construct(
        private SettleOrdersFromWalletAction $settleOrdersFromWallet,
        private CreateOrderTransactionAction $createOrderTransaction,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(Order $order): void
    {
        if ($order->status === OrderStatusEnum::CANCELED) {
            return;
        }

        DB::transaction(function () use ($order) {
            $totalAmount = (float)$order->total_amount;

            $order->cancel();
            $order->update([
                'paid_amount' => 0.00,
            ]);

            $this->createOrderTransaction->execute($order, $totalAmount, "Estorno por cancelamento do pedido #{$order->id}");
            $this->settleOrdersFromWallet->execute($order->customer);
        });
    }
}
