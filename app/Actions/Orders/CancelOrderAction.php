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
    ) {
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
                $order->customer->transactions()->create([
                    'amount' => $paidAmount,
                    'description' => "Estorno por cancelamento do pedido #{$order->id}",
                    'transactionable_id' => $order->id,
                    'transactionable_type' => Order::class,
                ]);

//                $this->settleOrdersFromWallet->execute($order->customer);
            }
        });
    }
}
