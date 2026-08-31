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
import { CalendarRange } from 'lucide-vue-next';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { formatCurrency } from '@/lib/formatters';
import type { WeekdaySalesItem } from '../types';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
);

const props = defineProps<{
    data: WeekdaySalesItem[];
}>();

const chartData = computed<ChartData<'bar'>>(() => {
    const labels = props.data.map((item) => item.short);
    const values = props.data.map((item) => item.total);

    return {
        labels,
        datasets: [
            {
                label: 'Faturamento',
                data: values,
                backgroundColor: 'rgba(234, 88, 12, 0.75)',
                hoverBackgroundColor: '#ea580c',
                borderRadius: 6,
                borderSkipped: false,
            },
        ],
    };
});

const chartOptions = computed<ChartOptions<'bar'>>(() => {
    return {
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

                        return props.data[idx]?.day || items[0].label;
                    },
                    label: (context) => {
                        const idx = context.dataIndex;
                        const item = props.data[idx];
                        const val = Number(context.raw ?? 0);

                        return [
                            ` Faturamento: ${formatCurrency(val)}`,
                            ` Pedidos: ${item ? item.count : 0}`,
                        ];
                    },
                },
            },
        },
        scales: {
            x: {
                grid: {
                    display: false,
                },
                ticks: {
                    color: '#888888',
                    font: {
                        family: 'Instrument Sans, sans-serif',
                        size: 11,
                    },
                },
            },
            y: {
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
                    class="flex size-8 items-center justify-center rounded-lg bg-orange-500/10 text-orange-600 dark:text-orange-400"
                >
                    <CalendarRange class="size-4" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-foreground">
                        Vendas por Dia da Semana
                    </h3>
                    <p class="text-xs text-muted-foreground">
                        Volume de faturamento de Seg a Dom
                    </p>
                </div>
            </div>
        </div>

        <div class="relative h-[280px] w-full">
            <Bar
                v-if="data.some((d) => d.total > 0)"
                :data="chartData"
                :options="chartOptions"
            />
            <div
                v-else
                class="flex h-full items-center justify-center text-sm text-muted-foreground"
            >
                Nenhum dado encontrado para o período selecionado.
            </div>
        </div>
    </div>
</template>
