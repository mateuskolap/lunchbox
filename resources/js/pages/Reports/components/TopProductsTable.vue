<script setup lang="ts">
import { Package } from 'lucide-vue-next';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatCurrency } from '@/lib/formatters';
import type { TopProductItem } from '@/types';

defineProps<{
    products: TopProductItem[];
}>();
</script>

<template>
    <div
        class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
    >
        <div class="mb-4 flex items-center gap-2">
            <Package class="size-4 text-info" />
            <h3 class="text-sm font-medium text-muted-foreground">
                Top 10 Produtos
            </h3>
        </div>
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-8">#</TableHead>
                    <TableHead>Produto</TableHead>
                    <TableHead class="text-right">Qtd. Vendida</TableHead>
                    <TableHead class="text-right">Faturamento</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="(product, index) in products"
                    :key="product.id"
                >
                    <TableCell class="font-medium text-muted-foreground">
                        {{ index + 1 }}
                    </TableCell>
                    <TableCell>{{ product.name }}</TableCell>
                    <TableCell class="text-right">
                        {{
                            Number(product.total_quantity).toLocaleString(
                                'pt-BR',
                            )
                        }}
                    </TableCell>
                    <TableCell class="text-right">
                        {{ formatCurrency(product.total_revenue) }}
                    </TableCell>
                </TableRow>
                <TableRow v-if="products.length === 0">
                    <TableCell
                        :colspan="4"
                        class="py-8 text-center text-sm text-muted-foreground"
                    >
                        Nenhum dado no período
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
