<script setup lang="ts">
import { Head, Link, usePoll } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import {
    ChevronRight,
    Clock,
    FileText,
    Plus,
    ShoppingCart,
    User,
    UtensilsCrossed,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatCurrency } from '@/lib/formatters';
import {
    create as ordersCreate,
    show as orderShow,
    today as ordersToday,
} from '@/routes/orders';
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
    return trans(status);
};

const formatTime = (dateStr: string) => {
    if (!isMounted.value || !dateStr) {
        return '';
    }

    const date = new Date(dateStr);
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${hours}:${minutes}`;
};

const pendingOrders = computed(() => {
    return props.orders.filter(
        (order) => order.status !== 'concluded' && order.status !== 'canceled',
    );
});

const otherOrders = computed(() => {
    return props.orders.filter(
        (order) => order.status === 'concluded' || order.status === 'canceled',
    );
});

const showOtherOrders = ref(false);
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
            <!-- Pending Orders -->
            <template v-if="pendingOrders.length > 0">
                <Link
                    v-for="order in pendingOrders"
                    :key="order.id"
                    :href="orderShow.url(order.id)"
                    class="group block rounded-xl border border-sidebar-border/70 bg-card p-4 transition-all hover:border-primary/30 hover:shadow-md active:scale-[0.99] dark:border-sidebar-border dark:hover:border-primary/40"
                >
                    <!-- Card Header -->
                    <div class="mb-3 flex items-start justify-between gap-2">
                        <div class="flex min-w-0 items-center gap-2">
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
                                <span class="font-medium text-foreground"
                                    >{{ item.quantity }}x</span
                                >
                                {{
                                    item.product?.name ?? `#${item.product_id}`
                                }}
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
                            class="line-clamp-2 text-xs text-amber-700 dark:text-amber-300"
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
            </template>
            <div
                v-else
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-sidebar-border/70 bg-muted/20 py-8 text-center"
            >
                <Clock class="mb-2 size-8 text-muted-foreground/60" />
                <p class="text-sm font-medium text-muted-foreground">
                    Nenhum pedido pendente para hoje.
                </p>
            </div>

            <!-- Other Orders Dropdown (Completed & Canceled) -->
            <div
                v-if="otherOrders.length > 0"
                class="mt-4 border-t border-sidebar-border/70 pt-4"
            >
                <button
                    type="button"
                    @click="showOtherOrders = !showOtherOrders"
                    class="flex w-full items-center justify-between rounded-lg border border-sidebar-border/70 bg-card px-4 py-3 text-sm font-semibold text-foreground transition-all hover:bg-muted/50 active:scale-[0.99]"
                >
                    <span class="flex items-center gap-2">
                        <span>Outros pedidos ({{ otherOrders.length }})</span>
                        <span class="text-xs font-normal text-muted-foreground"
                            >(Concluídos e Cancelados)</span
                        >
                    </span>
                    <ChevronRight
                        class="size-4 text-muted-foreground transition-transform duration-200"
                        :class="{ 'rotate-90': showOtherOrders }"
                    />
                </button>

                <div v-show="showOtherOrders" class="mt-3 space-y-3">
                    <Link
                        v-for="order in otherOrders"
                        :key="order.id"
                        :href="orderShow.url(order.id)"
                        class="group block rounded-xl border border-sidebar-border/70 bg-card p-4 transition-all hover:border-primary/30 hover:shadow-md active:scale-[0.99] dark:border-sidebar-border dark:hover:border-primary/40"
                    >
                        <!-- Card Header -->
                        <div
                            class="mb-3 flex items-start justify-between gap-2"
                        >
                            <div class="flex min-w-0 items-center gap-2">
                                <div
                                    class="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted"
                                >
                                    <User
                                        class="size-4 text-muted-foreground"
                                    />
                                </div>
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-semibold text-foreground"
                                    >
                                        {{
                                            order.customer?.name ??
                                            'Sem cliente'
                                        }}
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
                                    :variant="
                                        getStatusBadgeVariant(order.status)
                                    "
                                    class="text-[10px] capitalize"
                                >
                                    {{ getStatusText(order.status) }}
                                </Badge>
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
                                    <span class="font-medium text-foreground"
                                        >{{ item.quantity }}x</span
                                    >
                                    {{
                                        item.product?.name ??
                                        `#${item.product_id}`
                                    }}
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
                                class="line-clamp-2 text-xs text-amber-700 dark:text-amber-300"
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
            </div>
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
