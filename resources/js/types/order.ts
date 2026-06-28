import type { Customer } from './customer';
import type { Product } from './product';
import type { Transaction } from './transaction';

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
    paid_amount: string | number;
    status: OrderStatus;
    observation?: string | null;
    date: string;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;
    customer?: Customer;
    items?: OrderItem[];
    transactions?: Transaction[];
};
