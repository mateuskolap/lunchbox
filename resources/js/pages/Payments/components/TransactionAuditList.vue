<script setup lang="ts">
import {
    ArrowDownCircle,
    ArrowUpCircle,
    Receipt,
} from 'lucide-vue-next';
import EmptyState from '@/components/EmptyState.vue';
import TablePagination from '@/components/TablePagination.vue';
import {
    formatCurrency,
    formatDateTime,
} from '@/lib/formatters';
import type { PaginatedResponse, Transaction } from '@/types';

defineProps<{
    transactions: PaginatedResponse<Transaction>;
}>();

function getTransactionIcon(transaction: Transaction) {
    if (transaction.description.toLowerCase().includes('cancelamento') || transaction.description.toLowerCase().includes('estorno')) {
        return ArrowUpCircle;
    }
    return ArrowDownCircle;
}

function isDebitTransaction(transaction: Transaction) {
    return transaction.description.toLowerCase().includes('cancelamento') || transaction.description.toLowerCase().includes('estorno');
}
</script>

<template>
    <div
        class="flex flex-1 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
    >
        <div class="flex items-center gap-2 border-b border-sidebar-border/50 px-5 py-4">
            <Receipt class="size-4 text-muted-foreground" />
            <h3 class="text-sm font-semibold text-muted-foreground">
                Extrato de Transações
            </h3>
        </div>

        <EmptyState
            v-if="transactions.data.length === 0"
            :icon="Receipt"
            title="Nenhuma transação encontrada"
            description="Este cliente ainda não possui transações registradas."
        />

        <div v-else class="flex flex-1 flex-col overflow-auto">
            <div class="divide-y divide-sidebar-border/50">
                <div
                    v-for="transaction in transactions.data"
                    :key="transaction.id"
                    class="flex items-start gap-3 px-5 py-3.5"
                >
                    <div
                        class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full"
                        :class="
                            isDebitTransaction(transaction)
                                ? 'bg-destructive/10'
                                : 'bg-emerald-500/10'
                        "
                    >
                        <component
                            :is="getTransactionIcon(transaction)"
                            class="size-4"
                            :class="
                                isDebitTransaction(transaction)
                                    ? 'text-destructive'
                                    : 'text-emerald-600 dark:text-emerald-400'
                            "
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p
                            class="text-sm font-medium break-words text-foreground"
                        >
                            {{ transaction.description }}
                        </p>
                        <p
                            class="mt-0.5 text-xs text-muted-foreground"
                        >
                            {{
                                formatDateTime(
                                    transaction.created_at,
                                )
                            }}
                        </p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p
                            class="text-sm font-semibold"
                            :class="
                                isDebitTransaction(transaction)
                                    ? 'text-destructive'
                                    : 'text-emerald-600 dark:text-emerald-400'
                            "
                        >
                            {{
                                isDebitTransaction(transaction)
                                    ? '-'
                                    : '+'
                            }}{{ formatCurrency(transaction.amount) }}
                        </p>
                        <p
                            class="mt-0.5 text-xs text-muted-foreground"
                        >
                            Saldo:
                            {{
                                formatCurrency(
                                    transaction.customer_balance,
                                )
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <TablePagination :paginator="transactions" />
        </div>
    </div>
</template>
