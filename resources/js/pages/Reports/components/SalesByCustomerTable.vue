<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Contact, ShoppingCart } from 'lucide-vue-next';
import EmptyState from '@/components/EmptyState.vue';
import TablePagination from '@/components/TablePagination.vue';
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
import { formatCurrency, formatPhone } from '@/lib/formatters';
import { index as ordersIndex } from '@/routes/orders';
import type { PaginatedResponse } from '@/types';

export interface CustomerReportRow {
    id: number;
    name: string;
    phone?: string;
    balance: string | number;
    orders_count: number;
    orders_sum_total_amount: string | null;
    orders_sum_paid_amount: string | null;
}

defineProps<{
    customers: PaginatedResponse<CustomerReportRow>;
}>();

const { can } = usePermissions();
</script>

<template>
    <div
        class="flex flex-1 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
    >
        <EmptyState
            v-if="customers.data.length === 0"
            :icon="Contact"
            title="Nenhum registro encontrado"
            description="Não há vendas registradas para os filtros selecionados no período."
        />

        <div v-else class="flex flex-1 flex-col overflow-auto">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Cliente</TableHead>
                        <TableHead>Telefone</TableHead>
                        <TableHead class="text-center">Qtd. Pedidos</TableHead>
                        <TableHead class="text-right">Total Vendido</TableHead>
                        <TableHead class="text-right">Total Pago</TableHead>
                        <TableHead class="text-right"
                            >Saldo em Aberto</TableHead
                        >
                        <TableHead
                            v-if="can('orders.index')"
                            class="w-[80px] text-right"
                            >Ações</TableHead
                        >
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="item in customers.data" :key="item.id">
                        <TableCell class="font-medium">
                            {{ item.name }}
                        </TableCell>
                        <TableCell>
                            {{ formatPhone(item.phone) }}
                        </TableCell>
                        <TableCell class="text-center">
                            {{ item.orders_count }}
                        </TableCell>
                        <TableCell
                            class="text-right font-medium"
                            :class="{
                                'text-muted-foreground':
                                    Number(
                                        item.orders_sum_total_amount || 0,
                                    ) === 0,
                            }"
                        >
                            {{
                                formatCurrency(
                                    item.orders_sum_total_amount || 0,
                                )
                            }}
                        </TableCell>
                        <TableCell
                            class="text-right font-medium"
                            :class="
                                Number(item.orders_sum_paid_amount || 0) === 0
                                    ? 'text-muted-foreground'
                                    : 'text-emerald-600 dark:text-emerald-400'
                            "
                        >
                            {{
                                formatCurrency(item.orders_sum_paid_amount || 0)
                            }}
                        </TableCell>
                        <TableCell
                            class="text-right font-medium"
                            :class="{
                                'text-destructive':
                                    Number(item.orders_sum_total_amount || 0) -
                                        Number(
                                            item.orders_sum_paid_amount || 0,
                                        ) >
                                    0,
                                'text-muted-foreground':
                                    Number(item.orders_sum_total_amount || 0) -
                                        Number(
                                            item.orders_sum_paid_amount || 0,
                                        ) ===
                                    0,
                            }"
                        >
                            {{
                                formatCurrency(
                                    Number(item.orders_sum_total_amount || 0) -
                                        Number(
                                            item.orders_sum_paid_amount || 0,
                                        ),
                                )
                            }}
                        </TableCell>
                        <TableCell
                            v-if="can('orders.index')"
                            class="text-right"
                        >
                            <div class="flex items-center justify-end">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    title="Ver Pedidos"
                                    as-child
                                >
                                    <Link
                                        :href="
                                            ordersIndex.url({
                                                query: {
                                                    customer_id: item.id,
                                                },
                                            })
                                        "
                                    >
                                        <ShoppingCart
                                            class="size-4 text-muted-foreground"
                                        />
                                        <span class="sr-only">Ver Pedidos</span>
                                    </Link>
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <TablePagination :paginator="customers" />
        </div>
    </div>
</template>
