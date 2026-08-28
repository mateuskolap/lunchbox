<script setup lang="ts">
import { Filter, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface SalesReportFilters {
    start_date: string;
    end_date: string;
    customer: string;
}

defineProps<{
    hasActiveFilters: boolean;
}>();

const emit = defineEmits<{
    (e: 'clear'): void;
}>();

const model = defineModel<SalesReportFilters>({ required: true });
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
            <div class="w-full sm:w-[180px]">
                <label
                    class="mb-1 block text-xs font-medium text-muted-foreground"
                    >Data Inicial</label
                >
                <Input type="date" v-model="model.start_date" class="h-9" />
            </div>

            <div class="w-full sm:w-[180px]">
                <label
                    class="mb-1 block text-xs font-medium text-muted-foreground"
                    >Data Final</label
                >
                <Input type="date" v-model="model.end_date" class="h-9" />
            </div>

            <div class="w-full sm:w-[260px]">
                <label
                    class="mb-1 block text-xs font-medium text-muted-foreground"
                    >Cliente</label
                >
                <Input
                    v-model="model.customer"
                    placeholder="Nome do cliente"
                    class="h-9"
                />
            </div>
        </div>
    </div>
</template>
