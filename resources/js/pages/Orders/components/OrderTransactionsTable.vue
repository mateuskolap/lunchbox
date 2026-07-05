<script setup lang="ts">
import { Receipt, ArrowUpCircle, ArrowDownCircle } from 'lucide-vue-next';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatCurrency, formatDateTime } from '@/lib/formatters';
import type { Order, Transaction } from '@/types';

defineProps<{
    order: Order;
}>();

function isZeroTransaction(transaction: Transaction) {
    return Number(transaction.amount) === 0;
}

function isDebitTransaction(transaction: Transaction) {
    return Number(transaction.amount) < 0;
}

function getTransactionIcon(transaction: Transaction) {
    if (isZeroTransaction(transaction)) {
        return Receipt;
    }
    return isDebitTransaction(transaction) ? ArrowDownCircle : ArrowUpCircle;
}
</script>

<template>
    <div
        class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
    >
        <div
            class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
        >
            <h3 class="flex items-center gap-2 text-base font-semibold">
                <Receipt class="size-4 text-muted-foreground" />
                Histórico de Transações do Pedido
            </h3>
        </div>

        <!-- Transactions Table -->
        <div
            class="w-full overflow-x-auto rounded-lg border border-sidebar-border bg-card/50"
        >
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Transação</TableHead>
                        <TableHead>Data</TableHead>
                        <TableHead class="text-right">Valor</TableHead>
                        <TableHead class="text-right"
                            >Saldo do Cliente</TableHead
                        >
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-if="
                            !order.transactions ||
                            order.transactions.length === 0
                        "
                    >
                        <TableCell
                            colspan="4"
                            class="py-6 text-center text-sm text-muted-foreground"
                        >
                            Nenhuma transação vinculada a este pedido.
                        </TableCell>
                    </TableRow>
                    <TableRow
                        v-for="transaction in order.transactions"
                        :key="transaction.id"
                    >
                        <TableCell class="font-medium">
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex size-6 shrink-0 items-center justify-center rounded-full"
                                    :class="
                                        isZeroTransaction(transaction)
                                            ? 'bg-muted/70'
                                            : isDebitTransaction(transaction)
                                              ? 'bg-destructive/10'
                                              : 'bg-emerald-500/10'
                                    "
                                >
                                    <component
                                        :is="getTransactionIcon(transaction)"
                                        class="size-3"
                                        :class="
                                            isZeroTransaction(transaction)
                                                ? 'text-muted-foreground'
                                                : isDebitTransaction(
                                                        transaction,
                                                    )
                                                  ? 'text-destructive'
                                                  : 'text-emerald-600 dark:text-emerald-400'
                                        "
                                    />
                                </div>
                                <span class="break-words text-foreground">
                                    {{ transaction.description }}
                                </span>
                            </div>
                        </TableCell>
                        <TableCell
                            class="text-sm whitespace-nowrap text-muted-foreground"
                        >
                            {{ formatDateTime(transaction.created_at) }}
                        </TableCell>
                        <TableCell
                            class="text-right font-medium"
                            :class="
                                isZeroTransaction(transaction)
                                    ? 'text-muted-foreground'
                                    : isDebitTransaction(transaction)
                                      ? 'text-destructive'
                                      : 'text-emerald-600 dark:text-emerald-400'
                            "
                        >
                            {{
                                isZeroTransaction(transaction)
                                    ? ''
                                    : isDebitTransaction(transaction)
                                      ? '-'
                                      : '+'
                            }}{{
                                formatCurrency(
                                    Math.abs(Number(transaction.amount)),
                                )
                            }}
                        </TableCell>
                        <TableCell
                            class="text-right text-sm text-muted-foreground"
                        >
                            {{ formatCurrency(transaction.customer_balance) }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
