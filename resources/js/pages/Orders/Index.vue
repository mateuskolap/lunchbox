<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ShoppingCart, Eye, Plus, X, Filter } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import { watchDebounced } from '@vueuse/core';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import TablePagination from '@/components/TablePagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { usePermissions } from '@/composables/usePermissions';
import { formatCurrency } from '@/lib/formatters';
import { index as ordersIndex } from '@/routes/orders';
import type { Order, PaginatedResponse } from '@/types';
import AppSelect from '@/components/AppSelect.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pedidos',
                href: ordersIndex(),
            },
        ],
    },
});

const props = defineProps<{
    orders: PaginatedResponse<Order>;
    order_statuses: string[];
    filters: {
        customer_name?: string;
        status?: string;
        date?: string;
    };
}>();

const { can, canAny } = usePermissions();

// Translations map for statuses
const statusTranslations: Record<string, string> = {
    pending: 'Pendente',
    concluded: 'Concluído',
    canceled: 'Cancelado',
};

const translateStatus = (statusKey: string): string => {
    return statusTranslations[statusKey] || statusKey;
};

const statusOptions = computed(() => {
    return [
        { value: 'all', label: 'Todos os status' },
        ...props.order_statuses.map((st) => ({
            value: st,
            label: translateStatus(st),
        })),
    ];
});

// Filters states
const customerName = ref(props.filters.customer_name || '');
const status = ref(props.filters.status || 'all');
const dateValue = ref(props.filters.date || '');

let isClearing = false;

const triggerSearch = () => {
    router.get(
        ordersIndex(),
        {
            customer_name: customerName.value || undefined,
            status:
                status.value && status.value !== 'all'
                    ? status.value
                    : undefined,
            date: dateValue.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

// Watch status change immediately (no lag for select dropdown)
watch(status, () => {
    triggerSearch();
});

// Watch input typing with a 500ms debounce
watchDebounced(
    [customerName, dateValue],
    () => {
        if (!isClearing) {
            triggerSearch();
        }
    },
    { debounce: 500 },
);

const clearFilters = () => {
    isClearing = true;
    customerName.value = '';
    status.value = 'all';
    dateValue.value = '';

    // Reset the clearing flag after the debounce duration to ignore the upcoming debounced watcher trigger
    setTimeout(() => {
        isClearing = false;
    }, 600);
};

const hasActiveFilters = () => {
    return !!(
        customerName.value ||
        (status.value && status.value !== 'all') ||
        dateValue.value
    );
};

const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    const datePart = dateStr.split('T')[0];
    const parts = datePart.split('-');
    if (parts.length !== 3) return dateStr;
    const [year, month, day] = parts;
    return `${day}/${month}/${year}`;
};

const getStatusBadgeVariant = (orderStatus: string) => {
    switch (orderStatus) {
        case 'concluded':
            return 'default'; // dark style
        case 'canceled':
            return 'destructive'; // red style
        default:
            return 'secondary'; // gray style
    }
};

const getStatusText = (orderStatus: string) => {
    return translateStatus(orderStatus);
};
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
                <Link href="/pedidos/criar">
                    <Plus class="mr-2 size-4" />
                    Novo Pedido
                </Link>
            </Button>
        </div>

        <!-- Filter Bar -->
        <div
            class="flex flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
        >
            <div class="flex items-center justify-between">
                <div
                    class="flex items-center gap-1.5 text-sm font-semibold text-muted-foreground"
                >
                    <Filter class="size-4" />
                    Filtros
                </div>
                <!-- Clear Filters button -->
                <Button
                    v-if="hasActiveFilters()"
                    type="button"
                    variant="ghost"
                    class="h-8 px-2 text-xs text-muted-foreground hover:text-foreground"
                    @click="clearFilters"
                >
                    <X class="mr-1 size-3.5" />
                    Limpar
                </Button>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                <!-- Customer Filter -->
                <div class="w-full sm:w-[220px]">
                    <label
                        class="mb-1 block text-xs font-medium text-muted-foreground"
                        >Cliente</label
                    >
                    <Input
                        v-model="customerName"
                        placeholder="Nome do cliente"
                        class="h-9"
                    />
                </div>

                <!-- Status Filter -->
                <div class="w-full sm:w-[160px]">
                    <label
                        class="mb-1 block text-xs font-medium text-muted-foreground"
                        >Status</label
                    >
                    <AppSelect
                        v-model="status"
                        :options="statusOptions"
                        placeholder="Todos os status"
                    />
                </div>

                <!-- Date Filter -->
                <div class="w-full sm:w-[160px]">
                    <label
                        class="mb-1 block text-xs font-medium text-muted-foreground"
                        >Data</label
                    >
                    <Input type="date" v-model="dateValue" class="h-9" />
                </div>
            </div>
        </div>

        <!-- Orders Table container -->
        <div
            class="flex flex-1 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
        >
            <EmptyState
                v-if="orders.data.length === 0"
                :icon="ShoppingCart"
                title="Nenhum pedido encontrado"
                description="Você ainda não tem nenhum pedido cadastrado ou nenhum pedido corresponde aos filtros aplicados."
            >
                <Button v-if="can('orders.store')" as-child>
                    <Link href="/pedidos/criar">
                        <Plus class="mr-2 size-4" />
                        Criar Pedido
                    </Link>
                </Button>
            </EmptyState>

            <div v-else class="flex flex-1 flex-col overflow-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[80px]">Pedido</TableHead>
                            <TableHead>Cliente</TableHead>
                            <TableHead>Data</TableHead>
                            <TableHead class="text-right"
                                >Valor Total</TableHead
                            >
                            <TableHead class="w-[120px] text-center"
                                >Status</TableHead
                            >
                            <TableHead class="w-[80px] text-right"
                                >Ações</TableHead
                            >
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="order in orders.data" :key="order.id">
                            <TableCell class="font-bold"
                                >#{{ order.id }}</TableCell
                            >
                            <TableCell class="font-medium">
                                {{
                                    order.customer?.name ||
                                    'Cliente desconhecido'
                                }}
                            </TableCell>
                            <TableCell>{{ formatDate(order.date) }}</TableCell>
                            <TableCell class="text-right font-medium">
                                {{ formatCurrency(order.total_amount) }}
                            </TableCell>
                            <TableCell class="text-center">
                                <Badge
                                    :variant="
                                        getStatusBadgeVariant(order.status)
                                    "
                                    class="capitalize"
                                >
                                    {{ getStatusText(order.status) }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end">
                                    <!-- View Details link -->
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        title="Ver Detalhes"
                                        as-child
                                    >
                                        <Link :href="`/pedidos/${order.id}`">
                                            <Eye
                                                class="size-4 text-muted-foreground"
                                            />
                                            <span class="sr-only">Ver</span>
                                        </Link>
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <TablePagination :paginator="orders" />
            </div>
        </div>
    </div>
</template>
