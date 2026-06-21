<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { usePermissions } from '@/composables/usePermissions';
import { index as usersIndex } from '@/routes/users';
import type { PaginatedResponse } from '@/types';
import type { User } from '@/types/auth';
import type { Role } from '@/types/role';
import CreateUserDialog from './CreateUserDialog.vue';
import UserTable from './components/UserTable.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Usuários',
                href: usersIndex(),
            },
        ],
    },
});

defineProps<{
    users: PaginatedResponse<User>;
    roles: Role[];
}>();

const { can } = usePermissions();
</script>

<template>
    <Head title="Usuários" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Usuários"
                description="Gerencie os usuários do sistema."
            />
            <CreateUserDialog v-if="can('users.store')" />
        </div>

        <UserTable :users="users" :roles="roles" />
    </div>
</template>
