<?php

namespace App\Data\Orders;

use Spatie\LaravelData\Data;

class OrderItemData extends Data
{
    public function __construct(
        public int $product_id,
        public float $unit_price,
        public int $quantity,
        public float $total_amount,
    ) {}
}
