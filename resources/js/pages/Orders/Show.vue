<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import OrderInfoCards from './components/OrderInfoCards.vue';
import OrderItemsTable from './components/OrderItemsTable.vue';
import OrderShowHeader from './components/OrderShowHeader.vue';
import OrderTransactionsTable from './components/OrderTransactionsTable.vue';
import { index as ordersIndex } from '@/routes/orders';
import type { Order } from '@/types';

const isMounted = ref(false);
onMounted(() => {
    isMounted.value = true;
});

defineProps<{
    order: Order;
    list_url?: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pedidos',
                href: ordersIndex(),
            },
            {
                title: 'Detalhes do Pedido',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Pedido #${order.id}`" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-hidden p-4 sm:p-6">
        <!-- Header Actions -->
        <OrderShowHeader
            :order="order"
            :list-url="list_url"
            :is-mounted="isMounted"
        />

        <!-- Top Info Cards (Customer, General Info, Observations) -->
        <OrderInfoCards :order="order" />

        <!-- Products List & Transactions -->
        <div class="space-y-6">
            <OrderItemsTable :order="order" />
            <OrderTransactionsTable :order="order" />
        </div>
    </div>
</template>
