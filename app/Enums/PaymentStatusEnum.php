<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case CONFIRMED = 'confirmed';
    case CANCELED = 'canceled';
}
