<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ShoppingCart,
    Eye,
    Plus,
    X,
    Filter,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
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
import type { Customer, Order, PaginatedResponse } from '@/types';

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
    customers: Customer[];
    filters: {
        customer_id?: string;
        status?: string;
        date?: string;
    };
}>();

const { can, canAny } = usePermissions();

// Filters states
const customerId = ref(props.filters.customer_id || '');
const status = ref(props.filters.status || '');
const dateValue = ref(props.filters.date || '');

// Watcher for automatic query reload on filter change
watch([customerId, status, dateValue], () => {
    router.get(
        ordersIndex(),
        {
            customer_id: customerId.value || undefined,
            status: status.value || undefined,
            date: dateValue.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
});

const clearFilters = () => {
    customerId.value = '';
    status.value = '';
    dateValue.value = '';
};

const hasActiveFilters = () => {
    return !!(customerId.value || status.value || dateValue.value);
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
    switch (orderStatus) {
        case 'concluded':
            return 'Concluído';
        case 'canceled':
            return 'Cancelado';
        default:
            return 'Pendente';
    }
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
            <Button v-if="can('orders.store')" as-child data-test="create-order-button">
                <Link href="/pedidos/criar">
                    <Plus class="mr-2 size-4" />
                    Novo Pedido
                </Link>
            </Button>
        </div>

        <!-- Filter Bar -->
        <div class="flex flex-wrap items-end gap-3 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border">
            <div class="flex items-center gap-1.5 text-sm font-semibold text-muted-foreground mr-2">
                <Filter class="size-4" />
                Filtros
            </div>

            <!-- Customer Filter -->
            <div class="w-full sm:w-[220px]">
                <label class="mb-1 block text-xs font-medium text-muted-foreground">Cliente</label>
                <select
                    v-model="customerId"
                    class="file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                >
                    <option value="">Todos os clientes</option>
                    <option
                        v-for="customer in customers"
                        :key="customer.id"
                        :value="customer.id"
                    >
                        {{ customer.name }}
                    </option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="w-full sm:w-[160px]">
                <label class="mb-1 block text-xs font-medium text-muted-foreground">Status</label>
                <select
                    v-model="status"
                    class="file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                >
                    <option value="">Todos os status</option>
                    <option value="pending">Pendente</option>
                    <option value="concluded">Concluído</option>
                    <option value="canceled">Cancelado</option>
                </select>
            </div>

            <!-- Date Filter -->
            <div class="w-full sm:w-[160px]">
                <label class="mb-1 block text-xs font-medium text-muted-foreground">Data</label>
                <Input
                    type="date"
                    v-model="dateValue"
                    class="h-9"
                />
            </div>

            <!-- Clear Filters button -->
            <Button
                v-if="hasActiveFilters()"
                type="button"
                variant="ghost"
                class="h-9 text-muted-foreground hover:text-foreground"
                @click="clearFilters"
            >
                <X class="mr-1 size-4" />
                Limpar
            </Button>
        </div>

        <!-- Orders Table container -->
        <div class="flex flex-1 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border">
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
                            <TableHead class="text-right">Valor Total</TableHead>
                            <TableHead class="text-center w-[120px]">Status</TableHead>
                            <TableHead
                                class="w-[80px] text-right"
                                >Ações</TableHead
                            >
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="order in orders.data"
                            :key="order.id"
                        >
                            <TableCell class="font-bold">#{{ order.id }}</TableCell>
                            <TableCell class="font-medium">
                                {{ order.customer?.name || 'Cliente desconhecido' }}
                            </TableCell>
                            <TableCell>{{ formatDate(order.date) }}</TableCell>
                            <TableCell class="text-right font-medium">
                                {{ formatCurrency(order.total_amount) }}
                            </TableCell>
                            <TableCell class="text-center">
                                <Badge :variant="getStatusBadgeVariant(order.status)" class="capitalize">
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
                                            <Eye class="size-4 text-muted-foreground" />
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
