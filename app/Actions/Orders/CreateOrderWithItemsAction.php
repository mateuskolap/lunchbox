<?php

namespace App\Actions\Orders;

use App\Actions\Payments\SettleOrdersFromWalletAction;
use App\Data\Orders\CreateOrderWithItemsData;
use App\Enums\OrderStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CreateOrderWithItemsAction
{
    public function __construct(
        private PrepareOrderItemsAction      $prepareOrderItems,
        private SettleOrdersFromWalletAction $settleOrdersFromWallet,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(CreateOrderWithItemsData $data): Order
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::findOrFail($data->customer_id);

            $order = Order::create([
                'customer_id' => $customer->id,
                'status' => OrderStatusEnum::PENDING,
                'observation' => $data->observation,
                'date' => $data->date ?? now(),
            ]);

            $orderItems = $this->prepareOrderItems->execute($data->order_items);
            $order->items()->createMany($orderItems->toArray());
            $order->recalculateTotals();
            $this->settleOrdersFromWallet->execute($customer);

            return $order;
        });
    }
}
