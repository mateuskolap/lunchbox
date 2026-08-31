<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Trophy } from 'lucide-vue-next';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatCurrency } from '@/lib/formatters';
import { customerIndex } from '@/routes/customers/payments';
import type { TopCustomerItem } from '@/types';

defineProps<{
    customers: TopCustomerItem[];
}>();
</script>

<template>
    <div
        class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
    >
        <div class="mb-4 flex items-center gap-2">
            <Trophy class="size-4 text-warning" />
            <h3 class="text-sm font-medium text-muted-foreground">
                Top 10 Clientes
            </h3>
        </div>
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-8">#</TableHead>
                    <TableHead>Cliente</TableHead>
                    <TableHead class="text-right">Pedidos</TableHead>
                    <TableHead class="text-right">Faturado</TableHead>
                    <TableHead class="text-right">Pago</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="(customer, index) in customers"
                    :key="customer.id"
                >
                    <TableCell class="font-medium text-muted-foreground">
                        {{ index + 1 }}
                    </TableCell>
                    <TableCell>
                        <Link
                            :href="customerIndex.url(customer.id)"
                            class="text-primary hover:underline"
                        >
                            {{ customer.name }}
                        </Link>
                    </TableCell>
                    <TableCell class="text-right">
                        {{ customer.orders_count }}
                    </TableCell>
                    <TableCell class="text-right">
                        {{ formatCurrency(customer.total_sales) }}
                    </TableCell>
                    <TableCell
                        class="text-right text-emerald-600 dark:text-emerald-400"
                    >
                        {{ formatCurrency(customer.total_paid) }}
                    </TableCell>
                </TableRow>
                <TableRow v-if="customers.length === 0">
                    <TableCell
                        :colspan="5"
                        class="py-8 text-center text-sm text-muted-foreground"
                    >
                        Nenhum dado no período
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
