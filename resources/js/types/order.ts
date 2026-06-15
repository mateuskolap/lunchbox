import type { Customer } from './customer';
import type { Product } from './product';

export type OrderStatus = 'pending' | 'concluded' | 'canceled';

export type OrderItem = {
    id: number;
    order_id: number;
    product_id: number;
    unit_price: string | number;
    quantity: number;
    total_amount: string | number;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;
    product?: Product;
};

export type Order = {
    id: number;
    customer_id: number;
    total_amount: string | number;
    total_items_amount?: string | number;
    status: OrderStatus;
    observation?: string | null;
    date: string;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;
    customer?: Customer;
    items?: OrderItem[];
};
