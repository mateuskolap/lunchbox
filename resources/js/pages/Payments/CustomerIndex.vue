<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PaymentHeader from './components/PaymentHeader.vue';
import PaymentSummaryCards from './components/PaymentSummaryCards.vue';
import PaymentTable from './components/PaymentTable.vue';
import TransactionAuditList from './components/TransactionAuditList.vue';
import { usePermissions } from '@/composables/usePermissions';
import { index as customersIndex } from '@/routes/customers';
import type {
    Customer,
    PaginatedResponse,
    Payment,
    Transaction,
} from '@/types';

defineProps<{
    payments: PaginatedResponse<Payment>;
    transactions: PaginatedResponse<Transaction>;
    customer: Customer;
}>();

const { can } = usePermissions();

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

        <!-- Content Grid -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Payments Table -->
            <div class="min-w-0">
                <PaymentTable
                    :customer="customer"
                    :payments="payments"
                    :can-cancel-payment="can('payments.update')"
                />
            </div>

            <!-- Transactions (Audit) -->
            <div class="min-w-0">
                <TransactionAuditList :transactions="transactions" />
            </div>
        </div>
    </div>
</template>
