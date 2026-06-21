<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { index as ordersIndex } from '@/routes/orders';
import type { Order } from '@/types';
import OrderInfoCards from './components/OrderInfoCards.vue';
import OrderItemsTable from './components/OrderItemsTable.vue';
import OrderShowHeader from './components/OrderShowHeader.vue';

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

        <div class="grid gap-6 md:grid-cols-12">
            <!-- Left Side: Summary Info Cards -->
            <div class="order-2 min-w-0 space-y-6 md:order-1 md:col-span-4">
                <OrderInfoCards :order="order" />
            </div>

            <!-- Right Side: Items List -->
            <div class="order-1 min-w-0 space-y-6 md:order-2 md:col-span-8">
                <OrderItemsTable :order="order" />
            </div>
        </div>
    </div>
</template>
