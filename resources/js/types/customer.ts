import type { Order } from './order';
import type { Payment } from './payment';
import type { Transaction } from './transaction';

export type Customer = {
    id: number;
    name: string;
    phone?: string;
    balance: string | number;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;
    orders?: Order[];
    payments?: Payment[];
    transactions?: Transaction[];
};
