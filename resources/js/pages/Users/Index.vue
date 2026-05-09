<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import EmptyState from '@/components/EmptyState.vue';
import { Users } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Usuários',
                href: '#',
            },
        ],
    },
});

defineProps<{}>();
</script>

<template>
    <Head title="Usuários" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Usuários"
                description="Gerencie os usuários do sistema."
            />
        </div>

        <div class="flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card overflow-hidden flex flex-col">
            <EmptyState
                :icon="Users"
                title="Em desenvolvimento"
                description="A funcionalidade de usuários será implementada futuramente."
            />
        </div>
    </div>
</template>
