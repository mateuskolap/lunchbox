<?php

namespace App\Data\Payments;

use App\Enums\PaymentMethodEnum;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class CreatePaymentData extends Data
{
    public function __construct(
        public PaymentMethodEnum $method,
        public float             $value,
        public Carbon            $paid_at
    )
    {
    }
}
