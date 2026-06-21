<script setup lang="ts">
import { ShoppingCart } from 'lucide-vue-next';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatCurrency } from '@/lib/formatters';
import type { Order } from '@/types';

defineProps<{
    order: Order;
}>();
</script>

<template>
    <div
        class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
    >
        <div
            class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
        >
            <h3 class="flex items-center gap-2 text-base font-semibold">
                <ShoppingCart class="size-4 text-muted-foreground" />
                Produtos no Pedido
            </h3>
        </div>

        <!-- Items Table -->
        <div
            class="w-full overflow-x-auto rounded-lg border border-sidebar-border bg-card/50"
        >
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Produto</TableHead>
                        <TableHead class="text-right">Unitário</TableHead>
                        <TableHead class="w-[100px] text-center"
                            >Quantidade</TableHead
                        >
                        <TableHead class="text-right">Subtotal</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="!order.items || order.items.length === 0">
                        <TableCell
                            colspan="4"
                            class="py-6 text-center text-sm text-muted-foreground"
                        >
                            Nenhum item neste pedido.
                        </TableCell>
                    </TableRow>
                    <TableRow v-for="item in order.items" :key="item.id">
                        <TableCell class="font-medium">
                            {{
                                item.product?.name ||
                                `Produto #${item.product_id}`
                            }}
                        </TableCell>
                        <TableCell class="text-right">
                            {{ formatCurrency(item.unit_price) }}
                        </TableCell>
                        <TableCell class="text-center">
                            {{ item.quantity }}
                        </TableCell>
                        <TableCell
                            class="text-right font-medium text-foreground"
                        >
                            {{ formatCurrency(item.total_amount) }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
