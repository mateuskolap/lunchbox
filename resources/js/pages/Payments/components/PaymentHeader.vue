<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { formatPhone } from '@/lib/formatters';
import { index as customersIndex } from '@/routes/customers';
import type { Customer } from '@/types';
import CreatePaymentDialog from '../CreatePaymentDialog.vue';

defineProps<{
    customer: Customer;
    canCreatePayment: boolean;
}>();
</script>

<template>
    <div
        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="flex items-start gap-3 sm:items-center">
            <Button
                variant="outline"
                size="icon"
                as-child
                class="mt-1 shrink-0 sm:mt-0"
            >
                <Link :href="customersIndex()">
                    <ArrowLeft class="size-4" />
                    <span class="sr-only">Voltar</span>
                </Link>
            </Button>
            <div class="flex flex-col gap-1">
                <Heading
                    :title="customer.name"
                    description=""
                    variant="small"
                    class="p-0 text-lg font-bold break-words sm:text-xl"
                />
                <p class="text-xs text-muted-foreground sm:text-sm">
                    {{ formatPhone(customer.phone) }}
                </p>
            </div>
        </div>

        <CreatePaymentDialog
            v-if="canCreatePayment"
            :customer="customer"
        />
    </div>
</template>
