<script setup lang="ts">
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import type { ChartOptions, ChartData } from 'chart.js';
import { PackageCheck } from 'lucide-vue-next';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { formatCurrency } from '@/lib/formatters';
import type { TopProductItem } from '../types';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
);

const props = defineProps<{
    data: TopProductItem[];
}>();

const chartData = computed<ChartData<'bar'>>(() => {
    const labels = props.data.map((p) =>
        p.name.length > 20 ? p.name.substring(0, 18) + '...' : p.name,
    );
    const values = props.data.map((p) => p.quantity);

    return {
        labels,
        datasets: [
            {
                label: 'Qtd. Vendida',
                data: values,
                backgroundColor: 'rgba(16, 185, 129, 0.75)',
                hoverBackgroundColor: '#10b981',
                borderRadius: 6,
                borderSkipped: false,
            },
        ],
    };
});

const chartOptions = computed<ChartOptions<'bar'>>(() => {
    return {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false,
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
                    title: (items) => {
                        if (!items.length) {
                            return '';
                        }

                        const idx = items[0].dataIndex;

                        return props.data[idx]?.name || items[0].label;
                    },
                    label: (context) => {
                        const idx = context.dataIndex;
                        const item = props.data[idx];
                        const qty = Number(context.raw ?? 0);

                        return [
                            ` Quantidade vendida: ${qty}`,
                            ` Receita gerada: ${formatCurrency(item ? item.revenue : 0)}`,
                        ];
                    },
                },
            },
        },
        scales: {
            x: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(150, 150, 150, 0.12)',
                },
                ticks: {
                    color: '#888888',
                    font: {
                        family: 'Instrument Sans, sans-serif',
                        size: 11,
                    },
                    stepSize: 1,
                },
            },
            y: {
                grid: {
                    display: false,
                },
                ticks: {
                    color: 'currentColor',
                    font: {
                        family: 'Instrument Sans, sans-serif',
                        size: 11,
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
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div
                    class="flex size-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                >
                    <PackageCheck class="size-4" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-foreground">
                        Top 5 Produtos Mais Vendidos
                    </h3>
                    <p class="text-xs text-muted-foreground">
                        Ranking por quantidade no período
                    </p>
                </div>
            </div>
        </div>

        <div class="relative h-[280px] w-full">
            <Bar
                v-if="data.length > 0"
                :data="chartData"
                :options="chartOptions"
            />
            <div
                v-else
                class="flex h-full items-center justify-center text-sm text-muted-foreground"
            >
                Nenhum produto vendido no período.
            </div>
        </div>
    </div>
</template>
