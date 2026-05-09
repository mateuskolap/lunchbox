import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const page = usePage();
    const permissions = computed(() => page.props.auth.permissions ?? []);

    function can(permission: string): boolean {
        return permissions.value.includes(permission);
    }

    function canAny(perms: string[]): boolean {
        return perms.some((p) => permissions.value.includes(p));
    }

    return { can, canAny, permissions };
}
