import { router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { reactive, watch, toRaw, computed } from 'vue';

interface UseFiltersOptions<T> {
    /**
     * Fields that should trigger a search immediately (e.g., select inputs).
     * Any fields NOT in this list will be debounced.
     */
    immediateFields?: (keyof T)[];

    /**
     * Debounce time in milliseconds for debounced fields. Defaults to 500ms.
     */
    debounceMs?: number;

    /**
     * Optional transform function to clean up filters before sending them to the router.
     */
    transform?: (filters: T) => Record<string, any>;
}

export function useFilters<T extends Record<string, any>>(
    baseUrl: any,
    initialFilters: T,
    options: UseFiltersOptions<T> = {},
) {
    const {
        immediateFields = [],
        debounceMs = 500,
        transform = (f) => f,
    } = options;

    const filters = reactive<T>({ ...initialFilters });
    const defaultFilters = { ...initialFilters };
    let isClearing = false;

    const triggerSearch = () => {
        const url = typeof baseUrl === 'function' ? baseUrl() : baseUrl;
        const params = transform(toRaw(filters) as T);

        router.get(url, params, {
            preserveState: true,
            replace: true,
        });
    };

    // Helper to check if a field is immediate
    const isImmediate = (key: keyof T) => immediateFields.includes(key);

    // Watch immediate fields
    watch(
        () => {
            const tracked: Record<string, any> = {};

            for (const key of immediateFields) {
                const k = key as string;
                tracked[k] = (filters as Record<string, any>)[k];
            }

            return tracked;
        },
        () => {
            if (!isClearing) {
                triggerSearch();
            }
        },
        { deep: true },
    );

    // Watch debounced fields
    watchDebounced(
        () => {
            const tracked: Record<string, any> = {};

            for (const key in filters) {
                if (!isImmediate(key)) {
                    tracked[key] = (filters as Record<string, any>)[key];
                }
            }

            return tracked;
        },
        () => {
            if (!isClearing) {
                triggerSearch();
            }
        },
        { deep: true, debounce: debounceMs },
    );

    const clearFilters = () => {
        isClearing = true;

        for (const key in defaultFilters) {
            (filters as Record<string, any>)[key] = defaultFilters[key];
        }

        setTimeout(() => {
            isClearing = false;
            triggerSearch();
        }, debounceMs + 50);
    };

    const hasActiveFilters = computed(() => {
        return Object.keys(defaultFilters).some((key) => {
            const current = (filters as Record<string, any>)[key];
            const defaultValue = defaultFilters[key];

            const isCurrentEmpty =
                current === undefined ||
                current === null ||
                current === '' ||
                current === 'all' ||
                (Array.isArray(current) && current.length === 0);
            const isDefaultEmpty =
                defaultValue === undefined ||
                defaultValue === null ||
                defaultValue === '' ||
                defaultValue === 'all' ||
                (Array.isArray(defaultValue) && defaultValue.length === 0);

            if (isCurrentEmpty && isDefaultEmpty) {
                return false;
            }

            if (Array.isArray(current) && Array.isArray(defaultValue)) {
                return (
                    current.length !== defaultValue.length ||
                    current.some((val, i) => val !== defaultValue[i])
                );
            }

            return current !== defaultValue;
        });
    });

    return {
        filters,
        clearFilters,
        hasActiveFilters,
        triggerSearch,
    };
}
