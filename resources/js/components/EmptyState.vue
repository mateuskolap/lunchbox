<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    icon: Component;
    title: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex flex-col items-center justify-center p-8 flex-1 text-center">
        <div class="flex size-12 items-center justify-center rounded-full bg-muted/50 mb-4">
            <component :is="icon" class="size-6 text-muted-foreground" />
        </div>
        <h3 class="text-lg font-medium">{{ title }}</h3>
        <p v-if="description" class="text-sm text-muted-foreground max-w-sm mt-1 mb-4">
            {{ description }}
        </p>
        <slot />
    </div>
</template>
