<?php

namespace App\Actions\Orders;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class RevertOrdersSettlementAction
{
    public function __construct(
        private CreateOrderTransactionAction $createOrderTransaction,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(Customer $customer, Payment $payment): void
    {
        DB::transaction(function () use ($customer, $payment) {
            $customer->refresh();

            $remainingNeeded = (float) $payment->value;

            if ($remainingNeeded <= 0) {
                return;
            }

            // Query fully paid and partially paid orders using scopes.
            $paidOrders = $customer->orders()
                ->paid()
                ->orWhere(function ($query) use ($customer) {
                    $query->where('customer_id', $customer->id)
                        ->partiallyPaid();
                })
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get();

            $paidOrders->each(function ($order) use ($customer, &$remainingNeeded) {
                if ($remainingNeeded <= 0) {
                    return false;
                }

                $revertAmount = min($remainingNeeded, (float) $order->paid_amount);

                if ($revertAmount > 0) {
                    $order->update([
                        'paid_amount' => $order->paid_amount - $revertAmount,
                    ]);

                    $order->transactions()->create([
                        'customer_id' => $customer->id,
                        'amount' => 0.00,
                        'description' => "Cancelamento de pagamento no pedido #{$order->id}",
                        'customer_balance' => $customer->balance,
                    ]);

                    $remainingNeeded -= $revertAmount;
                }

                return true;
            });
        });
    }
}
