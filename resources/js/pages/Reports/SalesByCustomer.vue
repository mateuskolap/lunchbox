<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { useFilters } from '@/composables/useFilters';
import { salesByCustomer } from '@/routes/reports';
import type { PaginatedResponse } from '@/types';
import SalesSummaryCards from './components/SalesSummaryCards.vue';
import SalesByCustomerFilters from './components/SalesByCustomerFilters.vue';
import SalesByCustomerTable, { type CustomerReportRow } from './components/SalesByCustomerTable.vue';

interface SalesReportFiltersState {
    start_date: string;
    end_date: string;
    customer: string;
}

const props = defineProps<{
    customers: PaginatedResponse<CustomerReportRow>;
    total_sales: number;
    total_paid: number;
    total_balance: number;
    filters: {
        start_date?: string;
        end_date?: string;
        customer?: string;
    };
}>();

const { filters, clearFilters, hasActiveFilters } =
    useFilters<SalesReportFiltersState>(
        () => salesByCustomer.url(),
        {
            start_date: props.filters.start_date || '',
            end_date: props.filters.end_date || '',
            customer: props.filters.customer || '',
        },
        {
            transform: (f) => ({
                start_date: f.start_date || undefined,
                end_date: f.end_date || undefined,
                customer: f.customer || undefined,
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
                title: 'Vendas por Cliente',
                href: salesByCustomer.url(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Relatório de Vendas por Cliente" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-hidden p-4 sm:p-6">
        <div class="flex items-center justify-between">
            <Heading
                title="Relatório de Vendas por Cliente"
                description="Consulte as vendas, valores pagos e saldos devedores por cliente no período selecionado."
            />
        </div>

        <SalesSummaryCards
            :total-sales="total_sales"
            :total-paid="total_paid"
            :total-balance="total_balance"
        />

        <SalesByCustomerFilters
            v-model="filters"
            :has-active-filters="hasActiveFilters"
            @clear="clearFilters"
        />

        <SalesByCustomerTable :customers="customers" />
    </div>
</template>
