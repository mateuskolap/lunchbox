<script setup lang="ts">
import { Head, Link, usePoll } from '@inertiajs/vue3';
import {
    Plus,
    User,
    Clock,
    ShoppingCart,
    UtensilsCrossed,
    ChevronRight,
    FileText,
} from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import EmptyState from '@/components/EmptyState.vue';
import { formatCurrency } from '@/lib/formatters';
import {
    today as ordersToday,
    create as ordersCreate,
} from '@/routes/orders';
import { show as orderShow } from '@/routes/orders';
import type { Order } from '@/types';

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
            if (item.product?.is_lunchbox) {
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

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'concluded':
            return 'default';
        case 'canceled':
            return 'destructive';
        default:
            return 'secondary';
    }
};

const getStatusText = (status: string) => {
    switch (status) {
        case 'concluded':
            return 'Concluído';
        case 'canceled':
            return 'Cancelado';
        default:
            return 'Pendente';
    }
};

const formatTime = (dateStr: string) => {
    if (!isMounted.value || !dateStr) return '';
    const date = new Date(dateStr);
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes}`;
};
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

        <!-- Lunchbox Summary -->
        <div
            v-if="lunchboxSummary.length > 0"
            class="flex flex-wrap items-center gap-2"
        >
            <div
                v-for="item in lunchboxSummary"
                :key="item.name"
                class="flex items-center gap-2 rounded-lg border border-sidebar-border/70 bg-card px-3 py-2 dark:border-sidebar-border"
            >
                <UtensilsCrossed
                    class="size-3.5 shrink-0 text-muted-foreground"
                />
                <span class="text-sm font-medium text-foreground">{{
                    item.name
                }}</span>
                <span
                    class="flex size-6 items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-foreground"
                >
                    {{ item.quantity }}
                </span>
            </div>
        </div>

        <!-- Empty State -->
        <EmptyState
            v-if="orders.length === 0"
            :icon="ShoppingCart"
            title="Nenhum pedido hoje"
            description="Ainda não há pedidos registrados para hoje. Crie um novo pedido para começar."
        >
            <Button as-child>
                <Link :href="ordersCreate.url()">
                    <Plus class="mr-2 size-4" />
                    Novo Pedido
                </Link>
            </Button>
        </EmptyState>

        <!-- Orders List (Cards) -->
        <div v-else class="flex flex-1 flex-col gap-3 overflow-y-auto pb-20">
            <Link
                v-for="order in orders"
                :key="order.id"
                :href="orderShow.url(order.id)"
                class="group block rounded-xl border border-sidebar-border/70 bg-card p-4 transition-all hover:border-primary/30 hover:shadow-md active:scale-[0.99] dark:border-sidebar-border dark:hover:border-primary/40"
            >
                <!-- Card Header -->
                <div class="mb-3 flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <div
                            class="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted"
                        >
                            <User class="size-4 text-muted-foreground" />
                        </div>
                        <div class="min-w-0">
                            <p
                                class="truncate text-sm font-semibold text-foreground"
                            >
                                {{ order.customer?.name ?? 'Sem cliente' }}
                            </p>
                            <p
                                class="flex items-center gap-1 text-xs text-muted-foreground"
                            >
                                <Clock class="size-3" />
                                {{ formatTime(order.created_at) }}
                            </p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <Badge
                            :variant="getStatusBadgeVariant(order.status)"
                            class="text-[10px] capitalize"
                        >
                            {{ getStatusText(order.status) }}
                        </Badge>
                        <ChevronRight
                            class="size-4 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100"
                        />
                    </div>
                </div>

                <!-- Items -->
                <div
                    v-if="order.items && order.items.length > 0"
                    class="mb-3 space-y-1 rounded-lg bg-muted/30 px-3 py-2"
                >
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex items-center justify-between text-sm"
                    >
                        <span class="text-muted-foreground">
                            <span class="font-medium text-foreground">{{
                                item.quantity
                            }}x</span>
                            {{ item.product?.name ?? `#${item.product_id}` }}
                        </span>
                        <span class="text-xs text-muted-foreground">
                            {{ formatCurrency(item.total_amount) }}
                        </span>
                    </div>
                </div>

                <!-- Observation -->
                <div
                    v-if="order.observation"
                    class="mb-3 flex items-start gap-1.5 rounded-lg border border-amber-200/50 bg-amber-50/50 px-3 py-2 dark:border-amber-900/30 dark:bg-amber-950/20"
                >
                    <FileText
                        class="mt-0.5 size-3 shrink-0 text-amber-600 dark:text-amber-400"
                    />
                    <p
                        class="text-xs text-amber-700 dark:text-amber-300 line-clamp-2"
                    >
                        {{ order.observation }}
                    </p>
                </div>

                <!-- Card Footer -->
                <div
                    class="flex items-center justify-between border-t border-sidebar-border/50 pt-2"
                >
                    <span class="text-xs text-muted-foreground">
                        Pedido #{{ order.id }}
                    </span>
                    <span class="text-sm font-bold text-foreground">
                        {{ formatCurrency(order.total_amount) }}
                    </span>
                </div>
            </Link>
        </div>

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
