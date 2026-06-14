<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case CONCLUDED = 'concluded';
    case CANCELED = 'canceled';
}
