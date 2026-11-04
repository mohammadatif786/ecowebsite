<template>
    <SettingsSubModal
        :model-value="modelValue"
        title="Blocked accounts"
        icon="user-x"
        color="#ef4444"
        @back="$emit('update:modelValue', false)"
        @close-all="$emit('close-all')"
    >
        <p class="py-10 text-center text-sm text-slate-400">No blocked accounts yet.</p>
    </SettingsSubModal>
</template>

<script setup>
import SettingsSubModal from './SettingsSubModal.vue';

defineProps({ modelValue: Boolean });
defineEmits(['update:modelValue', 'close-all']);
</script>
