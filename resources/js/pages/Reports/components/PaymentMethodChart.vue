<script setup lang="ts">
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import type { TooltipItem } from 'chart.js';
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { formatCurrency } from '@/lib/formatters';
import type { PaymentByMethodItem } from '@/types';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps<{
    data: PaymentByMethodItem[];
}>();

const methodLabels: Record<string, string> = {
    cash: 'Dinheiro',
    pix: 'PIX',
    credit_card: 'Cartão de Crédito',
    debit_card: 'Cartão de Débito',
    food_voucher: 'Vale-Refeição',
    other: 'Outro',
};

const chartColors = [
    'hsl(16, 75%, 55%)',
    'hsl(145, 45%, 42%)',
    'hsl(40, 70%, 55%)',
    'hsl(0, 60%, 50%)',
    'hsl(25, 80%, 60%)',
    'hsl(200, 60%, 45%)',
];

const chartData = computed(() => ({
    labels: props.data.map((d) => methodLabels[d.method] || d.method),
    datasets: [
        {
            data: props.data.map((d) => Number(d.total)),
            backgroundColor: chartColors.slice(0, props.data.length),
            borderWidth: 2,
            borderColor: 'hsl(30, 33%, 97%)',
        },
    ],
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    cutout: '60%',
    plugins: {
        legend: { position: 'right' as const },
        tooltip: {
            callbacks: {
                label: (ctx: TooltipItem<'doughnut'>) => {
                    const data = ctx.dataset.data as number[];
                    const total = data.reduce(
                        (a: number, b: number) => a + b,
                        0,
                    );
                    const value = ctx.raw as number;
                    const pct = ((value / total) * 100).toFixed(1);

                    return `${ctx.label}: ${formatCurrency(value)} (${pct}%)`;
                },
            },
        },
    },
}));
</script>

<template>
    <div
        class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
    >
        <h3 class="mb-4 text-sm font-medium text-muted-foreground">
            Distribuição por Método de Pagamento
        </h3>
        <div class="h-[300px]">
            <Doughnut
                v-if="data.length > 0"
                :data="chartData"
                :options="chartOptions"
            />
            <div
                v-else
                class="flex h-full items-center justify-center text-sm text-muted-foreground"
            >
                Nenhum pagamento no período
            </div>
        </div>
    </div>
</template>
