<script setup lang="ts">
import { CreditCard, Wallet } from 'lucide-vue-next';
import AppCard from '@/components/AppCard.vue';
import { formatCurrency } from '@/lib/formatters';
import type { Customer } from '@/types';

defineProps<{
    customer: Customer;
    paymentsTotal: number;
}>();
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <!-- Wallet Balance -->
        <AppCard
            title="Saldo da Carteira"
            :value="formatCurrency(customer.balance)"
            :icon="Wallet"
            :value-class="{
                'text-emerald-600 dark:text-emerald-400': Number(customer.balance) > 0,
                'text-destructive': Number(customer.balance) < 0,
            }"
        />

        <!-- Payments Count -->
        <AppCard
            title="Pagamentos"
            :value="paymentsTotal"
            :icon="CreditCard"
            icon-bg-class="bg-emerald-500/10"
            icon-color-class="text-emerald-600 dark:text-emerald-400"
        />
    </div>
</template>
