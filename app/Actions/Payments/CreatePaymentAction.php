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
        private SettleOrdersForCustomerByPaymentAction $settleOrdersByPayment,
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

            $this->settleOrdersByPayment->execute($customer, $payment);

            return $payment;
        });
    }
}
