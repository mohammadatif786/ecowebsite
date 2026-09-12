<template>
    <SettingsSubModal
        :model-value="modelValue"
        title="Help & Support"
        icon="life-buoy"
        color="#14b8a6"
        @back="$emit('update:modelValue', false)"
        @close-all="$emit('close-all')"
    >
        <div class="mb-4 space-y-2">
            <button v-for="item in items" :key="item.label" type="button" @click="$emit('toast', item.label)" class="help-row">
                <span class="help-icon"><i :data-lucide="item.icon" class="h-4 w-4"></i></span>
                <span class="flex-1 text-sm font-bold">{{ item.label }}</span>
                <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300"></i>
            </button>
        </div>
        <div class="rounded-2xl bg-slate-50 p-4 text-center">
            <p class="text-[11px] text-slate-400">LinkUp Vibes &middot; App version 2026.1</p>
        </div>
    </SettingsSubModal>
</template>

<script setup>
import SettingsSubModal from './SettingsSubModal.vue';

defineProps({ modelValue: Boolean });
defineEmits(['update:modelValue', 'close-all', 'toast']);

const items = [
    { label: 'Frequently Asked Questions', icon: 'help-circle' },
    { label: 'Contact Support', icon: 'message-circle' },
    { label: 'Report a Problem', icon: 'flag' },
    { label: 'Community Guidelines', icon: 'shield' },
    { label: 'Terms of Service', icon: 'file-text' },
    { label: 'Privacy Policy', icon: 'lock' },
];
</script>

<style scoped>
.help-row {
    display: flex;
    width: 100%;
    align-items: center;
    gap: 0.75rem;
    border-radius: 0.75rem;
    border: 1px solid #f1f5f9;
    padding: 0.75rem;
    text-align: left;
    transition: background 0.15s ease;
}
.help-row:hover {
    background: #f8fafc;
}
.help-icon {
    display: grid;
    height: 2.25rem;
    width: 2.25rem;
    place-items: center;
    border-radius: 0.5rem;
    background: #f0fdfa;
    color: #0d9488;
}
</style>
