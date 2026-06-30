<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Filter, X } from 'lucide-vue-next';
import { computed } from 'vue';
import AppSelect from '@/components/AppSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface OrderFilters {
    customer_name: string;
    status: string;
    start_date: string;
    end_date: string;
}

const props = defineProps<{
    orderStatuses: string[];
    hasActiveFilters: boolean;
}>();

const emit = defineEmits<{
    (e: 'clear'): void;
}>();

const model = defineModel<OrderFilters>({ required: true });

const statusOptions = computed(() => {
    return props.orderStatuses.map((st) => ({
        value: st,
        label: trans(st),
    }));
});
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
            <!-- Customer Filter -->
            <div class="w-full sm:w-[220px]">
                <label
                    class="mb-1 block text-xs font-medium text-muted-foreground"
                    >Cliente</label
                >
                <Input
                    v-model="model.customer_name"
                    placeholder="Nome do cliente"
                    class="h-9"
                />
            </div>

            <!-- Status Filter -->
            <div class="w-full sm:w-[160px]">
                <label
                    class="mb-1 block text-xs font-medium text-muted-foreground"
                    >Status</label
                >
                <AppSelect
                    v-model="model.status"
                    :options="statusOptions"
                    placeholder="Todos os status"
                />
            </div>

            <!-- Start Date Filter -->
            <div class="w-full sm:w-[160px]">
                <label
                    class="mb-1 block text-xs font-medium text-muted-foreground"
                    >Data Inicial</label
                >
                <Input
                    type="date"
                    v-model="model.start_date"
                    class="h-9"
                    :class="{
                        'text-muted-foreground': !model.start_date,
                        'text-foreground': model.start_date,
                    }"
                />
            </div>

            <!-- End Date Filter -->
            <div class="w-full sm:w-[160px]">
                <label
                    class="mb-1 block text-xs font-medium text-muted-foreground"
                >
                    Data Final
                </label>
                <Input
                    type="date"
                    v-model="model.end_date"
                    class="h-9"
                    :class="{
                        'text-muted-foreground': !model.end_date,
                        'text-foreground': model.end_date,
                    }"
                />
            </div>
        </div>
    </div>
</template>
