<script setup lang="ts">
import { TrendingUp, Coins, Scale } from 'lucide-vue-next';
import AppCard from '@/components/AppCard.vue';
import { formatCurrency } from '@/lib/formatters';

defineProps<{
    totalSales: number;
    totalPaid: number;
    totalBalance: number;
}>();
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <!-- Total de Vendas -->
        <AppCard
            title="Total de Vendas"
            :value="formatCurrency(totalSales)"
            :icon="TrendingUp"
        />

        <!-- Total Pago -->
        <AppCard
            title="Total Pago"
            :value="formatCurrency(totalPaid)"
            :icon="Coins"
            icon-bg-class="bg-emerald-500/10"
            icon-color-class="text-emerald-600 dark:text-emerald-400"
            value-class="text-emerald-600 dark:text-emerald-400"
        />

        <!-- Saldo em Aberto -->
        <AppCard
            title="Saldo em Aberto"
            :value="formatCurrency(totalBalance)"
            :icon="Scale"
            icon-bg-class="bg-destructive/10"
            icon-color-class="text-destructive"
            :value-class="{
                'text-destructive': totalBalance > 0,
                'text-foreground': totalBalance <= 0,
            }"
        />
    </div>
</template>
