<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { useFilters } from '@/composables/useFilters';
import { generalReport } from '@/routes/reports';
import GeneralReportFilters from './components/GeneralReportFilters.vue';
import GeneralSummaryCards from './components/GeneralSummaryCards.vue';
import MonthlySalesChart from './components/MonthlySalesChart.vue';
import PaymentMethodChart from './components/PaymentMethodChart.vue';
import PaymentSummaryTable from './components/PaymentSummaryTable.vue';
import SalesHeatmap from './components/SalesHeatmap.vue';
import TopCustomersChart from './components/TopCustomersChart.vue';
import TopProductsChart from './components/TopProductsChart.vue';
import WeekdaySalesChart from './components/WeekdaySalesChart.vue';
import type { GeneralReportFilterValues, GeneralReportProps } from './types';

const props = defineProps<GeneralReportProps>();

const { filters, clearFilters, hasActiveFilters } =
    useFilters<GeneralReportFilterValues>(
        () => generalReport.url(),
        {
            start_date: props.filters.start_date || '',
            end_date: props.filters.end_date || '',
        },
        {
            transform: (f) => ({
                start_date: f.start_date || undefined,
                end_date: f.end_date || undefined,
            }),
        },
    );

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Relatórios',
                href: '#',
            },
            {
                title: 'Relatório Geral',
                href: generalReport.url(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Relatório Geral de Vendas" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-hidden p-4 sm:p-6">
        <div class="flex items-center justify-between">
            <Heading
                title="Relatório Geral de Vendas"
                description="Visão consolidada do faturamento, recebimentos, métricas e tendências do negócio."
            />
        </div>

        <!-- Filters (with quick presets) -->
        <GeneralReportFilters
            v-model="filters"
            :has-active-filters="hasActiveFilters"
            @clear="clearFilters"
        />

        <!-- Top KPI Cards with Variation Badges -->
        <GeneralSummaryCards :kpis="kpis" />

        <!-- Row 1: Line Chart (Monthly Sales) & Doughnut (Payment Methods) -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <MonthlySalesChart :data="monthly_sales" />
            </div>
            <div class="lg:col-span-1">
                <PaymentMethodChart
                    :data="payment_methods"
                    :total-value="total_payments_value"
                />
            </div>
        </div>

        <!-- Row 2: Heatmap (Full Width) -->
        <SalesHeatmap :days="heatmap_days" />

        <!-- Row 3: Weekday Sales & Top Products -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <WeekdaySalesChart :data="weekday_sales" />
            <TopProductsChart :data="top_products" />
        </div>

        <!-- Row 4: Top Customers & Payment Summary Table -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <TopCustomersChart :data="top_customers" />
            <PaymentSummaryTable
                :data="payment_methods"
                :total-value="total_payments_value"
                :total-count="total_payments_count"
            />
        </div>
    </div>
</template>
