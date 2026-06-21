<?php

namespace App\Actions\Orders;

use App\Data\Orders\OrderItemData;
use App\Data\Orders\UpdateOrderWithItemsData;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UpdateOrderWithItemsAction
{
    public function __construct(
        private PrepareOrderItemsAction $prepareOrderItems,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(Order $order, UpdateOrderWithItemsData $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            $order->update([
                'date' => $data->date,
                'observation' => $data->observation,
            ]);

            $order->items()->whereNotIn(
                'product_id',
                $data->order_items->pluck('product_id')->all()
            )->delete();

            $orderItems = $this->prepareOrderItems->execute($data->order_items);

            $orderItems->each(function (OrderItemData $item) use ($order) {
                $order->items()->updateOrCreate(
                    [
                        'product_id' => $item->product_id
                    ],
                    [
                        'unit_price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'total_amount' => $item->total_amount,
                    ]
                );
            });

            $order->recalculateTotals();

            return $order;
        });
    }
}
