<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    icon: Component;
    title: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex flex-1 flex-col items-center justify-center p-8 text-center"
    >
        <div
            class="mb-4 flex size-12 items-center justify-center rounded-full bg-muted/50"
        >
            <component :is="icon" class="size-6 text-muted-foreground" />
        </div>
        <h3 class="text-lg font-medium">{{ title }}</h3>
        <p
            v-if="description"
            class="mt-1 mb-4 max-w-sm text-sm text-muted-foreground"
        >
            {{ description }}
        </p>
        <slot />
    </div>
</template>
