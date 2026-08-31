<script setup lang="ts">
import {
    TrendingUp,
    TrendingDown,
    Coins,
    Scale,
    ShoppingBag,
    Users,
    Receipt,
    ShieldAlert,
    Minus,
} from 'lucide-vue-next';
import { formatCurrency } from '@/lib/formatters';
import type { GeneralReportKPIs, MetricWithVariation } from '../types';

defineProps<{
    kpis: GeneralReportKPIs;
}>();

function formatVariation(variation: number | null): string {
    if (variation === null || variation === undefined) {
        return '0%';
    }

    const sign = variation > 0 ? '+' : '';

    return `${sign}${variation}%`;
}

function getVariationColor(
    metric: MetricWithVariation,
    invertSense: boolean = false,
): {
    bgClass: string;
    textClass: string;
    icon: typeof TrendingUp;
} {
    const val = metric.variation ?? 0;

    if (val === 0) {
        return {
            bgClass: 'bg-muted text-muted-foreground',
            textClass: 'text-muted-foreground',
            icon: Minus,
        };
    }

    const isPositive = val > 0;
    const isGood = invertSense ? !isPositive : isPositive;

    if (isGood) {
        return {
            bgClass: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
            textClass: 'text-emerald-600 dark:text-emerald-400',
            icon: isPositive ? TrendingUp : TrendingDown,
        };
    } else {
        return {
            bgClass: 'bg-destructive/10 text-destructive',
            textClass: 'text-destructive',
            icon: isPositive ? TrendingUp : TrendingDown,
        };
    }
}
</script>

<template>
    <div
        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
    >
        <!-- 1. Total de Vendas -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
        >
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                    >
                        <TrendingUp class="size-5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">
                            Total de Vendas
                        </p>
                        <p class="text-xl font-bold text-foreground">
                            {{ formatCurrency(kpis.total_sales.value) }}
                        </p>
                    </div>
                </div>
            </div>
            <div
                class="mt-3 flex items-center gap-1.5 border-t border-border/40 pt-3"
            >
                <span
                    class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-semibold"
                    :class="getVariationColor(kpis.total_sales).bgClass"
                >
                    <component
                        :is="getVariationColor(kpis.total_sales).icon"
                        class="mr-1 size-3"
                    />
                    {{ formatVariation(kpis.total_sales.variation) }}
                </span>
                <span class="text-xs text-muted-foreground"
                    >vs período anterior</span
                >
            </div>
        </div>

        <!-- 2. Total Pago / Recebido -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
        >
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                    >
                        <Coins class="size-5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">
                            Total Recebido
                        </p>
                        <p
                            class="text-xl font-bold text-emerald-600 dark:text-emerald-400"
                        >
                            {{ formatCurrency(kpis.total_paid.value) }}
                        </p>
                    </div>
                </div>
            </div>
            <div
                class="mt-3 flex items-center gap-1.5 border-t border-border/40 pt-3"
            >
                <span
                    class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-semibold"
                    :class="getVariationColor(kpis.total_paid).bgClass"
                >
                    <component
                        :is="getVariationColor(kpis.total_paid).icon"
                        class="mr-1 size-3"
                    />
                    {{ formatVariation(kpis.total_paid.variation) }}
                </span>
                <span class="text-xs text-muted-foreground"
                    >vs período anterior</span
                >
            </div>
        </div>

        <!-- 3. Saldo em Aberto -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
        >
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg"
                        :class="
                            kpis.total_balance.value > 0
                                ? 'bg-destructive/10 text-destructive'
                                : 'bg-muted text-muted-foreground'
                        "
                    >
                        <Scale class="size-5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">
                            Saldo em Aberto
                        </p>
                        <p
                            class="text-xl font-bold"
                            :class="
                                kpis.total_balance.value > 0
                                    ? 'text-destructive'
                                    : 'text-foreground'
                            "
                        >
                            {{ formatCurrency(kpis.total_balance.value) }}
                        </p>
                    </div>
                </div>
            </div>
            <div
                class="mt-3 flex items-center gap-1.5 border-t border-border/40 pt-3"
            >
                <span
                    class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-semibold"
                    :class="getVariationColor(kpis.total_balance, true).bgClass"
                >
                    <component
                        :is="getVariationColor(kpis.total_balance, true).icon"
                        class="mr-1 size-3"
                    />
                    {{ formatVariation(kpis.total_balance.variation) }}
                </span>
                <span class="text-xs text-muted-foreground"
                    >vs período anterior</span
                >
            </div>
        </div>

        <!-- 4. Número de Pedidos -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
        >
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400"
                    >
                        <ShoppingBag class="size-5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">
                            Número de Pedidos
                        </p>
                        <p class="text-xl font-bold text-foreground">
                            {{
                                kpis.total_orders.value.toLocaleString('pt-BR')
                            }}
                        </p>
                    </div>
                </div>
            </div>
            <div
                class="mt-3 flex items-center gap-1.5 border-t border-border/40 pt-3"
            >
                <span
                    class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-semibold"
                    :class="getVariationColor(kpis.total_orders).bgClass"
                >
                    <component
                        :is="getVariationColor(kpis.total_orders).icon"
                        class="mr-1 size-3"
                    />
                    {{ formatVariation(kpis.total_orders.variation) }}
                </span>
                <span class="text-xs text-muted-foreground"
                    >vs período anterior</span
                >
            </div>
        </div>

        <!-- 5. Ticket Médio -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
        >
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400"
                    >
                        <Receipt class="size-5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">
                            Ticket Médio
                        </p>
                        <p class="text-xl font-bold text-foreground">
                            {{ formatCurrency(kpis.average_ticket.value) }}
                        </p>
                    </div>
                </div>
            </div>
            <div
                class="mt-3 flex items-center gap-1.5 border-t border-border/40 pt-3"
            >
                <span
                    class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-semibold"
                    :class="getVariationColor(kpis.average_ticket).bgClass"
                >
                    <component
                        :is="getVariationColor(kpis.average_ticket).icon"
                        class="mr-1 size-3"
                    />
                    {{ formatVariation(kpis.average_ticket.variation) }}
                </span>
                <span class="text-xs text-muted-foreground"
                    >vs período anterior</span
                >
            </div>
        </div>

        <!-- 6. Clientes Ativos -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
        >
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400"
                    >
                        <Users class="size-5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">
                            Clientes Ativos
                        </p>
                        <p class="text-xl font-bold text-foreground">
                            {{
                                kpis.active_customers.value.toLocaleString(
                                    'pt-BR',
                                )
                            }}
                        </p>
                    </div>
                </div>
            </div>
            <div
                class="mt-3 flex items-center gap-1.5 border-t border-border/40 pt-3"
            >
                <span
                    class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-semibold"
                    :class="getVariationColor(kpis.active_customers).bgClass"
                >
                    <component
                        :is="getVariationColor(kpis.active_customers).icon"
                        class="mr-1 size-3"
                    />
                    {{ formatVariation(kpis.active_customers.variation) }}
                </span>
                <span class="text-xs text-muted-foreground"
                    >vs período anterior</span
                >
            </div>
        </div>

        <!-- 7. Taxa de Inadimplência -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs sm:col-span-2 lg:col-span-3 xl:col-span-2 dark:border-sidebar-border"
        >
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg"
                        :class="
                            kpis.default_rate.value > 0
                                ? 'bg-destructive/10 text-destructive'
                                : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                        "
                    >
                        <ShieldAlert class="size-5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">
                            Taxa de Pedidos com Saldo Aberto
                        </p>
                        <div class="flex items-baseline gap-2">
                            <p
                                class="text-xl font-bold"
                                :class="
                                    kpis.default_rate.value > 0
                                        ? 'text-destructive'
                                        : 'text-emerald-600 dark:text-emerald-400'
                                "
                            >
                                {{ kpis.default_rate.value }}%
                            </p>
                            <span class="text-xs text-muted-foreground"
                                >dos pedidos no período</span
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="mt-3 flex items-center gap-1.5 border-t border-border/40 pt-3"
            >
                <span
                    class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-semibold"
                    :class="getVariationColor(kpis.default_rate, true).bgClass"
                >
                    <component
                        :is="getVariationColor(kpis.default_rate, true).icon"
                        class="mr-1 size-3"
                    />
                    {{ formatVariation(kpis.default_rate.variation) }}
                </span>
                <span class="text-xs text-muted-foreground"
                    >vs período anterior</span
                >
            </div>
        </div>
    </div>
</template>
