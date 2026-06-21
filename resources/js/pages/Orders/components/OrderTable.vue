<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Eye, Plus, ShoppingCart } from 'lucide-vue-next';
import EmptyState from '@/components/EmptyState.vue';
import TablePagination from '@/components/TablePagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { usePermissions } from '@/composables/usePermissions';
import { formatCurrency, formatDate, getOrderStatusBadgeVariant } from '@/lib/formatters';
import { create as ordersCreate, show as orderShow } from '@/routes/orders';
import type { Order, PaginatedResponse } from '@/types';

defineProps<{
    orders: PaginatedResponse<Order>;
}>();

const { can } = usePermissions();
</script>

<template>
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
                <Link :href="ordersCreate.url()">
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
                        <TableHead class="w-[120px] text-center"
                            >Status</TableHead
                        >
                        <TableHead class="w-[80px] text-right">Ações</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="order in orders.data" :key="order.id">
                        <TableCell class="font-bold">#{{ order.id }}</TableCell>
                        <TableCell class="font-medium">
                            {{ order.customer?.name || 'Cliente desconhecido' }}
                        </TableCell>
                        <TableCell>{{ formatDate(order.date) }}</TableCell>
                        <TableCell class="text-right font-medium">
                            {{ formatCurrency(order.total_amount) }}
                        </TableCell>
                        <TableCell class="text-center">
                            <Badge
                                :variant="getOrderStatusBadgeVariant(order.status)"
                                class="capitalize"
                            >
                                {{ trans(order.status) }}
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
                                    <Link :href="orderShow.url(order.id)">
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
</template>
