export interface MetricWithVariation {
    value: number;
    prev_value: number;
    variation: number | null;
}

export interface GeneralReportKPIs {
    total_sales: MetricWithVariation;
    total_paid: MetricWithVariation;
    total_balance: MetricWithVariation;
    total_orders: MetricWithVariation;
    average_ticket: MetricWithVariation;
    active_customers: MetricWithVariation;
    default_rate: MetricWithVariation;
}

export interface MonthlySalesItem {
    key: string;
    label: string;
    total_sales: number;
    total_paid: number;
}

export interface HeatmapDayItem {
    date: string;
    day_of_week: number;
    total: number;
    count: number;
}

export interface PaymentMethodItem {
    method: string;
    label: string;
    count: number;
    total_value: number;
    percentage: number;
}

export interface WeekdaySalesItem {
    day: string;
    short: string;
    total: number;
    count: number;
}

export interface TopProductItem {
    id: number;
    name: string;
    quantity: number;
    revenue: number;
}

export interface TopCustomerItem {
    id: number;
    name: string;
    total_amount: number;
    orders_count: number;
}

export interface GeneralReportFilterValues {
    start_date: string;
    end_date: string;
}

export interface GeneralReportProps {
    kpis: GeneralReportKPIs;
    monthly_sales: MonthlySalesItem[];
    heatmap_days: HeatmapDayItem[];
    payment_methods: PaymentMethodItem[];
    total_payments_value: number;
    total_payments_count: number;
    weekday_sales: WeekdaySalesItem[];
    top_products: TopProductItem[];
    top_customers: TopCustomerItem[];
    filters: GeneralReportFilterValues;
}
