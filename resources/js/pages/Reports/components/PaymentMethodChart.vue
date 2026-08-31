<script setup lang="ts">
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import type { ChartOptions, ChartData } from 'chart.js';
import { PieChart as PieChartIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { formatCurrency } from '@/lib/formatters';
import type { PaymentMethodItem } from '../types';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps<{
    data: PaymentMethodItem[];
    totalValue: number;
}>();

const methodColors: Record<string, string> = {
    pix: '#06b6d4',
    cash: '#10b981',
    credit_card: '#8b5cf6',
    debit_card: '#3b82f6',
    food_voucher: '#f59e0b',
    other: '#6b7280',
};

const chartData = computed<ChartData<'doughnut'>>(() => {
    const labels = props.data.map((item) => item.label);
    const values = props.data.map((item) => item.total_value);
    const backgroundColors = props.data.map(
        (item) => methodColors[item.method] || '#94a3b8',
    );

    return {
        labels,
        datasets: [
            {
                data: values,
                backgroundColor: backgroundColors,
                borderWidth: 2,
                borderColor: 'var(--color-card, #ffffff)',
                hoverOffset: 6,
            },
        ],
    };
});

const chartOptions = computed<ChartOptions<'doughnut'>>(() => {
    return {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    boxWidth: 8,
                    boxHeight: 8,
                    padding: 14,
                    font: {
                        family: 'Instrument Sans, sans-serif',
                        size: 11,
                    },
                    color: 'currentColor',
                },
            },
            tooltip: {
                backgroundColor: 'rgba(20, 20, 25, 0.92)',
                titleFont: {
                    family: 'Instrument Sans, sans-serif',
                    size: 13,
                    weight: 'bold',
                },
                bodyFont: {
                    family: 'Instrument Sans, sans-serif',
                    size: 12,
                },
                padding: 10,
                cornerRadius: 8,
                callbacks: {
                    label: (context) => {
                        const val = Number(context.raw ?? 0);
                        const total = props.totalValue || 1;
                        const pct = ((val / total) * 100).toFixed(1);

                        return ` ${context.label}: ${formatCurrency(val)} (${pct}%)`;
                    },
                },
            },
        },
    };
});
</script>

<template>
    <div
        class="flex flex-col rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
    >
        <div class="mb-2 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div
                    class="flex size-8 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-600 dark:text-cyan-400"
                >
                    <PieChartIcon class="size-4" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-foreground">
                        Distribuição por Pagamento
                    </h3>
                    <p class="text-xs text-muted-foreground">
                        Fatia recebida por cada método
                    </p>
                </div>
            </div>
            <span class="text-xs font-semibold text-muted-foreground">
                Total: {{ formatCurrency(totalValue) }}
            </span>
        </div>

        <div class="relative flex h-[300px] w-full items-center justify-center">
            <Doughnut
                v-if="data.length > 0 && totalValue > 0"
                :data="chartData"
                :options="chartOptions"
            />
            <div
                v-else
                class="flex h-full items-center justify-center text-sm text-muted-foreground"
            >
                Nenhum pagamento registrado no período.
            </div>
        </div>
    </div>
</template>
