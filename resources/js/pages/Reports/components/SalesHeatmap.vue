<script setup lang="ts">
import { CalendarDays, Flame } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { formatCurrency, formatDate } from '@/lib/formatters';
import type { HeatmapDayItem } from '../types';

const props = defineProps<{
    days: HeatmapDayItem[];
}>();

const hoveredDay = ref<HeatmapDayItem | null>(null);
const tooltipPosition = ref({ x: 0, y: 0 });

interface WeekColumn {
    days: (HeatmapDayItem | null)[]; // 7 days (index 0=Sunday to 6=Saturday)
    monthLabel?: string;
}

const weekdayLabels = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
const monthNamesShort = [
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

const maxDailySales = computed(() => {
    if (!props.days.length) {
        return 0;
    }

    return Math.max(...props.days.map((d) => d.total));
});

const totalSalesInPeriod = computed(() => {
    return props.days.reduce((acc, d) => acc + d.total, 0);
});

const bestDay = computed(() => {
    if (!props.days.length || maxDailySales.value === 0) {
        return null;
    }

    return props.days.reduce(
        (best, cur) => (cur.total > (best?.total ?? 0) ? cur : best),
        props.days[0],
    );
});

const activeDaysCount = computed(() => {
    return props.days.filter((d) => d.count > 0).length;
});

const averageActiveDailySales = computed(() => {
    if (activeDaysCount.value === 0) {
        return 0;
    }

    return totalSalesInPeriod.value / activeDaysCount.value;
});

function getIntensityLevel(total: number): number {
    if (total <= 0 || maxDailySales.value <= 0) {
        return 0;
    }

    const ratio = total / maxDailySales.value;

    if (ratio <= 0.25) {
        return 1;
    }

    if (ratio <= 0.5) {
        return 2;
    }

    if (ratio <= 0.75) {
        return 3;
    }

    return 4;
}

function getCellColorClass(level: number): string {
    switch (level) {
        case 1:
            return 'bg-emerald-200 dark:bg-emerald-950 border-emerald-300 dark:border-emerald-900';
        case 2:
            return 'bg-emerald-400 dark:bg-emerald-800 border-emerald-500 dark:border-emerald-700';
        case 3:
            return 'bg-emerald-600 dark:bg-emerald-600 border-emerald-700 dark:border-emerald-500';
        case 4:
            return 'bg-emerald-700 dark:bg-emerald-400 border-emerald-800 dark:border-emerald-300';
        default:
            return 'bg-muted/40 dark:bg-muted/20 border-border/40';
    }
}

// Build 7-row columns matrix
const calendarWeeks = computed<WeekColumn[]>(() => {
    if (!props.days.length) {
        return [];
    }

    const weeks: WeekColumn[] = [];
    let currentWeekDays: (HeatmapDayItem | null)[] = Array(7).fill(null);
    let lastMonth = -1;

    props.days.forEach((day, index) => {
        const dateObj = new Date(`${day.date}T12:00:00`);
        const dayOfWeek = dateObj.getDay(); // 0 = Sunday, 6 = Saturday
        const month = dateObj.getMonth();

        currentWeekDays[dayOfWeek] = day;

        // If it's Saturday or last day of data, push column
        if (dayOfWeek === 6 || index === props.days.length - 1) {
            let monthLabel: string | undefined = undefined;

            // Check if a new month starts in this week column
            if (month !== lastMonth) {
                monthLabel = monthNamesShort[month];
                lastMonth = month;
            }

            weeks.push({
                days: [...currentWeekDays],
                monthLabel,
            });
            currentWeekDays = Array(7).fill(null);
        }
    });

    return weeks;
});

function handleCellMouseEnter(day: HeatmapDayItem | null, event: MouseEvent) {
    if (!day) {
        return;
    }

    hoveredDay.value = day;
    const target = event.currentTarget as HTMLElement;
    const rect = target.getBoundingClientRect();
    tooltipPosition.value = {
        x: rect.left + rect.width / 2,
        y: rect.top - 8,
    };
}

function handleCellMouseLeave() {
    hoveredDay.value = null;
}

function formatDayOfWeekPt(dateStr: string): string {
    const d = new Date(`${dateStr}T12:00:00`);
    const weekdays = [
        'Domingo',
        'Segunda-feira',
        'Terça-feira',
        'Quarta-feira',
        'Quinta-feira',
        'Sexta-feira',
        'Sábado',
    ];

    return weekdays[d.getDay()];
}
</script>

<template>
    <div
        class="flex flex-col rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
    >
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <div
                    class="flex size-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                >
                    <CalendarDays class="size-4" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-foreground">
                        Mapa de Calor de Vendas por Dia
                    </h3>
                    <p class="text-xs text-muted-foreground">
                        Distribuição e intensidade do faturamento diário no
                        período
                    </p>
                </div>
            </div>

            <!-- Quick stats tags -->
            <div
                class="flex flex-wrap items-center gap-3 text-xs text-muted-foreground"
            >
                <div
                    v-if="bestDay && bestDay.total > 0"
                    class="flex items-center gap-1.5 rounded-lg bg-amber-500/10 px-2.5 py-1 font-medium text-amber-700 dark:text-amber-300"
                >
                    <Flame class="size-3.5" />
                    <span
                        >Melhor dia:
                        <strong>{{ formatDate(bestDay.date) }}</strong> ({{
                            formatCurrency(bestDay.total)
                        }})</span
                    >
                </div>
                <div class="rounded-lg bg-muted/60 px-2.5 py-1">
                    <span
                        >Média em dias ativos:
                        <strong>{{
                            formatCurrency(averageActiveDailySales)
                        }}</strong></span
                    >
                </div>
            </div>
        </div>

        <!-- Heatmap Scrollable Container -->
        <div class="relative overflow-x-auto pt-1 pb-2">
            <div class="inline-flex min-w-full flex-col gap-1">
                <!-- Month Labels Header -->
                <div
                    class="flex items-center gap-1 pl-8 text-[10px] font-medium text-muted-foreground"
                >
                    <div
                        v-for="(week, wIdx) in calendarWeeks"
                        :key="wIdx"
                        class="w-3.5 shrink-0 text-center"
                    >
                        <span
                            v-if="week.monthLabel"
                            class="-ml-1 block text-left font-semibold"
                        >
                            {{ week.monthLabel }}
                        </span>
                    </div>
                </div>

                <!-- Heatmap Body: Days of week + Columns -->
                <div class="flex items-start gap-1">
                    <!-- Weekday labels on left -->
                    <div
                        class="flex flex-col gap-1 pr-2 text-[10px] font-medium text-muted-foreground select-none"
                    >
                        <div
                            v-for="(label, rowIdx) in weekdayLabels"
                            :key="rowIdx"
                            class="flex h-3.5 items-center justify-end leading-none"
                        >
                            <span v-if="rowIdx % 2 === 1">{{ label }}</span>
                        </div>
                    </div>

                    <!-- Week columns -->
                    <div class="flex items-center gap-1">
                        <div
                            v-for="(week, wIdx) in calendarWeeks"
                            :key="wIdx"
                            class="flex flex-col gap-1"
                        >
                            <div
                                v-for="(day, dIdx) in week.days"
                                :key="dIdx"
                                class="size-3.5 cursor-pointer rounded-xs border transition-transform hover:scale-125"
                                :class="
                                    day
                                        ? getCellColorClass(
                                              getIntensityLevel(day.total),
                                          )
                                        : 'cursor-default border-transparent bg-transparent'
                                "
                                @mouseenter="handleCellMouseEnter(day, $event)"
                                @mouseleave="handleCellMouseLeave"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Tooltip -->
        <Teleport to="body">
            <div
                v-if="hoveredDay"
                class="pointer-events-none fixed z-50 -translate-x-1/2 -translate-y-full rounded-lg border border-border/80 bg-popover px-3 py-2 text-xs text-foreground shadow-xl transition-all duration-75"
                :style="{
                    left: `${tooltipPosition.x}px`,
                    top: `${tooltipPosition.y}px`,
                }"
            >
                <p class="font-semibold text-foreground">
                    {{ formatDayOfWeekPt(hoveredDay.date) }},
                    {{ formatDate(hoveredDay.date) }}
                </p>
                <div
                    class="mt-1 flex items-center justify-between gap-4 text-muted-foreground"
                >
                    <span>Faturamento:</span>
                    <span class="font-bold text-foreground">{{
                        formatCurrency(hoveredDay.total)
                    }}</span>
                </div>
                <div
                    class="flex items-center justify-between gap-4 text-muted-foreground"
                >
                    <span>Pedidos:</span>
                    <span class="font-medium text-foreground">
                        {{
                            hoveredDay.count === 0
                                ? 'Nenhum pedido'
                                : `${hoveredDay.count} pedido(s)`
                        }}
                    </span>
                </div>
            </div>
        </Teleport>

        <!-- Footer / Legend -->
        <div
            class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-border/40 pt-3 text-xs text-muted-foreground"
        >
            <div>
                <span
                    >{{ days.length }} dias no período &bull;
                    {{ activeDaysCount }} dias com vendas</span
                >
            </div>
            <div class="flex items-center gap-1.5">
                <span>Menos</span>
                <div
                    class="size-3 rounded-xs border border-border/40 bg-muted/40 dark:bg-muted/20"
                />
                <div
                    class="size-3 rounded-xs border border-emerald-300 bg-emerald-200 dark:border-emerald-900 dark:bg-emerald-950"
                />
                <div
                    class="size-3 rounded-xs border border-emerald-500 bg-emerald-400 dark:border-emerald-700 dark:bg-emerald-800"
                />
                <div
                    class="size-3 rounded-xs border border-emerald-700 bg-emerald-600 dark:border-emerald-500 dark:bg-emerald-600"
                />
                <div
                    class="size-3 rounded-xs border border-emerald-800 bg-emerald-700 dark:border-emerald-300 dark:bg-emerald-400"
                />
                <span>Mais</span>
            </div>
        </div>
    </div>
</template>
