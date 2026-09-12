<template>
    <SettingsSubModal
        :model-value="modelValue"
        title="Contact Us"
        icon="mail"
        color="#14b8a6"
        @back="$emit('update:modelValue', false)"
        @close-all="$emit('close-all')"
    >
        <div class="space-y-2">
            <div class="contact-row">
                <span class="contact-icon"><i data-lucide="mail" class="h-4 w-4"></i></span>
                <div>
                    <p class="text-[11px] font-black text-slate-400">Email</p>
                    <p class="text-sm font-bold">support@linkupvibes.app</p>
                </div>
            </div>
            <div class="contact-row">
                <span class="contact-icon"><i data-lucide="phone" class="h-4 w-4"></i></span>
                <div>
                    <p class="text-[11px] font-black text-slate-400">Phone</p>
                    <p class="text-sm font-bold">+1 (242) 555-0100</p>
                </div>
            </div>
        </div>
    </SettingsSubModal>
</template>

<script setup>
import SettingsSubModal from './SettingsSubModal.vue';

defineProps({ modelValue: Boolean });
defineEmits(['update:modelValue', 'close-all']);
</script>

<style scoped>
.contact-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-radius: 0.75rem;
    border: 1px solid #f1f5f9;
    padding: 0.75rem;
}
.contact-icon {
    display: grid;
    height: 2.25rem;
    width: 2.25rem;
    place-items: center;
    border-radius: 0.5rem;
    background: #f0fdfa;
    color: #0d9488;
}
</style>
