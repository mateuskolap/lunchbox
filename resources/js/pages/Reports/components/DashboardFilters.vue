<script setup lang="ts">
import { Filter, X } from 'lucide-vue-next';
import { computed } from 'vue';
import AppSelect from '@/components/AppSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { ReportsDashboardFilters } from '@/types';

const model = defineModel<ReportsDashboardFilters>();

const props = defineProps<{
    hasActiveFilters: boolean;
    years: number[];
}>();

const emit = defineEmits<{
    clear: [];
}>();

const yearOptions = computed(() =>
    props.years.map((year) => ({
        value: String(year),
        label: String(year),
    })),
);

const selectedYear = computed<string | undefined>({
    get: () => {
        if (!model.value?.start_date && !model.value?.end_date) {
            const currentYearStr = String(new Date().getFullYear());

            if (props.years.includes(Number(currentYearStr))) {
                return currentYearStr;
            }

            if (props.years.length > 0) {
                return String(props.years[0]);
            }

            return currentYearStr;
        }

        if (model.value?.start_date && model.value?.end_date) {
            const startYear = model.value.start_date.split('-')[0];
            const endYear = model.value.end_date.split('-')[0];

            if (
                startYear === endYear &&
                model.value.start_date === `${startYear}-01-01` &&
                model.value.end_date === `${startYear}-12-31`
            ) {
                return startYear;
            }
        }

        return undefined;
    },
    set: (year: string | undefined) => {
        if (model.value && year) {
            model.value.start_date = `${year}-01-01`;
            model.value.end_date = `${year}-12-31`;
        }
    },
});
</script>

<template>
    <div
        class="flex flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
    >
        <div class="flex items-center justify-between">
            <div
                class="flex items-center gap-2 text-sm font-medium text-muted-foreground"
            >
                <Filter class="size-4" />
                <span>Filtros</span>
            </div>
            <Button
                v-if="hasActiveFilters"
                variant="ghost"
                size="sm"
                class="h-7 text-xs"
                @click="emit('clear')"
            >
                <X class="mr-1 size-3" />
                Limpar
            </Button>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <!-- Seleção de ano -->
            <div class="flex w-full flex-col gap-1.5 sm:w-[160px]">
                <Label class="text-xs">Ano</Label>
                <AppSelect
                    v-model="selectedYear"
                    :options="yearOptions"
                    placeholder="Selecione o ano"
                />
            </div>

            <!-- Data início -->
            <div class="flex w-full flex-col gap-1.5 sm:w-[160px]">
                <Label class="text-xs">Data Início</Label>
                <Input
                    type="date"
                    v-model="model!.start_date"
                    class="h-9"
                />
            </div>

            <!-- Data fim -->
            <div class="flex w-full flex-col gap-1.5 sm:w-[160px]">
                <Label class="text-xs">Data Fim</Label>
                <Input
                    type="date"
                    v-model="model!.end_date"
                    class="h-9"
                />
            </div>
        </div>
    </div>
</template>
