<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    title: string;
    value: string | number;
    icon?: Component;
    iconBgClass?: string;
    iconColorClass?: string;
    valueClass?: any;
}>();
</script>

<template>
    <div
        class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
    >
        <div class="flex items-center gap-3">
            <!-- Icon slot or component -->
            <div
                v-if="icon"
                class="flex size-10 shrink-0 items-center justify-center rounded-lg"
                :class="iconBgClass || 'bg-primary/10'"
            >
                <component
                    :is="icon"
                    class="size-5"
                    :class="iconColorClass || 'text-primary'"
                />
            </div>

            <!-- Content -->
            <div class="min-w-0">
                <p class="text-sm text-muted-foreground">
                    {{ title }}
                </p>
                <p
                    class="text-lg font-bold text-foreground"
                    :class="valueClass"
                >
                    {{ value }}
                </p>
            </div>
        </div>
    </div>
</template>
