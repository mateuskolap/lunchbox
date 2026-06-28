<?php

namespace App\Actions\Payments;

use App\Actions\Orders\RevertOrdersSettlementAction;
use App\Enums\PaymentStatusEnum;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CancelPaymentAction
{
    public function __construct(
        private CreatePaymentTransactionAction $createPaymentTransaction,
        private RevertOrdersSettlementAction   $revertOrdersSettlement,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(Payment $payment): void
    {
        if ($payment->status === PaymentStatusEnum::CANCELED) {
            return;
        }

        DB::transaction(function () use ($payment) {
            $payment->cancel();

            $this->createPaymentTransaction->execute($payment, "Cancelamento do pagamento #{$payment->id}", true);
            $this->revertOrdersSettlement->execute($payment->customer);
        });
    }
}
