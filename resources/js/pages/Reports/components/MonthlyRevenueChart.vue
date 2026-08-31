<script setup lang="ts">
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
    
} from 'chart.js';
import type {TooltipItem} from 'chart.js';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import { formatCurrency } from '@/lib/formatters';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
);

const props = defineProps<{
    revenue: Record<string, number>;
    payments: Record<string, number>;
}>();

const monthNames = [
    'Jan',
    'Fev',
    'Mar',
    'Abr',
    'Mai',
    'Jun',
    'Jul',
    'Ago',
    'Set',
    'Out',
    'Nov',
    'Dez',
];

const chartData = computed(() => {
    const allMonths = [
        ...new Set([
            ...Object.keys(props.revenue),
            ...Object.keys(props.payments),
        ]),
    ].sort();

    return {
        labels: allMonths.map((m) => {
            const [, month] = m.split('-');

            return monthNames[parseInt(month) - 1] || m;
        }),
        datasets: [
            {
                label: 'Faturamento',
                data: allMonths.map((m) => Number(props.revenue[m] || 0)),
                borderColor: 'hsl(16, 75%, 55%)',
                backgroundColor: 'hsla(16, 75%, 55%, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointHoverRadius: 6,
            },
            {
                label: 'Pagamentos',
                data: allMonths.map((m) => Number(props.payments[m] || 0)),
                borderColor: 'hsl(145, 45%, 42%)',
                backgroundColor: 'hsla(145, 45%, 42%, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointHoverRadius: 6,
            },
        ],
    };
});

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index' as const, intersect: false },
    plugins: {
        legend: { position: 'top' as const },
        tooltip: {
            callbacks: {
                label: (ctx: TooltipItem<'line'>) =>
                    `${ctx.dataset.label ?? ''}: ${formatCurrency(ctx.raw as number)}`,
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value: string | number) =>
                    formatCurrency(Number(value)),
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
            Faturamento × Pagamentos (Mês a Mês)
        </h3>
        <div class="h-[350px]">
            <Line :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
