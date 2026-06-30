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

interface CustomerFiltersState {
    name: string;
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
    filters: {
        name?: string;
    };
}>();

const { can } = usePermissions();

const { filters, clearFilters, hasActiveFilters } =
    useFilters<CustomerFiltersState>(
        () => customersIndex(),
        {
            name: props.filters.name || '',
        },
        {
            transform: (f) => ({
                name: f.name || undefined,
            }),
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
