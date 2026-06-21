<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { usePermissions } from '@/composables/usePermissions';
import { index as rolesIndex } from '@/routes/roles';
import type { Role, Permission, PaginatedResponse } from '@/types';
import RoleTable from './components/RoleTable.vue';
import CreateRoleDialog from './CreateRoleDialog.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Papéis',
                href: rolesIndex(),
            },
        ],
    },
});

defineProps<{
    roles: PaginatedResponse<Role>;
    permissions: Permission[];
}>();

const { can } = usePermissions();
</script>

<template>
    <Head title="Papéis" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Papéis"
                description="Gerencie os papéis (perfis de acesso) e permissões dos usuários do sistema."
            />
            <CreateRoleDialog
                v-if="can('roles.store')"
                :permissions="permissions"
            />
        </div>

        <RoleTable :roles="roles" :permissions="permissions" />
    </div>
</template>
