<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

defineProps<{
    label: string;
    fieldId: string;
    error?: string;
    srOnly?: boolean;
}>();
</script>

<template>
    <div class="grid gap-2">
        <Label :for="fieldId" :class="{ 'sr-only': srOnly }">{{ label }}</Label>
        <slot />
        <InputError :message="error" />
    </div>
</template>
