<script setup lang="ts">
import { UtensilsCrossed } from 'lucide-vue-next';

interface SummaryItem {
    name: string;
    quantity: number;
}

defineProps<{
    summary: SummaryItem[];
}>();
</script>

<template>
    <div v-if="summary.length > 0" class="flex flex-wrap items-center gap-2">
        <div
            v-for="item in summary"
            :key="item.name"
            class="flex items-center gap-2 rounded-lg border border-sidebar-border/70 bg-card px-3 py-2 dark:border-sidebar-border"
        >
            <UtensilsCrossed class="size-3.5 shrink-0 text-muted-foreground" />
            <span class="text-sm font-medium text-foreground">{{
                item.name
            }}</span>
            <span
                class="flex size-6 items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-foreground"
            >
                {{ item.quantity }}
            </span>
        </div>
    </div>
</template>
