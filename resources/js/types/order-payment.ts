import type { Order } from './order';
import type { Payment } from './payment';

export type OrderPayment = {
    id: number;
    order_id: number;
    payment_id: number;
    allocated_amount: string | number;
    created_at: string;
    updated_at: string;
    order?: Order;
    payment?: Payment;
};
