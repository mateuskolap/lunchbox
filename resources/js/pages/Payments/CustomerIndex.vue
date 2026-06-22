<script setup lang="ts">
import type { Customer, PaginatedResponse, Payment } from '@/types';

defineProps<{
    payments: PaginatedResponse<Payment>;
    customer: Customer;
}>();
</script>

<template>
    <pre>{{ payments }}</pre>
</template>
