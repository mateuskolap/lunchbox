<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case CASH = 'cash';
    case PIX = 'pix';
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case FOOD_VOUCHER = 'food_voucher';
    case OTHER = 'other';
}
