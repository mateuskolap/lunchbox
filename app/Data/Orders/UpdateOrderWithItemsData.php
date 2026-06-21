<?php

namespace App\Data\Orders;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class UpdateOrderWithItemsData extends Data
{
    /**
     * @param Carbon $date
     * @param Collection<int, RawOrderItemData> $order_items
     * @param string|null $observation
     */
    public function __construct(
        public Carbon     $date,
        public Collection $order_items,
        public ?string    $observation = null,
    )
    {
    }
}
