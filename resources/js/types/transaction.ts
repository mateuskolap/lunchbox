import type { Customer } from './customer';

export type Transaction = {
    id: number;
    customer_id: number;
    amount: string | number;
    description: string;
    transactionable_type: string;
    transactionable_id: number;
    created_at: string;
    updated_at: string;
    customer?: Customer;
    transactionable?: any;
};
