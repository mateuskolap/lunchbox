<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type Option = {
    value: string;
    label: string;
};

const props = withDefaults(
    defineProps<{
        options: Option[];
        placeholder?: string;
        searchPlaceholder?: string;
        emptyText?: string;
        name?: string;
        required?: boolean;
        disabled?: boolean;
        searchable?: boolean;
    }>(),
    {
        placeholder: 'Selecione...',
        searchPlaceholder: 'Pesquisar...',
        emptyText: 'Nenhum resultado encontrado',
        searchable: false,
    },
);

const model = defineModel<string>();

const searchQuery = ref('');
const searchInputRef = ref<any>(null);

const filteredOptions = computed(() => {
    if (!props.searchable || !searchQuery.value) {
        return props.options;
    }

    const search = searchQuery.value.toLowerCase();

    return props.options.filter((opt) =>
        opt.label.toLowerCase().includes(search),
    );
});

const onOpenChange = (open: boolean) => {
    if (!open) {
        searchQuery.value = '';
    } else if (props.searchable) {
        nextTick(() => {
            const input =
                searchInputRef.value?.$el?.querySelector('input') ||
                searchInputRef.value?.$el;

            if (input) {
                input.focus();
            }
        });
    }
};
</script>

<template>
    <Select
        v-model="model"
        :name="name"
        :required="required"
        :disabled="disabled"
        @update:open="onOpenChange"
    >
        <SelectTrigger class="h-9 w-full bg-transparent dark:bg-input/30">
            <SelectValue :placeholder="placeholder" />
        </SelectTrigger>
        <SelectContent>
            <!-- Search input inside Select content dropdown -->
            <div
                v-if="searchable"
                class="sticky -top-1 z-10 bg-popover border-b border-sidebar-border -mx-1 -mt-1 pt-2 pb-2 px-3 touch-none"
                @keydown.stop
                @pointerdown.stop
                @mousedown.stop
                @touchstart.stop
                @touchend.stop
            >
                <Input
                    v-model="searchQuery"
                    :placeholder="searchPlaceholder"
                    class="h-8 text-base md:text-xs focus:border-input focus:ring-0 focus-visible:border-input focus-visible:ring-0"
                    ref="searchInputRef"
                />
            </div>
            <SelectItem
                v-for="opt in filteredOptions"
                :key="opt.value"
                :value="opt.value"
            >
                {{ opt.label }}
            </SelectItem>
            <div
                v-if="filteredOptions.length === 0"
                class="p-4 text-center text-xs text-muted-foreground"
            >
                {{ emptyText }}
            </div>
        </SelectContent>
    </Select>
</template>
