<script setup lang="ts">
import { CreditCard, Wallet } from 'lucide-vue-next';
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
        <div
            class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10"
                >
                    <Wallet class="size-5 text-primary" />
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-muted-foreground">
                        Saldo da Carteira
                    </p>
                    <p
                        class="text-lg font-bold text-foreground"
                        :class="{
                            'text-emerald-600 dark:text-emerald-400':
                                Number(customer.balance) > 0,
                            'text-destructive': Number(customer.balance) < 0,
                        }"
                    >
                        {{ formatCurrency(customer.balance) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Payments Count -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10"
                >
                    <CreditCard
                        class="size-5 text-emerald-600 dark:text-emerald-400"
                    />
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-muted-foreground">Pagamentos</p>
                    <p class="text-lg font-bold text-foreground">
                        {{ paymentsTotal }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
