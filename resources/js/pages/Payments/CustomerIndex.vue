<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Filter, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useFilters } from '@/composables/useFilters';
import { usePermissions } from '@/composables/usePermissions';
import { index as customersIndex } from '@/routes/customers';
import { customerIndex as customerPaymentsIndex } from '@/routes/customers/payments';
import type {
    Customer,
    Order,
    PaginatedResponse,
    Payment,
    Transaction,
} from '@/types';
import PaymentHeader from './components/PaymentHeader.vue';
import PaymentSummaryCards from './components/PaymentSummaryCards.vue';
import PaymentTable from './components/PaymentTable.vue';
import TransactionAuditList from './components/TransactionAuditList.vue';
import UnpaidOrdersTable from './components/UnpaidOrdersTable.vue';

interface PaymentFilters {
    start_date: string;
    end_date: string;
}

const props = defineProps<{
    payments: PaginatedResponse<Payment>;
    transactions: PaginatedResponse<Transaction>;
    unpaid_orders: PaginatedResponse<Order>;
    customer: Customer;
    filters: Partial<PaymentFilters>;
}>();

const { can } = usePermissions();

const { filters, clearFilters, hasActiveFilters } = useFilters<PaymentFilters>(
    () => customerPaymentsIndex.url(props.customer.id),
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
                title: 'Clientes',
                href: customersIndex(),
            },
            {
                title: 'Pagamentos',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Pagamentos - ${customer.name}`" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-hidden p-4 sm:p-6">
        <!-- Header -->
        <PaymentHeader
            :customer="customer"
            :can-create-payment="can('payments.store')"
        />

        <!-- Summary Cards -->
        <PaymentSummaryCards
            :customer="customer"
            :payments-total="payments.total"
        />

        <!-- Filter Bar -->
        <div
            class="flex flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
        >
            <div class="flex items-center justify-between">
                <div
                    class="flex items-center gap-1.5 text-sm font-semibold text-muted-foreground"
                >
                    <Filter class="size-4" />
                    Filtros
                </div>
                <!-- Clear Filters button -->
                <Button
                    v-if="hasActiveFilters"
                    type="button"
                    variant="ghost"
                    class="h-8 px-2 text-xs text-muted-foreground hover:text-foreground"
                    @click="clearFilters"
                >
                    <X class="mr-1 size-3.5" />
                    Limpar
                </Button>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                <!-- Start Date Filter -->
                <div class="w-full sm:w-[180px]">
                    <label
                        class="mb-1 block text-xs font-medium text-muted-foreground"
                        >Data Inicial</label
                    >
                    <Input
                        type="date"
                        v-model="filters.start_date"
                        placeholder="Data inicial"
                        class="h-9"
                        :class="{
                            'text-muted-foreground': !filters.start_date,
                            'text-foreground': filters.start_date,
                        }"
                    />
                </div>

                <!-- End Date Filter -->
                <div class="w-full sm:w-[180px]">
                    <label
                        class="mb-1 block text-xs font-medium text-muted-foreground"
                    >
                        Data Final
                    </label>
                    <Input
                        type="date"
                        v-model="filters.end_date"
                        placeholder="Data final"
                        class="h-9"
                        :class="{
                            'text-muted-foreground': !filters.end_date,
                            'text-foreground': filters.end_date,
                        }"
                    />
                </div>
            </div>
        </div>

        <!-- Content List -->
        <div class="flex flex-col gap-6">
            <!-- Payments Table -->
            <div class="min-w-0">
                <PaymentTable
                    :customer="customer"
                    :payments="payments"
                    :can-cancel-payment="can('payments.update')"
                />
            </div>

            <!-- Unpaid Orders Table -->
            <div class="min-w-0">
                <UnpaidOrdersTable :unpaid-orders="unpaid_orders" />
            </div>

            <!-- Transactions (Audit) -->
            <div class="min-w-0">
                <TransactionAuditList :transactions="transactions" />
            </div>
        </div>
    </div>
</template>
