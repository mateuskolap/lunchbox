<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { LayoutDashboard } from 'lucide-vue-next';
import EmptyState from '@/components/EmptyState.vue';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col items-center justify-center p-4">
        <EmptyState
            :icon="LayoutDashboard"
            title="Bem-vindo ao sistema"
            description="Você ainda não possui permissões para acessar os módulos principais. Entre em contato com um administrador para solicitar acesso."
        />
    </div>
</template>
