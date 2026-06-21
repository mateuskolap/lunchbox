<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { usePermissions } from '@/composables/usePermissions';
import { index as customersIndex } from '@/routes/customers';
import type { Customer, PaginatedResponse } from '@/types';
import CreateCustomerDialog from './CreateCustomerDialog.vue';
import CustomerTable from './components/CustomerTable.vue';

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

defineProps<{
    customers: PaginatedResponse<Customer>;
}>();

const { can } = usePermissions();
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

        <CustomerTable :customers="customers" />
    </div>
</template>
