<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { useFilters } from '@/composables/useFilters';
import { dashboard } from '@/routes/reports';
import type {
    PaymentByMethodItem,
    ReportsDashboardFilters,
    TopCustomerItem,
} from '@/types';
import DailyHeatmaps from './components/DailyHeatmaps.vue';
import DashboardFilters from './components/DashboardFilters.vue';
import DashboardSummaryCards from './components/DashboardSummaryCards.vue';
import MonthlyRevenueChart from './components/MonthlyRevenueChart.vue';
import PaymentMethodChart from './components/PaymentMethodChart.vue';
import TopCustomersTable from './components/TopCustomersTable.vue';

const props = defineProps<{
    total_sales: number;
    total_paid: number;
    total_balance: number;
    average_ticket: number;
    total_orders: number;
    available_years: number[];
    monthly_revenue: Record<string, number>;
    monthly_payments: Record<string, number>;
    daily_revenue: Record<string, number>;
    daily_payments: Record<string, number>;
    payments_by_method: PaymentByMethodItem[];
    top_customers: TopCustomerItem[];
    filters: ReportsDashboardFilters;
}>();

const { filters, clearFilters, hasActiveFilters } =
    useFilters<ReportsDashboardFilters>(
        () => dashboard.url(),
        {
            start_date: props.filters.start_date || '',
            end_date: props.filters.end_date || '',
        },
        {
            immediateFields: ['start_date', 'end_date'],
            transform: (f) => ({
                start_date: f.start_date || undefined,
                end_date: f.end_date || undefined,
            }),
        },
    );

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Relatórios', href: '#' },
            { title: 'Dashboard', href: dashboard.url() },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard de Relatórios" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-hidden p-4 sm:p-6">
        <div class="flex items-center justify-between">
            <Heading
                title="Dashboard de Relatórios"
                description="Visão geral do desempenho financeiro com gráficos e indicadores."
            />
        </div>

        <DashboardFilters
            v-model="filters"
            :years="available_years"
            :has-active-filters="hasActiveFilters"
            @clear="clearFilters"
        />

        <DashboardSummaryCards
            :total-sales="total_sales"
            :total-paid="total_paid"
            :total-balance="total_balance"
            :average-ticket="average_ticket"
            :total-orders="total_orders"
        />

        <MonthlyRevenueChart
            :revenue="monthly_revenue"
            :payments="monthly_payments"
        />

        <DailyHeatmaps
            :daily-revenue="daily_revenue"
            :daily-payments="daily_payments"
        />

        <PaymentMethodChart :data="payments_by_method" />

        <div>
            <TopCustomersTable :customers="top_customers" />
        </div>
    </div>
</template>
