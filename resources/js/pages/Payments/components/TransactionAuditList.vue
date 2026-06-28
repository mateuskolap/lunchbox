<script setup lang="ts">
import {
    ArrowDownCircle,
    ArrowUpCircle,
    Receipt,
    Eye,
} from 'lucide-vue-next';
import EmptyState from '@/components/EmptyState.vue';
import TablePagination from '@/components/TablePagination.vue';
import { Link } from '@inertiajs/vue3';
import { show as orderShow } from '@/routes/orders';
import {
    formatCurrency,
    formatDateTime,
} from '@/lib/formatters';
import type { PaginatedResponse, Transaction } from '@/types';

defineProps<{
    transactions: PaginatedResponse<Transaction>;
}>();

function isZeroTransaction(transaction: Transaction) {
    return Number(transaction.amount) === 0;
}

function isDebitTransaction(transaction: Transaction) {
    return Number(transaction.amount) < 0;
}

function isOrderTransaction(transaction: Transaction) {
    return transaction.transactionable_type === 'App\\Models\\Order';
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
                            isZeroTransaction(transaction)
                                ? 'bg-muted/70'
                                : isDebitTransaction(transaction)
                                    ? 'bg-destructive/10'
                                    : 'bg-emerald-500/10'
                        "
                    >
                        <component
                            :is="getTransactionIcon(transaction)"
                            class="size-4"
                            :class="
                                isZeroTransaction(transaction)
                                    ? 'text-muted-foreground'
                                    : isDebitTransaction(transaction)
                                        ? 'text-destructive'
                                        : 'text-emerald-600 dark:text-emerald-400'
                            "
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p
                                class="text-sm font-medium break-words text-foreground"
                            >
                                {{ transaction.description }}
                            </p>
                            <Link
                                v-if="isOrderTransaction(transaction)"
                                :href="orderShow.url(transaction.transactionable_id)"
                                class="inline-flex items-center gap-1 rounded bg-primary/10 px-1.5 py-0.5 text-[10px] font-semibold text-primary hover:bg-primary/20 transition-colors"
                            >
                                <Eye class="size-3" />
                                Ver Pedido
                            </Link>
                        </div>
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
                            }}{{ formatCurrency(Math.abs(Number(transaction.amount))) }}
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
