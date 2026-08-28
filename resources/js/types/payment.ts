import type { Customer } from './customer';

export type PaymentMethod =
    'cash' | 'pix' | 'credit_card' | 'debit_card' | 'food_voucher' | 'other';
export type PaymentStatus = 'pending' | 'confirmed' | 'canceled';

export type Payment = {
    id: number;
    customer_id: number;
    method: PaymentMethod;
    status: PaymentStatus;
    value: string | number;
    paid_at?: string | null;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;
    customer?: Customer;
};
