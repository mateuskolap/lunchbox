<?php

namespace App\Actions\Orders;

use App\Actions\Payments\SettleOrdersFromWalletAction;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class ReopenOrderAction
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
        DB::transaction(function () use ($order) {
            $order->reopen();

            $this->settleOrdersFromWallet->execute($order->customer);
        });
    }
}
