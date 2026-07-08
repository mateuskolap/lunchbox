<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { useFilters } from '@/composables/useFilters';
import { usePermissions } from '@/composables/usePermissions';
import { create as ordersCreate, index as ordersIndex } from '@/routes/orders';
import type { Order, PaginatedResponse } from '@/types';
import OrderFilters from './components/OrderFilters.vue';
import OrderTable from './components/OrderTable.vue';

interface OrderFiltersState {
    customer_name: string;
    status: string;
    start_date: string;
    end_date: string;
    customer_id: string;
}

const props = defineProps<{
    orders: PaginatedResponse<Order>;
    order_statuses: string[];
    filters: {
        customer_name?: string;
        status?: string;
        start_date?: string;
        end_date?: string;
        customer_id?: string;
    };
}>();

const { can } = usePermissions();

const { filters, clearFilters, hasActiveFilters } =
    useFilters<OrderFiltersState>(
        () => ordersIndex(),
        {
            customer_name: props.filters.customer_name || '',
            status: props.filters.status || 'all',
            start_date: props.filters.start_date || '',
            end_date: props.filters.end_date || '',
            customer_id: props.filters.customer_id || '',
        },
        {
            immediateFields: ['status'],
            transform: (f) => ({
                customer_name: f.customer_name || undefined,
                status: f.status && f.status !== 'all' ? f.status : undefined,
                start_date: f.start_date || undefined,
                end_date: f.end_date || undefined,
                customer_id: f.customer_id || undefined,
            }),
        },
    );
</script>

<template>
    <Head title="Pedidos" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <!-- Top bar with Heading & Create Button -->
        <div class="flex items-center justify-between">
            <Heading
                title="Pedidos"
                description="Gerencie os pedidos do sistema."
            />
            <Button
                v-if="can('orders.store')"
                as-child
                data-test="create-order-button"
            >
                <Link :href="ordersCreate.url()">
                    <Plus class="mr-2 size-4" />
                    Novo Pedido
                </Link>
            </Button>
        </div>

        <!-- Filter Bar -->
        <OrderFilters
            v-model="filters"
            :order-statuses="order_statuses"
            :has-active-filters="hasActiveFilters"
            @clear="clearFilters"
        />

        <!-- Orders Table container -->
        <OrderTable :orders="orders" />
    </div>
</template>
