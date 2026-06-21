<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Clock, FileText, User } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import {
    formatCurrency,
    formatTime,
    getOrderStatusBadgeVariant,
} from '@/lib/formatters';
import { show as orderShow } from '@/routes/orders';
import type { Order } from '@/types';

defineProps<{
    order: Order;
    isMounted: boolean;
}>();
</script>

<template>
    <Link
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
                    <p class="truncate text-sm font-semibold text-foreground">
                        {{ order.customer?.name ?? 'Sem cliente' }}
                    </p>
                    <p
                        class="flex items-center gap-1 text-xs text-muted-foreground"
                    >
                        <Clock class="size-3" />
                        {{ isMounted ? formatTime(order.created_at) : '' }}
                    </p>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <Badge
                    :variant="getOrderStatusBadgeVariant(order.status)"
                    class="text-[10px] capitalize"
                >
                    {{ trans(order.status) }}
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
            <p class="line-clamp-2 text-xs text-amber-700 dark:text-amber-300">
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
