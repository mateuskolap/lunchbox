<script setup lang="ts">
import {
    TrendingUp,
    Coins,
    Scale,
    Receipt,
    ShoppingCart,
} from 'lucide-vue-next';
import AppCard from '@/components/AppCard.vue';
import { formatCurrency } from '@/lib/formatters';

defineProps<{
    totalSales: number;
    totalPaid: number;
    totalBalance: number;
    averageTicket: number;
    totalOrders: number;
}>();
</script>

<template>
    <div
        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5"
    >
        <AppCard
            title="Total Faturado"
            :value="formatCurrency(totalSales)"
            :icon="TrendingUp"
        />

        <AppCard
            title="Total Recebido"
            :value="formatCurrency(totalPaid)"
            :icon="Coins"
            icon-bg-class="bg-emerald-500/10"
            icon-color-class="text-emerald-600 dark:text-emerald-400"
        />

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

        <AppCard
            title="Ticket Médio"
            :value="formatCurrency(averageTicket)"
            :icon="Receipt"
            icon-bg-class="bg-info/10"
            icon-color-class="text-info"
        />

        <AppCard
            title="Total de Pedidos"
            :value="totalOrders.toLocaleString('pt-BR')"
            :icon="ShoppingCart"
            icon-bg-class="bg-warning/10"
            icon-color-class="text-warning"
        />
    </div>
</template>
