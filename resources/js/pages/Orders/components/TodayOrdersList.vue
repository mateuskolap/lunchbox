<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight, Clock, Plus, ShoppingCart } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import TodayOrderCard from './TodayOrderCard.vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { create as ordersCreate } from '@/routes/orders';
import type { Order } from '@/types';

const props = defineProps<{
    orders: Order[];
    isMounted: boolean;
}>();

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
            <TodayOrderCard
                v-for="order in pendingOrders"
                :key="order.id"
                :order="order"
                :is-mounted="isMounted"
            />
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
                <TodayOrderCard
                    v-for="order in otherOrders"
                    :key="order.id"
                    :order="order"
                    :is-mounted="isMounted"
                />
            </div>
        </div>
    </div>
</template>
