<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { PaginatedResponse } from '@/types';

defineProps<{
    paginator: PaginatedResponse<unknown>;
}>();
</script>

<template>
    <div
        v-if="paginator.last_page > 1"
        class="mt-auto flex items-center justify-between border-t border-border p-4"
    >
        <p class="text-sm text-muted-foreground">
            Mostrando
            <span class="font-medium">{{ paginator.data.length }}</span> de
            <span class="font-medium">{{ paginator.total }}</span> resultados
        </p>
        <div class="flex items-center gap-1">
            <Link
                v-if="paginator.prev_page_url"
                :href="paginator.prev_page_url"
                :class="cn(buttonVariants({ variant: 'ghost' }), 'gap-1 px-2.5 sm:pr-2.5')"
            >
                <ChevronLeft />
                <span class="hidden sm:block">Anterior</span>
            </Link>
            <div
                v-else
                :class="cn(buttonVariants({ variant: 'ghost' }), 'gap-1 px-2.5 sm:pr-2.5 opacity-50 pointer-events-none')"
            >
                <ChevronLeft />
                <span class="hidden sm:block">Anterior</span>
            </div>

            <Link
                v-if="paginator.next_page_url"
                :href="paginator.next_page_url"
                :class="cn(buttonVariants({ variant: 'ghost' }), 'gap-1 px-2.5 sm:pr-2.5')"
            >
                <span class="hidden sm:block">Próximo</span>
                <ChevronRight />
            </Link>
            <div
                v-else
                :class="cn(buttonVariants({ variant: 'ghost' }), 'gap-1 px-2.5 sm:pr-2.5 opacity-50 pointer-events-none')"
            >
                <span class="hidden sm:block">Próximo</span>
                <ChevronRight />
            </div>
        </div>
    </div>
</template>


