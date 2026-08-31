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
import { Trophy } from 'lucide-vue-next';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { formatCurrency } from '@/lib/formatters';
import type { TopCustomerItem } from '../types';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
);

const props = defineProps<{
    data: TopCustomerItem[];
}>();

const chartData = computed<ChartData<'bar'>>(() => {
    const labels = props.data.map((c) =>
        c.name.length > 20 ? c.name.substring(0, 18) + '...' : c.name,
    );
    const values = props.data.map((c) => c.total_amount);

    return {
        labels,
        datasets: [
            {
                label: 'Faturamento',
                data: values,
                backgroundColor: 'rgba(139, 92, 246, 0.75)',
                hoverBackgroundColor: '#8b5cf6',
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
                        const val = Number(context.raw ?? 0);

                        return [
                            ` Faturamento: ${formatCurrency(val)}`,
                            ` Pedidos realizados: ${item ? item.orders_count : 0}`,
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
                    callback: (value) => formatCurrency(Number(value)),
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
                    class="flex size-8 items-center justify-center rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400"
                >
                    <Trophy class="size-4" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-foreground">
                        Top 5 Clientes por Faturamento
                    </h3>
                    <p class="text-xs text-muted-foreground">
                        Clientes com maior volume de compras no período
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
                Nenhum pedido registrado para clientes no período.
            </div>
        </div>
    </div>
</template>
