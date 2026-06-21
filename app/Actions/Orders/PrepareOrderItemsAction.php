<?php

namespace App\Actions\Orders;

use App\Data\Orders\RawOrderItemData;
use App\Data\Orders\OrderItemData;
use App\Models\Product;
use Illuminate\Support\Collection;

class PrepareOrderItemsAction
{
    /**
     * @param Collection<int, RawOrderItemData> $items
     * @return Collection<int, OrderItemData>
     */
    public function execute(Collection $items): Collection
    {
        $products = Product::whereIn('id', $items->pluck('product_id'))
            ->get()
            ->keyBy('id');

        return $items->map(function (RawOrderItemData $item) use ($products) {
            $product = $products->get($item->product_id);
            $price = $product ? (float)$product->price : 0.00;

            return new OrderItemData(
                product_id: $item->product_id,
                unit_price: $price,
                quantity: $item->quantity,
                total_amount: $price * $item->quantity
            );
        });
    }
}
