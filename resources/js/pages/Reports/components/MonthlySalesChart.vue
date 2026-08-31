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
    Filler,
} from 'chart.js';
import type { ChartOptions, ChartData } from 'chart.js';
import { LineChart as LineChartIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import { formatCurrency } from '@/lib/formatters';
import type { MonthlySalesItem } from '../types';

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
    data: MonthlySalesItem[];
}>();

const chartData = computed<ChartData<'line'>>(() => {
    const labels = props.data.map((item) => item.label);
    const salesValues = props.data.map((item) => item.total_sales);
    const paidValues = props.data.map((item) => item.total_paid);

    return {
        labels,
        datasets: [
            {
                label: 'Total Vendido',
                data: salesValues,
                borderColor: '#ea580c',
                backgroundColor: 'rgba(234, 88, 12, 0.12)',
                borderWidth: 2.5,
                tension: 0.35,
                fill: true,
                pointBackgroundColor: '#ea580c',
                pointBorderColor: '#ffffff',
                pointHoverRadius: 6,
                pointRadius: 4,
            },
            {
                label: 'Total Recebido',
                data: paidValues,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.08)',
                borderWidth: 2.5,
                tension: 0.35,
                fill: true,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#ffffff',
                pointHoverRadius: 6,
                pointRadius: 4,
            },
        ],
    };
});

const chartOptions = computed<ChartOptions<'line'>>(() => {
    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                position: 'top',
                align: 'end',
                labels: {
                    usePointStyle: true,
                    boxWidth: 8,
                    boxHeight: 8,
                    font: {
                        family: 'Instrument Sans, sans-serif',
                        size: 12,
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
                padding: 12,
                cornerRadius: 8,
                callbacks: {
                    label: (context) => {
                        const val = context.parsed.y ?? 0;

                        return ` ${context.dataset.label}: ${formatCurrency(val)}`;
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
                    class="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary"
                >
                    <LineChartIcon class="size-4" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-foreground">
                        Vendas Mês a Mês
                    </h3>
                    <p class="text-xs text-muted-foreground">
                        Comparativo de total vendido e total recebido por mês
                    </p>
                </div>
            </div>
        </div>

        <div class="relative h-[300px] w-full">
            <Line
                v-if="data.length > 0"
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
