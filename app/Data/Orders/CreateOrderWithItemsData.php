<?php

namespace App\Data\Orders;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class CreateOrderWithItemsData extends Data
{
    /**
     * @param  Collection<int, RawOrderItemData>  $order_items
     */
    public function __construct(
        public int $customer_id,
        public Collection $order_items,
        public ?string $observation = null,
        public ?string $date = null,
    ) {}
}
