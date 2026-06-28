<?php

namespace App\Actions\Payments;

use App\Actions\Orders\SettleOrdersFromWalletAction;
use App\Data\Payments\CreatePaymentData;
use App\Enums\PaymentStatusEnum;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CreatePaymentAction
{
    public function __construct(
        private SettleOrdersFromWalletAction   $settleOrdersFromWallet,
        private CreatePaymentTransactionAction $createPaymentTransaction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(Customer $customer, CreatePaymentData $data): Payment
    {
        return DB::transaction(function () use ($customer, $data) {
            $payment = $customer->payments()->create([
                'method' => $data->method,
                'status' => PaymentStatusEnum::CONFIRMED,
                'value' => $data->value,
                'paid_at' => $data->paid_at ?? now(),
            ]);

            $this->createPaymentTransaction->execute($payment, "Valor referente ao pagamento #{$payment->id}");
            $this->settleOrdersFromWallet->execute($customer);

            return $payment;
        });
    }
}
