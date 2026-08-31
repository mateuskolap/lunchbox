<script setup lang="ts">
import { computed } from 'vue';
import { formatCurrency } from '@/lib/formatters';

interface HeatmapCell {
    date: string;
    day: number;
    value: number;
    level: number;
}

interface HeatmapMonthLabel {
    label: string;
    col: number;
}

const props = defineProps<{
    title: string;
    data: Record<string, number>;
    colorScheme?: 'orange' | 'green';
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

const dayLabels = ['Seg', '', 'Qua', '', 'Sex', '', ''];

const heatmapData = computed(() => {
    const entries = Object.entries(props.data);

    if (entries.length === 0) {
        return {
            weeks: [] as (HeatmapCell | null)[][],
            months: [] as HeatmapMonthLabel[],
            maxValue: 0,
        };
    }

    const valueMap = new Map(entries.map(([d, v]) => [d, Number(v)]));
    const dates = entries.map(([d]) => new Date(d + 'T00:00:00'));
    const minDate = new Date(Math.min(...dates.map((d) => d.getTime())));
    const maxDate = new Date(Math.max(...dates.map((d) => d.getTime())));

    // Start from Monday of the week containing minDate
    const startDate = new Date(minDate);
    startDate.setDate(
        startDate.getDate() - ((startDate.getDay() + 6) % 7),
    );

    // End at Sunday of the week containing maxDate
    const endDate = new Date(maxDate);
    const endDayOffset = (7 - ((endDate.getDay() + 6) % 7)) % 7;

    if (endDayOffset > 0) {
        endDate.setDate(endDate.getDate() + endDayOffset);
    }

    const maxValue = Math.max(...[...valueMap.values()], 1);

    const weeks: (HeatmapCell | null)[][] = [];
    const months: HeatmapMonthLabel[] = [];

    const current = new Date(startDate);
    let weekIndex = -1;
    let lastMonth = -1;

    while (current <= endDate) {
        const dayOfWeek = (current.getDay() + 6) % 7; // 0=Mon, 6=Sun

        if (dayOfWeek === 0) {
            weekIndex++;
            weeks.push(new Array(7).fill(null));
        }

        const dateStr = current.toISOString().split('T')[0];
        const value = valueMap.get(dateStr) || 0;
        const level =
            value === 0
                ? 0
                : Math.min(4, Math.ceil((value / maxValue) * 4));
        const day = current.getDate();
        const month = current.getMonth();

        if (month !== lastMonth && weekIndex >= 0) {
            months.push({ label: monthNames[month], col: weekIndex });
            lastMonth = month;
        }

        if (weekIndex >= 0 && weeks[weekIndex]) {
            weeks[weekIndex][dayOfWeek] = {
                date: dateStr,
                day,
                value,
                level,
            };
        }

        current.setDate(current.getDate() + 1);
    }

    return { weeks, months, maxValue };
});

function getCellClass(level: number): string {
    const scheme = props.colorScheme || 'orange';
    const colors: Record<string, string[]> = {
        orange: [
            'bg-muted',
            'bg-orange-200 dark:bg-orange-900/40',
            'bg-orange-300 dark:bg-orange-700/60',
            'bg-orange-400 dark:bg-orange-600/80',
            'bg-orange-500 dark:bg-orange-500',
        ],
        green: [
            'bg-muted',
            'bg-emerald-200 dark:bg-emerald-900/40',
            'bg-emerald-300 dark:bg-emerald-700/60',
            'bg-emerald-400 dark:bg-emerald-600/80',
            'bg-emerald-500 dark:bg-emerald-500',
        ],
    };

    return colors[scheme]?.[level] ?? colors.orange[level];
}
</script>

<template>
    <div
        class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
    >
        <h3 class="mb-4 text-sm font-medium text-muted-foreground">
            {{ title }}
        </h3>
        <div class="overflow-x-auto pb-2">
            <div class="relative inline-flex min-w-max flex-col">
                <!-- Month labels -->
                <div class="mb-1 flex" style="padding-left: 30px">
                    <template
                        v-for="(month, i) in heatmapData.months"
                        :key="i"
                    >
                        <div
                            class="absolute text-[10px] text-muted-foreground"
                            :style="{
                                left: `${30 + month.col * 18}px`,
                                top: '0px',
                            }"
                        >
                            {{ month.label }}
                        </div>
                    </template>
                </div>

                <div class="mt-4 flex gap-[2px]">
                    <!-- Day-of-week labels -->
                    <div class="flex shrink-0 flex-col gap-[2px] pr-1">
                        <div
                            v-for="(label, i) in dayLabels"
                            :key="i"
                            class="flex h-4 w-5 items-center text-[10px] text-muted-foreground"
                        >
                            {{ label }}
                        </div>
                    </div>

                    <!-- Weeks grid -->
                    <div
                        v-for="(week, wi) in heatmapData.weeks"
                        :key="wi"
                        class="flex flex-col gap-[2px]"
                    >
                        <div
                            v-for="(cell, di) in week"
                            :key="di"
                            class="group relative flex size-4 items-center justify-center rounded-[3px] text-[7px] font-medium leading-none transition-transform hover:scale-125"
                            :class="
                                cell
                                    ? getCellClass(cell.level)
                                    : 'bg-transparent'
                            "
                            :title="
                                cell
                                    ? `${cell.date}: ${formatCurrency(cell.value)}`
                                    : ''
                            "
                        >
                            <span
                                v-if="cell"
                                class="select-none"
                                :class="
                                    cell.level >= 3
                                        ? 'text-white dark:text-white'
                                        : 'text-muted-foreground'
                                "
                            >
                                {{ cell.day }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div
                    class="mt-3 flex items-center gap-1.5 text-[10px] text-muted-foreground"
                >
                    <span>Menor</span>
                    <div
                        v-for="level in [0, 1, 2, 3, 4]"
                        :key="level"
                        class="size-3 rounded-[2px]"
                        :class="getCellClass(level)"
                    />
                    <span>Maior</span>
                </div>
            </div>
        </div>
    </div>
</template>
