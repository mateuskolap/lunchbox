<script setup lang="ts">
import { Calendar, FileText, User } from 'lucide-vue-next';
import { formatCurrency, formatDate, formatPhone } from '@/lib/formatters';
import type { Order } from '@/types';

defineProps<{
    order: Order;
}>();
</script>

<template>
    <div class="grid gap-6 sm:grid-cols-3">
        <!-- Customer info -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
        >
            <h3
                class="mb-4 flex items-center gap-2 text-sm font-semibold text-muted-foreground"
            >
                <User class="size-4" />
                Cliente
            </h3>
            <div v-if="order.customer" class="min-w-0 space-y-2">
                <p class="text-base font-bold break-words text-foreground">
                    {{ order.customer.name }}
                </p>
                <p class="text-sm text-muted-foreground">
                    Telefone: {{ formatPhone(order.customer.phone) }}
                </p>
            </div>
        </div>

        <!-- Order Info -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
        >
            <h3
                class="mb-4 flex items-center gap-2 text-sm font-semibold text-muted-foreground"
            >
                <Calendar class="size-4" />
                Informações Gerais
            </h3>
            <div class="space-y-3">
                <div
                    class="flex flex-wrap items-baseline justify-between gap-1 text-sm"
                >
                    <span class="text-muted-foreground">Data de Entrega:</span>
                    <span class="font-medium text-foreground">{{
                        formatDate(order.date)
                    }}</span>
                </div>
                <div
                    class="flex flex-wrap items-baseline justify-between gap-1 border-t border-sidebar-border/50 pt-2 text-sm"
                >
                    <span class="text-muted-foreground">Valor Total:</span>
                    <span class="text-base font-bold text-foreground">{{
                        formatCurrency(order.total_amount)
                    }}</span>
                </div>
                <div
                    class="flex flex-wrap items-baseline justify-between gap-1 border-t border-sidebar-border/50 pt-2 text-sm"
                >
                    <span class="text-muted-foreground">Valor Pago:</span>
                    <span
                        class="text-base font-bold"
                        :class="{
                            'text-emerald-600 dark:text-emerald-400':
                                order.status !== 'canceled' &&
                                Number(order.paid_amount) >=
                                    Number(order.total_amount),
                            'text-amber-500':
                                order.status !== 'canceled' &&
                                Number(order.paid_amount) > 0 &&
                                Number(order.paid_amount) <
                                    Number(order.total_amount),
                            'text-destructive':
                                order.status !== 'canceled' &&
                                Number(order.paid_amount) <= 0,
                            'text-muted-foreground line-through':
                                order.status === 'canceled',
                        }"
                    >
                        {{ formatCurrency(order.paid_amount) }}
                    </span>
                </div>
                <div
                    v-if="
                        Number(order.paid_amount) <
                            Number(order.total_amount) &&
                        order.status !== 'canceled'
                    "
                    class="flex flex-wrap items-baseline justify-between gap-1 border-t border-sidebar-border/50 pt-2 text-sm"
                >
                    <span class="text-muted-foreground">Valor Restante:</span>
                    <span class="text-base font-bold text-amber-500">
                        {{
                            formatCurrency(
                                Number(order.total_amount) -
                                    Number(order.paid_amount),
                            )
                        }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Observations -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
        >
            <h3
                class="mb-2 flex items-center gap-2 text-sm font-semibold text-muted-foreground"
            >
                <FileText class="size-4" />
                Observações
            </h3>
            <p
                class="rounded-lg border border-sidebar-border/50 bg-muted/30 p-3 text-sm break-words whitespace-pre-line text-foreground"
            >
                {{ order.observation || 'Nenhuma observação informada.' }}
            </p>
        </div>
    </div>
</template>
