<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import CreateUserDialog from './CreateUserDialog.vue';
import EditUserDialog from './EditUserDialog.vue';
import DeleteUserDialog from './DeleteUserDialog.vue';
import Heading from '@/components/Heading.vue';
import TablePagination from '@/components/TablePagination.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Users } from 'lucide-vue-next';
import type { PaginatedResponse } from '@/types';
import type { User } from '@/types/auth';
import { index as usersIndex } from '@/routes/users';
import EmptyState from '@/components/EmptyState.vue';
import { usePermissions } from '@/composables/usePermissions';

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

const props = defineProps<{
    users: PaginatedResponse<User>;
}>();

const page = usePage();
const authUser = computed(() => page.props.auth.user);
const { can, canAny } = usePermissions();

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

        <div class="flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card overflow-hidden flex flex-col">
            <EmptyState
                v-if="users.data.length === 0"
                :icon="Users"
                title="Nenhum usuário encontrado"
                description="Você ainda não tem nenhum usuário cadastrado. Adicione um novo usuário para começar."
            >
                <CreateUserDialog v-if="can('users.store')" />
            </EmptyState>

            <div v-else class="flex flex-col flex-1 overflow-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nome</TableHead>
                            <TableHead>E-mail</TableHead>
                            <TableHead v-if="canAny(['users.update', 'users.destroy'])" class="w-[100px] text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in users.data" :key="user.id">
                            <TableCell class="font-medium">{{ user.name }}</TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell v-if="canAny(['users.update', 'users.destroy'])" class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <EditUserDialog v-if="can('users.update')" :user="user" />
                                    <!-- Esconde botão de deletar se for o próprio usuário logado -->
                                    <DeleteUserDialog v-if="can('users.destroy') && user.id !== authUser.id" :user="user" />
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <TablePagination :paginator="users" />
            </div>
        </div>
    </div>
</template>
