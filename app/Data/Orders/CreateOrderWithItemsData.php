<?php

namespace App\Data\Orders;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class CreateOrderWithItemsData extends Data
{
    public function __construct(
        public int        $customer_id,
        /** @var Collection<int, RawOrderItemData> */
        public Collection $order_items,
        public ?string    $observation = null,
        public ?string    $date = null,
    )
    {
    }
}
