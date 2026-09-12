<template>
    <SettingsSubModal
        :model-value="modelValue"
        title="Legal Center"
        icon="file-text"
        color="#64748b"
        @back="$emit('update:modelValue', false)"
        @close-all="$emit('close-all')"
    >
        <p class="py-10 text-center text-sm text-slate-400">Legal documents will be available here soon.</p>
    </SettingsSubModal>
</template>

<script setup>
import SettingsSubModal from './SettingsSubModal.vue';

defineProps({ modelValue: Boolean });
defineEmits(['update:modelValue', 'close-all']);
</script>
