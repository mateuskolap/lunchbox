<?php

namespace App\Actions\Orders;

use App\Models\Order;

readonly class CreateOrderTransactionAction
{
    public function execute(Order $order, float|string $amount, string $description): void
    {
        $customer = $order->customer;

        $customer->update([
            'balance' => $customer->balance + $amount,
        ]);

        $order->transactions()->create([
            'customer_id' => $customer->id,
            'amount' => $amount,
            'description' => $description,
            'customer_balance' => $customer->balance,
        ]);
    }
}
