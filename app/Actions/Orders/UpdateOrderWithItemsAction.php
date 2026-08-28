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
        private SettleOrdersFromWalletAction $settleOrdersFromWallet,
        private CreateOrderTransactionAction $createOrderTransaction,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(Order $order, UpdateOrderWithItemsData $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            $previousTotal = $order->total_amount;

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
                        'product_id' => $item->product_id,
                    ],
                    [
                        'unit_price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'total_amount' => $item->total_amount,
                    ]
                );
            });

            $order->recalculateTotals();

            $this->handlePaymentReconciliation($order, $previousTotal);

            return $order;
        });
    }

    /**
     * @throws Throwable
     */
    private function handlePaymentReconciliation(Order $order, float|string $previousTotal): void
    {
        $newTotal = (float) $order->total_amount;
        $previousTotal = (float) $previousTotal;
        $customer = $order->customer;

        if ($newTotal > $previousTotal) {
            $difference = $newTotal - $previousTotal;
            $balanceBefore = (float) $customer->balance;

            $this->createOrderTransaction->execute($order, -$difference, "Ajuste de valor por aumento no pedido #{$order->id}");

            if ($balanceBefore > 0) {
                $additionalPaid = min($difference, $balanceBefore);
                $order->increment('paid_amount', $additionalPaid);
            }
        } elseif ($newTotal < $previousTotal) {
            $difference = $previousTotal - $newTotal;

            $this->createOrderTransaction->execute($order, $difference, "Ajuste de valor por redução no pedido #{$order->id}");

            $order->update([
                'paid_amount' => min((float) $order->paid_amount, $newTotal),
            ]);
        }

        $this->settleOrdersFromWallet->execute($customer);
    }
}
