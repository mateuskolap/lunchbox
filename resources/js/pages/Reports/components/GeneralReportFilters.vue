<script setup lang="ts">
import { Calendar, Filter, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { GeneralReportFilterValues } from '../types';

defineProps<{
    hasActiveFilters: boolean;
}>();

const emit = defineEmits<{
    (e: 'clear'): void;
}>();

const model = defineModel<GeneralReportFilterValues>({ required: true });

function formatYMD(d: Date): string {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function applyPreset(
    preset:
        | 'this_year'
        | 'this_month'
        | 'last_30_days'
        | 'last_90_days'
        | 'last_year',
) {
    const now = new Date();
    const currentYear = now.getFullYear();

    if (preset === 'this_year') {
        model.value.start_date = `${currentYear}-01-01`;
        model.value.end_date = `${currentYear}-12-31`;
    } else if (preset === 'this_month') {
        const startOfMonth = new Date(currentYear, now.getMonth(), 1);
        const endOfMonth = new Date(currentYear, now.getMonth() + 1, 0);
        model.value.start_date = formatYMD(startOfMonth);
        model.value.end_date = formatYMD(endOfMonth);
    } else if (preset === 'last_30_days') {
        const past30 = new Date();
        past30.setDate(now.getDate() - 30);
        model.value.start_date = formatYMD(past30);
        model.value.end_date = formatYMD(now);
    } else if (preset === 'last_90_days') {
        const past90 = new Date();
        past90.setDate(now.getDate() - 90);
        model.value.start_date = formatYMD(past90);
        model.value.end_date = formatYMD(now);
    } else if (preset === 'last_year') {
        model.value.start_date = `${currentYear - 1}-01-01`;
        model.value.end_date = `${currentYear - 1}-12-31`;
    }
}

function isPresetActive(
    preset:
        | 'this_year'
        | 'this_month'
        | 'last_30_days'
        | 'last_90_days'
        | 'last_year',
): boolean {
    const now = new Date();
    const currentYear = now.getFullYear();

    if (preset === 'this_year') {
        return (
            model.value.start_date === `${currentYear}-01-01` &&
            model.value.end_date === `${currentYear}-12-31`
        );
    }

    if (preset === 'this_month') {
        const startOfMonth = new Date(currentYear, now.getMonth(), 1);
        const endOfMonth = new Date(currentYear, now.getMonth() + 1, 0);

        return (
            model.value.start_date === formatYMD(startOfMonth) &&
            model.value.end_date === formatYMD(endOfMonth)
        );
    }

    if (preset === 'last_year') {
        return (
            model.value.start_date === `${currentYear - 1}-01-01` &&
            model.value.end_date === `${currentYear - 1}-12-31`
        );
    }

    return false;
}
</script>

<template>
    <div
        class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
    >
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div
                class="flex items-center gap-2 text-sm font-semibold text-foreground"
            >
                <Filter class="size-4 text-primary" />
                <span>Filtro de Período</span>
            </div>

            <!-- Quick Presets -->
            <div class="flex flex-wrap items-center gap-1.5">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :class="[
                        'h-7 px-2.5 text-xs',
                        isPresetActive('this_year')
                            ? 'border-primary bg-primary text-primary-foreground hover:bg-primary/90'
                            : '',
                    ]"
                    @click="applyPreset('this_year')"
                >
                    Este Ano
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :class="[
                        'h-7 px-2.5 text-xs',
                        isPresetActive('this_month')
                            ? 'border-primary bg-primary text-primary-foreground hover:bg-primary/90'
                            : '',
                    ]"
                    @click="applyPreset('this_month')"
                >
                    Este Mês
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="h-7 px-2.5 text-xs"
                    @click="applyPreset('last_30_days')"
                >
                    Últimos 30 dias
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="h-7 px-2.5 text-xs"
                    @click="applyPreset('last_90_days')"
                >
                    Últimos 90 dias
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :class="[
                        'h-7 px-2.5 text-xs',
                        isPresetActive('last_year')
                            ? 'border-primary bg-primary text-primary-foreground hover:bg-primary/90'
                            : '',
                    ]"
                    @click="applyPreset('last_year')"
                >
                    Ano Anterior
                </Button>
                <Button
                    v-if="hasActiveFilters"
                    type="button"
                    variant="ghost"
                    size="sm"
                    class="h-7 px-2 text-xs text-muted-foreground hover:text-foreground"
                    @click="emit('clear')"
                >
                    <X class="mr-1 size-3.5" />
                    Resetar
                </Button>
            </div>
        </div>

        <div
            class="flex flex-wrap items-end gap-3 border-t border-border/40 pt-1"
        >
            <div class="w-full sm:w-[190px]">
                <label
                    class="mb-1 block flex items-center gap-1 text-xs font-medium text-muted-foreground"
                >
                    <Calendar class="size-3" /> Data Inicial
                </label>
                <Input type="date" v-model="model.start_date" class="h-9" />
            </div>

            <div class="w-full sm:w-[190px]">
                <label
                    class="mb-1 block flex items-center gap-1 text-xs font-medium text-muted-foreground"
                >
                    <Calendar class="size-3" /> Data Final
                </label>
                <Input type="date" v-model="model.end_date" class="h-9" />
            </div>
        </div>
    </div>
</template>
