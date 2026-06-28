<?php

namespace App\Actions\Payments;

use App\Models\Payment;

class CreatePaymentTransactionAction
{
    public function execute(Payment $payment, string $description, bool $debit = false): void
    {
        $customer = $payment->customer;

        $customer->update([
            'balance' => $customer->balance + ($debit ? -$payment->value : $payment->value),
        ]);

        $payment->transactions()->create([
            'customer_id' => $customer->id,
            'amount' => $debit ? -$payment->value : $payment->value,
            'description' => $description,
            'customer_balance' => $customer->balance,
        ]);
    }
}
