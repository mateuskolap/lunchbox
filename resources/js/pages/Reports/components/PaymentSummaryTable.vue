<script setup lang="ts">
import { CreditCard } from 'lucide-vue-next';
import { formatCurrency } from '@/lib/formatters';
import type { PaymentMethodItem } from '../types';

defineProps<{
    data: PaymentMethodItem[];
    totalValue: number;
    totalCount: number;
}>();

const methodColors: Record<string, string> = {
    pix: 'bg-cyan-500',
    cash: 'bg-emerald-500',
    credit_card: 'bg-purple-500',
    debit_card: 'bg-blue-500',
    food_voucher: 'bg-amber-500',
    other: 'bg-gray-500',
};
</script>

<template>
    <div
        class="flex flex-col rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
    >
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div
                    class="flex size-8 items-center justify-center rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400"
                >
                    <CreditCard class="size-4" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-foreground">
                        Resumo de Pagamentos
                    </h3>
                    <p class="text-xs text-muted-foreground">
                        Detalhamento por método de pagamento
                    </p>
                </div>
            </div>
        </div>

        <div v-if="data.length > 0" class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr
                        class="border-b border-border/50 text-xs font-semibold text-muted-foreground"
                    >
                        <th class="pb-2.5">Método</th>
                        <th class="pb-2.5 text-center">Transações</th>
                        <th class="pb-2.5 text-right">Valor Total</th>
                        <th class="pb-2.5 pl-4 text-right">Participação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    <tr
                        v-for="item in data"
                        :key="item.method"
                        class="transition-colors hover:bg-muted/30"
                    >
                        <td class="py-2.5 font-medium text-foreground">
                            <div class="flex items-center gap-2">
                                <span
                                    class="size-2.5 shrink-0 rounded-full"
                                    :class="
                                        methodColors[item.method] ||
                                        'bg-slate-400'
                                    "
                                />
                                <span>{{ item.label }}</span>
                            </div>
                        </td>
                        <td class="py-2.5 text-center text-muted-foreground">
                            {{ item.count }}
                        </td>
                        <td
                            class="py-2.5 text-right font-semibold text-foreground"
                        >
                            {{ formatCurrency(item.total_value) }}
                        </td>
                        <td class="py-2.5 pl-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div
                                    class="hidden h-1.5 w-16 overflow-hidden rounded-full bg-muted sm:block"
                                >
                                    <div
                                        class="h-full rounded-full transition-all"
                                        :class="
                                            methodColors[item.method] ||
                                            'bg-primary'
                                        "
                                        :style="{
                                            width: `${item.percentage}%`,
                                        }"
                                    />
                                </div>
                                <span
                                    class="w-11 text-right text-xs font-medium text-muted-foreground"
                                >
                                    {{ item.percentage }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr
                        class="border-t border-border bg-muted/20 font-bold text-foreground"
                    >
                        <td class="py-2.5">Total Geral</td>
                        <td class="py-2.5 text-center">{{ totalCount }}</td>
                        <td
                            class="py-2.5 text-right text-emerald-600 dark:text-emerald-400"
                        >
                            {{ formatCurrency(totalValue) }}
                        </td>
                        <td class="py-2.5 pl-4 text-right">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div
            v-else
            class="flex h-[200px] items-center justify-center text-sm text-muted-foreground"
        >
            Nenhum pagamento registrado no período.
        </div>
    </div>
</template>
