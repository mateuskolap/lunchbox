<script setup lang="ts">
import { computed } from 'vue';
import { formatCurrency, formatDate } from '@/lib/formatters';

interface HeatmapDay {
    day: number;
    date: string;
    value: number;
    level: number;
}

interface HeatmapMonth {
    year: number;
    month: number;
    name: string;
    firstDayOfWeek: number;
    days: HeatmapDay[];
    monthTotal: number;
}

const props = defineProps<{
    title: string;
    data: Record<string, number>;
    colorScheme?: 'orange' | 'green';
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

const monthsData = computed(() => {
    const entries = Object.entries(props.data);
    const valueMap = new Map(entries.map(([d, v]) => [d, Number(v)]));
    const maxValue = Math.max(...[...valueMap.values()], 1);

    let startYear: number;
    let startMonth: number;
    let endYear: number;
    let endMonth: number;

    if (entries.length > 0) {
        const dates = entries.map(([d]) => new Date(d + 'T00:00:00'));
        const minDate = new Date(Math.min(...dates.map((d) => d.getTime())));
        const maxDate = new Date(Math.max(...dates.map((d) => d.getTime())));

        startYear = minDate.getFullYear();
        startMonth = minDate.getMonth();
        endYear = maxDate.getFullYear();
        endMonth = maxDate.getMonth();
    } else {
        const now = new Date();

        startYear = now.getFullYear();
        startMonth = 0;
        endYear = now.getFullYear();
        endMonth = 11;
    }

    const months: HeatmapMonth[] = [];
    let curYear = startYear;
    let curMonth = startMonth;

    while (
        curYear < endYear ||
        (curYear === endYear && curMonth <= endMonth)
    ) {
        const firstDay = new Date(curYear, curMonth, 1);
        const lastDay = new Date(curYear, curMonth + 1, 0);
        const daysInMonth = lastDay.getDate();

        // Monday-first: 0 = Mon, ..., 6 = Sun
        const firstDayOfWeek = (firstDay.getDay() + 6) % 7;

        const days: HeatmapDay[] = [];
        let monthTotal = 0;

        for (let day = 1; day <= daysInMonth; day++) {
            const monthStr = String(curMonth + 1).padStart(2, '0');
            const dayStr = String(day).padStart(2, '0');
            const dateStr = `${curYear}-${monthStr}-${dayStr}`;
            const value = valueMap.get(dateStr) || 0;

            monthTotal += value;

            const level =
                value === 0
                    ? 0
                    : Math.min(4, Math.ceil((value / maxValue) * 4));

            days.push({
                day,
                date: dateStr,
                value,
                level,
            });
        }

        months.push({
            year: curYear,
            month: curMonth,
            name: `${monthNames[curMonth]}${startYear !== endYear ? ` ${curYear}` : ''}`,
            firstDayOfWeek,
            days,
            monthTotal,
        });

        curMonth++;

        if (curMonth > 11) {
            curMonth = 0;
            curYear++;
        }
    }

    const totalValue = Array.from(valueMap.values()).reduce(
        (a, b) => a + b,
        0,
    );

    return {
        months,
        totalValue,
    };
});

function getCellClass(level: number): string {
    const scheme = props.colorScheme || 'orange';
    const colors: Record<string, string[]> = {
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

    return colors[scheme]?.[level] ?? colors.orange[level];
}
</script>

<template>
    <div
        class="flex flex-col rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
    >
        <!-- Header: Title, Total & Legend -->
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-medium text-muted-foreground">
                    {{ title }}
                </h3>
                <p class="text-lg font-bold text-foreground">
                    {{ formatCurrency(monthsData.totalValue) }}
                </p>
            </div>

            <!-- Legend -->
            <div
                class="flex items-center gap-1.5 text-xs text-muted-foreground"
            >
                <span class="text-[11px]">Menor</span>
                <div
                    v-for="level in [0, 1, 2, 3, 4]"
                    :key="level"
                    class="size-3 rounded-[3px]"
                    :class="getCellClass(level)"
                />
                <span class="text-[11px]">Maior</span>
            </div>
        </div>

        <!-- Months Container: Responsive Grid with Horizontal Scroll support -->
        <div class="overflow-x-auto pb-1">
            <div
                class="grid grid-cols-[repeat(auto-fill,minmax(210px,1fr))] gap-4 min-w-0"
            >
                <div
                    v-for="month in monthsData.months"
                    :key="`${month.year}-${month.month}`"
                    class="flex flex-col rounded-lg border border-sidebar-border/60 bg-background/50 p-3.5 dark:border-sidebar-border/40 dark:bg-background/20"
                >
                    <!-- Month Title & Subtotal -->
                    <div class="mb-2.5 flex items-center justify-between">
                        <span class="text-xs font-semibold text-foreground">
                            {{ month.name }}
                        </span>
                        <span
                            v-if="month.monthTotal > 0"
                            class="text-[11px] font-medium text-muted-foreground"
                        >
                            {{ formatCurrency(month.monthTotal) }}
                        </span>
                    </div>

                    <!-- Weekdays Header (Seg -> Dom) -->
                    <div class="mb-1.5 grid grid-cols-7 gap-1 text-center">
                        <span
                            v-for="(label, i) in dayLabels"
                            :key="i"
                            class="text-[10px] font-medium text-muted-foreground"
                        >
                            {{ label }}
                        </span>
                    </div>

                    <!-- Days Grid (Left -> Right, Top -> Bottom) -->
                    <div class="grid grid-cols-7 gap-1">
                        <!-- Blanks before 1st day of month -->
                        <div
                            v-for="blank in month.firstDayOfWeek"
                            :key="`blank-${blank}`"
                            class="aspect-square"
                        />

                        <!-- Day Cells -->
                        <div
                            v-for="cell in month.days"
                            :key="cell.date"
                            class="group relative flex aspect-square cursor-default items-center justify-center rounded-[4px] text-[10px] font-medium transition-transform duration-100 hover:scale-125 hover:shadow-xs"
                            :class="getCellClass(cell.level)"
                            :title="`${formatDate(cell.date)}: ${formatCurrency(cell.value)}`"
                        >
                            <span>{{ cell.day }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
