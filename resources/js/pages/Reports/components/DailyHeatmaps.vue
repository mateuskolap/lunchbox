<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import AppSelect from '@/components/AppSelect.vue';
import { Label } from '@/components/ui/label';
import { formatCurrency, formatDate } from '@/lib/formatters';

interface HeatmapDay {
    day: number;
    date: string;
    value: number;
    level: number;
}

const props = defineProps<{
    dailyRevenue: Record<string, number>;
    dailyPayments: Record<string, number>;
}>();

const monthNames = [
    'Janeiro',
    'Fevereiro',
    'Março',
    'Abril',
    'Maio',
    'Junho',
    'Julho',
    'Agosto',
    'Setembro',
    'Outubro',
    'Novembro',
    'Dezembro',
];

const dayLabels = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];

// Obter somente os meses (YYYY-MM) que possuem algum valor em faturamento ou pagamentos
const availableMonths = computed(() => {
    const monthsSet = new Set<string>();

    for (const [date, val] of Object.entries(props.dailyRevenue)) {
        if (Number(val) > 0) {
            monthsSet.add(date.substring(0, 7));
        }
    }

    for (const [date, val] of Object.entries(props.dailyPayments)) {
        if (Number(val) > 0) {
            monthsSet.add(date.substring(0, 7));
        }
    }

    const sorted = Array.from(monthsSet).sort();

    return sorted.map((ym) => {
        const [year, month] = ym.split('-');
        const monthIndex = parseInt(month, 10) - 1;

        return {
            value: ym,
            label: `${monthNames[monthIndex]} / ${year}`,
        };
    });
});

const monthOptions = computed(() => {
    if (availableMonths.value.length > 0) {
        return availableMonths.value;
    }

    const now = new Date();
    const ym = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;

    return [
        {
            value: ym,
            label: `${monthNames[now.getMonth()]} / ${now.getFullYear()}`,
        },
    ];
});

const selectedMonth = ref<string>('');

watch(
    monthOptions,
    (options) => {
        if (
            options.length > 0 &&
            (!selectedMonth.value ||
                !options.some((o) => o.value === selectedMonth.value))
        ) {
            selectedMonth.value = options[options.length - 1].value;
        }
    },
    { immediate: true },
);

function buildMonthHeatmap(
    dataMap: Record<string, number>,
    selectedYM: string,
) {
    if (!selectedYM) {
        return {
            firstDayOfWeek: 0,
            days: [] as HeatmapDay[],
            total: 0,
            maxValue: 1,
        };
    }

    const [yearStr, monthStr] = selectedYM.split('-');
    const year = parseInt(yearStr, 10);
    const monthIndex = parseInt(monthStr, 10) - 1;

    const firstDay = new Date(year, monthIndex, 1);
    const lastDay = new Date(year, monthIndex + 1, 0);
    const daysInMonth = lastDay.getDate();

    // Monday-first: 0 = Seg, ..., 6 = Dom
    const firstDayOfWeek = (firstDay.getDay() + 6) % 7;

    const valueMap = new Map<string, number>();
    let monthTotal = 0;
    let monthMax = 0;

    for (let day = 1; day <= daysInMonth; day++) {
        const dStr = String(day).padStart(2, '0');
        const dateStr = `${year}-${String(monthIndex + 1).padStart(2, '0')}-${dStr}`;
        const val = Number(dataMap[dateStr] || 0);

        valueMap.set(dateStr, val);
        monthTotal += val;

        if (val > monthMax) {
            monthMax = val;
        }
    }

    const maxValue = Math.max(monthMax, 1);
    const days: HeatmapDay[] = [];

    for (let day = 1; day <= daysInMonth; day++) {
        const dStr = String(day).padStart(2, '0');
        const dateStr = `${year}-${String(monthIndex + 1).padStart(2, '0')}-${dStr}`;
        const val = valueMap.get(dateStr) || 0;
        const level =
            val === 0 ? 0 : Math.min(4, Math.ceil((val / maxValue) * 4));

        days.push({
            day,
            date: dateStr,
            value: val,
            level,
        });
    }

    return {
        firstDayOfWeek,
        days,
        total: monthTotal,
        maxValue,
    };
}

const revenueHeatmap = computed(() =>
    buildMonthHeatmap(props.dailyRevenue, selectedMonth.value),
);

const paymentsHeatmap = computed(() =>
    buildMonthHeatmap(props.dailyPayments, selectedMonth.value),
);

function getCellClass(level: number, scheme: 'orange' | 'green'): string {
    const colors: Record<'orange' | 'green', string[]> = {
        orange: [
            'bg-muted/70 text-muted-foreground/80 hover:bg-muted dark:bg-muted/40 dark:text-muted-foreground/60',
            'bg-orange-100 text-orange-950 dark:bg-orange-950/60 dark:text-orange-200',
            'bg-orange-200 text-orange-950 dark:bg-orange-800/70 dark:text-orange-100',
            'bg-orange-400 text-white dark:bg-orange-600 dark:text-white',
            'bg-orange-500 text-white font-bold dark:bg-orange-500 dark:text-white',
        ],
        green: [
            'bg-muted/70 text-muted-foreground/80 hover:bg-muted dark:bg-muted/40 dark:text-muted-foreground/60',
            'bg-emerald-100 text-emerald-950 dark:bg-emerald-950/60 dark:text-emerald-200',
            'bg-emerald-200 text-emerald-950 dark:bg-emerald-800/70 dark:text-emerald-100',
            'bg-emerald-400 text-white dark:bg-emerald-600 dark:text-white',
            'bg-emerald-500 text-white font-bold dark:bg-emerald-500 dark:text-white',
        ],
    };

    return colors[scheme][level] ?? colors[scheme][0];
}
</script>

<template>
    <div
        class="flex flex-col gap-5 rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
    >
        <!-- Header: Seletor de Mês -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-semibold text-foreground">
                    Mapeamento Diário
                </h3>
                <p class="text-xs text-muted-foreground">
                    Visualização diária de faturamento e pagamentos por mês.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <Label class="text-xs text-muted-foreground">Mês:</Label>
                <div class="w-[180px]">
                    <AppSelect
                        v-model="selectedMonth"
                        :options="monthOptions"
                        placeholder="Selecione o mês"
                    />
                </div>
            </div>
        </div>

        <!-- Heatmaps Lado a Lado com Tamanho Fixo -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Heatmap Faturamento -->
            <div
                class="flex flex-col rounded-lg border border-sidebar-border/60 bg-background/50 p-4 dark:border-sidebar-border/40 dark:bg-background/20"
            >
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-semibold text-foreground">
                            Faturamento Diário
                        </h4>
                        <p class="text-sm font-bold text-orange-600 dark:text-orange-400">
                            {{ formatCurrency(revenueHeatmap.total) }}
                        </p>
                    </div>

                    <!-- Legenda Laranja -->
                    <div
                        class="flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <span class="text-[10px]">Menor</span>
                        <div
                            v-for="level in [0, 1, 2, 3, 4]"
                            :key="level"
                            class="size-2.5 rounded-[2px]"
                            :class="getCellClass(level, 'orange')"
                        />
                        <span class="text-[10px]">Maior</span>
                    </div>
                </div>

                <!-- Dias da semana -->
                <div class="mb-1.5 grid grid-cols-7 gap-1 text-center">
                    <span
                        v-for="(label, i) in dayLabels"
                        :key="i"
                        class="text-[11px] font-medium text-muted-foreground"
                    >
                        {{ label }}
                    </span>
                </div>

                <!-- Grade de Dias -->
                <div class="grid grid-cols-7 gap-1">
                    <div
                        v-for="blank in revenueHeatmap.firstDayOfWeek"
                        :key="`rev-blank-${blank}`"
                        class="aspect-[16/9]"
                    />

                    <div
                        v-for="cell in revenueHeatmap.days"
                        :key="`rev-${cell.date}`"
                        class="group relative flex aspect-[16/9] cursor-default items-center justify-center rounded-md text-xs font-semibold transition-transform duration-100 hover:scale-105 hover:shadow-xs"
                        :class="getCellClass(cell.level, 'orange')"
                        :title="`${formatDate(cell.date)}: ${formatCurrency(cell.value)}`"
                    >
                        <span>{{ cell.day }}</span>
                    </div>
                </div>
            </div>

            <!-- Heatmap Pagamentos -->
            <div
                class="flex flex-col rounded-lg border border-sidebar-border/60 bg-background/50 p-4 dark:border-sidebar-border/40 dark:bg-background/20"
            >
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-semibold text-foreground">
                            Pagamentos Diários
                        </h4>
                        <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                            {{ formatCurrency(paymentsHeatmap.total) }}
                        </p>
                    </div>

                    <!-- Legenda Verde -->
                    <div
                        class="flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <span class="text-[10px]">Menor</span>
                        <div
                            v-for="level in [0, 1, 2, 3, 4]"
                            :key="level"
                            class="size-2.5 rounded-[2px]"
                            :class="getCellClass(level, 'green')"
                        />
                        <span class="text-[10px]">Maior</span>
                    </div>
                </div>

                <!-- Dias da semana -->
                <div class="mb-1.5 grid grid-cols-7 gap-1 text-center">
                    <span
                        v-for="(label, i) in dayLabels"
                        :key="i"
                        class="text-[11px] font-medium text-muted-foreground"
                    >
                        {{ label }}
                    </span>
                </div>

                <!-- Grade de Dias -->
                <div class="grid grid-cols-7 gap-1">
                    <div
                        v-for="blank in paymentsHeatmap.firstDayOfWeek"
                        :key="`pay-blank-${blank}`"
                        class="aspect-[16/9]"
                    />

                    <div
                        v-for="cell in paymentsHeatmap.days"
                        :key="`pay-${cell.date}`"
                        class="group relative flex aspect-[16/9] cursor-default items-center justify-center rounded-md text-xs font-semibold transition-transform duration-100 hover:scale-105 hover:shadow-xs"
                        :class="getCellClass(cell.level, 'green')"
                        :title="`${formatDate(cell.date)}: ${formatCurrency(cell.value)}`"
                    >
                        <span>{{ cell.day }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
