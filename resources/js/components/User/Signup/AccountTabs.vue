<script setup lang="ts">
// "Create account | Sign in" switch at the top of the account page.
import type { AccountMode } from './types';

const props = defineProps<{ modelValue: AccountMode }>();
const emit = defineEmits<{ (e: 'update:modelValue', value: AccountMode): void }>();

const tabs: { id: AccountMode; label: string }[] = [
    { id: 'create', label: 'Create account' },
    { id: 'signin', label: 'Sign in' },
];

function select(id: AccountMode) {
    if (id !== props.modelValue) emit('update:modelValue', id);
}

// Left and right arrows move between the two tabs, as a tab list should.
function onKey(event: KeyboardEvent) {
    if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
    event.preventDefault();
    const next = props.modelValue === 'create' ? 'signin' : 'create';
    select(next);
    document.getElementById(`account-tab-${next}`)?.focus();
}
</script>

<template>
    <div role="tablist" aria-label="Account" class="flex gap-1 rounded-[16px] bg-[#E8EDF6] p-[5px]" @keydown="onKey">
        <button
            v-for="tab in tabs"
            :id="`account-tab-${tab.id}`"
            :key="tab.id"
            type="button"
            role="tab"
            :aria-selected="modelValue === tab.id"
            :aria-controls="`account-panel-${tab.id}`"
            :tabindex="modelValue === tab.id ? 0 : -1"
            class="min-h-[46px] flex-1 cursor-pointer rounded-[12px] text-[15px] font-extrabold"
            :class="modelValue === tab.id ? 'bg-white text-[#0B1530] shadow-[0_1px_4px_rgba(11,21,48,0.12)]' : 'bg-transparent text-[#475569] hover:text-[#0B1530]'"
            @click="select(tab.id)"
        >
            {{ tab.label }}
        </button>
    </div>
</template>
