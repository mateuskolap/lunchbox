<script setup lang="ts">
import { Filter, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface CustomerFilters {
    name: string;
    debtors_only: boolean;
}

defineProps<{
    hasActiveFilters: boolean;
}>();

const emit = defineEmits<{
    (e: 'clear'): void;
}>();

const model = defineModel<CustomerFilters>({ required: true });
</script>

<template>
    <div
        class="flex flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
    >
        <div class="flex items-center justify-between">
            <div
                class="flex items-center gap-1.5 text-sm font-semibold text-muted-foreground"
            >
                <Filter class="size-4" />
                Filtros
            </div>
            <!-- Clear Filters button -->
            <Button
                v-if="hasActiveFilters"
                type="button"
                variant="ghost"
                class="h-8 px-2 text-xs text-muted-foreground hover:text-foreground"
                @click="emit('clear')"
            >
                <X class="mr-1 size-3.5" />
                Limpar
            </Button>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <!-- Name Filter -->
            <div class="w-full sm:w-[220px]">
                <label
                    class="mb-1 block text-xs font-medium text-muted-foreground"
                    >Nome</label
                >
                <Input
                    v-model="model.name"
                    placeholder="Nome do cliente"
                    class="h-9"
                />
            </div>

            <!-- Debtors Only Filter -->
            <div class="flex h-9 items-center space-x-2 py-1">
                <Checkbox
                    id="filter-debtors-only"
                    :model-value="model.debtors_only"
                    @update:model-value="model.debtors_only = !!$event"
                />
                <Label
                    for="filter-debtors-only"
                    class="cursor-pointer text-sm font-medium text-muted-foreground select-none hover:text-foreground peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                >
                    Apenas devedores
                </Label>
            </div>
        </div>
    </div>
</template>
