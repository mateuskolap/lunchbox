<script setup lang="ts">
import { Head, Link, usePoll } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { create as ordersCreate, today as ordersToday } from '@/routes/orders';
import type { Order } from '@/types';
import TodayOrdersList from './components/TodayOrdersList.vue';
import TodaySummary from './components/TodaySummary.vue';
import TodayTotalCard from './components/TodayTotalCard.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pedidos',
                href: ordersToday(),
            },
            {
                title: 'Hoje',
                href: '#',
            },
        ],
    },
});

const props = defineProps<{
    orders: Order[];
}>();

usePoll(10000);

const isMounted = ref(false);
onMounted(() => {
    isMounted.value = true;
});

type LunchboxSummaryItem = {
    name: string;
    quantity: number;
};

const lunchboxSummary = computed<LunchboxSummaryItem[]>(() => {
    const countMap = new Map<string, number>();

    for (const order of props.orders) {
        if (order.status === 'canceled') {
            continue;
        }

        for (const item of order.items ?? []) {
            if (item.product?.show_in_prep_summary) {
                const name = item.product.name;
                const qty = Number(item.quantity) || 0;
                countMap.set(name, (countMap.get(name) ?? 0) + qty);
            }
        }
    }

    return Array.from(countMap.entries())
        .map(([name, quantity]) => ({ name, quantity }))
        .sort((a, b) => a.name.localeCompare(b.name));
});

const todayTotal = computed(() => {
    return props.orders
        .filter((order) => order.status !== 'canceled')
        .reduce((sum, order) => sum + Number(order.total_amount), 0);
});

const todayCount = computed(() => {
    return props.orders.filter((order) => order.status !== 'canceled').length;
});
</script>

<template>
    <Head title="Pedidos do Dia" />

    <div class="relative flex h-full flex-1 flex-col gap-4 p-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <Heading
                title="Pedidos do Dia"
                description="Acompanhe os pedidos de hoje em tempo real."
            />
        </div>

        <!-- Total open & concluded orders card -->
        <TodayTotalCard :total="todayTotal" :count="todayCount" />

        <!-- Lunchbox Summary -->
        <TodaySummary :summary="lunchboxSummary" />

        <!-- Orders List -->
        <TodayOrdersList :orders="orders" :is-mounted="isMounted" />

        <!-- FAB - New Order -->
        <Link
            :href="ordersCreate.url()"
            class="fixed right-6 bottom-6 z-50 flex size-14 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg transition-all hover:scale-105 hover:shadow-xl active:scale-95"
        >
            <Plus class="size-6" />
            <span class="sr-only">Novo Pedido</span>
        </Link>
    </div>
</template>
