<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Eye, ShoppingCart } from 'lucide-vue-next';
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
import {
    formatCurrency,
    formatDate,
    getOrderStatusBadgeVariant,
} from '@/lib/formatters';
import { show as orderShow } from '@/routes/orders';
import type { Order, PaginatedResponse } from '@/types';

defineProps<{
    unpaidOrders: PaginatedResponse<Order>;
}>();
</script>

<template>
    <div
        class="flex flex-1 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
    >
        <div
            class="flex items-center gap-2 border-b border-sidebar-border/50 px-5 py-4"
        >
            <ShoppingCart class="size-4 text-muted-foreground" />
            <h3 class="text-sm font-semibold text-muted-foreground">
                Pedidos Pendentes de Pagamento
            </h3>
        </div>

        <EmptyState
            v-if="unpaidOrders.data.length === 0"
            :icon="ShoppingCart"
            title="Nenhum pedido pendente"
            description="Este cliente não possui pedidos pendentes de pagamento."
        />

        <div v-else class="flex flex-1 flex-col overflow-auto">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-[100px]">Pedido</TableHead>
                        <TableHead>Data</TableHead>
                        <TableHead class="text-right">Valor Total</TableHead>
                        <TableHead class="text-right">Valor Pago</TableHead>
                        <TableHead class="w-[120px] text-center"
                            >Status</TableHead
                        >
                        <TableHead class="w-[80px] text-right">Ações</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="order in unpaidOrders.data"
                        :key="order.id"
                    >
                        <TableCell class="font-bold">#{{ order.id }}</TableCell>
                        <TableCell>{{ formatDate(order.date) }}</TableCell>
                        <TableCell class="text-right font-medium">
                            {{ formatCurrency(order.total_amount) }}
                        </TableCell>
                        <TableCell class="text-right font-medium">
                            <span
                                :class="{
                                    'text-muted-foreground':
                                        order.status === 'canceled',
                                    'font-semibold text-emerald-600 dark:text-emerald-400':
                                        order.status !== 'canceled' &&
                                        Number(order.paid_amount) >=
                                            Number(order.total_amount),
                                    'font-semibold text-amber-500':
                                        order.status !== 'canceled' &&
                                        Number(order.paid_amount) > 0 &&
                                        Number(order.paid_amount) <
                                            Number(order.total_amount),
                                    'text-destructive':
                                        order.status !== 'canceled' &&
                                        Number(order.paid_amount) <= 0,
                                }"
                            >
                                {{ formatCurrency(order.paid_amount) }}
                            </span>
                        </TableCell>
                        <TableCell class="text-center">
                            <Badge
                                :variant="
                                    getOrderStatusBadgeVariant(order.status)
                                "
                                class="capitalize"
                            >
                                {{ trans(order.status) }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex items-center justify-end">
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

            <TablePagination :paginator="unpaidOrders" />
        </div>
    </div>
</template>
