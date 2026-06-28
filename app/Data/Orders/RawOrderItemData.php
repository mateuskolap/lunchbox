<?php

namespace App\Data\Orders;

use Spatie\LaravelData\Data;

class RawOrderItemData extends Data
{
    public function __construct(
        public int   $product_id,
        public int   $quantity,
        public ?float $unit_price = null,
    )
    {
    }
}
