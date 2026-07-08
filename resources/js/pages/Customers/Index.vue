<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { useFilters } from '@/composables/useFilters';
import { usePermissions } from '@/composables/usePermissions';
import { index as customersIndex } from '@/routes/customers';
import type { Customer, PaginatedResponse } from '@/types';
import CustomerFilters from './components/CustomerFilters.vue';
import CustomerTable from './components/CustomerTable.vue';
import CreateCustomerDialog from './CreateCustomerDialog.vue';

interface CustomerFilters {
    name: string;
    debtors_only: boolean;
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Clientes',
                href: customersIndex(),
            },
        ],
    },
});

const props = defineProps<{
    customers: PaginatedResponse<Customer>;
    filters: Partial<CustomerFilters>;
}>();

const { can } = usePermissions();

const { filters, clearFilters, hasActiveFilters } = useFilters<CustomerFilters>(
    () => customersIndex(),
    {
        name: props.filters.name || '',
        debtors_only: String(props.filters.debtors_only) === '1',
    },
    {
        transform: (f) => ({
            name: f.name || undefined,
            debtors_only: f.debtors_only ? '1' : '0',
        }),
        immediateFields: ['debtors_only'],
    },
);
</script>

<template>
    <Head title="Clientes" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Clientes"
                description="Gerencie os clientes do sistema."
            />
            <CreateCustomerDialog v-if="can('customers.store')" />
        </div>

        <CustomerFilters
            v-model="filters"
            :has-active-filters="hasActiveFilters"
            @clear="clearFilters"
        />

        <CustomerTable :customers="customers" />
    </div>
</template>
