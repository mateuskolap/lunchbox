<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        src="/logo.png"
        alt="Ale Marmitas"
        :class="className"
        class="rounded-full object-cover"
        v-bind="$attrs"
    />
</template>
