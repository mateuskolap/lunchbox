<script setup lang="ts">
import { Input } from '@/components/ui/input';

const modelValue = defineModel<string | number | null>();
</script>

<template>
    <Input
        v-model="modelValue"
        class="bg-transparent dark:bg-input/30 transition-colors"
    />
</template>
