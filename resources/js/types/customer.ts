import type { Order } from './order';
import type { Payment } from './payment';
import type { WalletTransaction } from './wallet-transaction';

export type Customer = {
    id: number;
    name: string;
    phone?: string;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;
    orders?: Order[];
    payments?: Payment[];
    transactions?: WalletTransaction[];
};
