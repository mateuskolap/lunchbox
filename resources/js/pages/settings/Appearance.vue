<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Configurações de aparência',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Configurações de aparência" />

    <h1 class="sr-only">Configurações de aparência</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Configurações de aparência"
            description="Atualize as configurações de aparência da sua conta"
        />
        <AppearanceTabs />
    </div>
</template>
