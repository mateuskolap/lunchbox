<script setup lang="ts">
import { DollarSign } from 'lucide-vue-next';
import AppCard from '@/components/AppCard.vue';
import { formatCurrency } from '@/lib/formatters';

defineProps<{
    total: number;
    count: number;
}>();
</script>

<template>
    <div class="grid gap-4">
        <!-- Total Amount Card -->
        <AppCard
            title="Total em Pedidos"
            :value="formatCurrency(total)"
            :icon="DollarSign"
            icon-bg-class="bg-emerald-500/10"
            icon-color-class="text-emerald-600 dark:text-emerald-400"
        />
    </div>
</template>
