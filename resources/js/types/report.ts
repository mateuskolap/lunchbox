export interface ReportsDashboardFilters {
    start_date: string;
    end_date: string;
}

export interface TopCustomerItem {
    id: number;
    name: string;
    total_sales: number;
    total_paid: number;
    orders_count: number;
}

export interface TopProductItem {
    id: number;
    name: string;
    total_quantity: number;
    total_revenue: number;
}

export interface PaymentByMethodItem {
    method: string;
    total: number;
    count: number;
}

export interface SalesReportFilters {
    start_date: string;
    end_date: string;
    customer: string;
}

export interface CustomerReportRow {
    id: number;
    name: string;
    phone?: string;
    balance: string | number;
    orders_count: number;
    orders_sum_total_amount: string | number | null;
    orders_sum_paid_amount: string | number | null;
}
