<?php

namespace App\Actions\Orders;

use App\Data\Orders\CreateOrderWithItemsData;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CreateOrderWithItemsAction
{
    public function __construct(
        private PrepareOrderItemsAction $prepareOrderItems,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(CreateOrderWithItemsData $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'customer_id' => $data->customer_id,
                'status' => OrderStatusEnum::PENDING,
                'observation' => $data->observation,
                'date' => $data->date ?? now(),
            ]);

            $orderItems = $this->prepareOrderItems->execute($data->order_items);
            $order->items()->createMany($orderItems->toArray());
            $order->recalculateTotals();

            return $order;
        });
    }
}
