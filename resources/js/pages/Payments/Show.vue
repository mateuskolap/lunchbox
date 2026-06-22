<script setup lang="ts">
import type {
    Customer,
    OrderPayment,
    PaginatedResponse,
    Payment,
} from '@/types';

defineProps<{
    orderPayments: PaginatedResponse<OrderPayment>;
    payment: Payment;
    customer: Customer;
}>();
</script>

<template></template>
