<?php

namespace App\Data\Orders;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class UpdateOrderWithItemsData extends Data
{
    /**
     * @param  Collection<int, RawOrderItemData>  $order_items
     */
    public function __construct(
        public Carbon $date,
        public Collection $order_items,
        public ?string $observation = null,
    ) {}
}
