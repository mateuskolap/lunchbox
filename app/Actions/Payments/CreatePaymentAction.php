<?php

namespace App\Actions\Payments;

use App\Data\Payments\CreatePaymentData;
use App\Enums\PaymentStatusEnum;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CreatePaymentAction
{
    public function __construct(
        private SettleOrdersFromWalletAction $settleOrdersFromWallet,
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
                'paid_at' => $data->paid_at,
            ]);

            $customer->transactions()->create([
                'amount' => $payment->value,
                'description' => "Crédito do pagamento #{$payment->id}",
                'transactionable_id' => $payment->id,
                'transactionable_type' => Payment::class,
            ]);

//            $this->settleOrdersFromWallet->execute($customer);

            return $payment;
        });
    }
}
