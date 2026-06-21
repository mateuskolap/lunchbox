<?php

namespace App\Data\Orders;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class CreateOrderWithItemsData extends Data
{
    /**
     * @param int $customer_id
     * @param Collection<int, RawOrderItemData> $order_items
     * @param string|null $observation
     * @param string|null $date
     */
    public function __construct(
        public int        $customer_id,
        public Collection $order_items,
        public ?string    $observation = null,
        public ?string    $date = null,
    )
    {
    }
}
