<template>
    <SettingsSubModal
        :model-value="modelValue"
        title="Advertise My Business"
        icon="megaphone"
        color="#f59e0b"
        @back="$emit('update:modelValue', false)"
        @close-all="$emit('close-all')"
    >
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-center">
            <i data-lucide="megaphone" class="mx-auto mb-2 h-8 w-8 text-amber-500"></i>
            <p class="mb-1 font-black">Coming soon</p>
            <p class="text-[12px] text-amber-700">
                Promote your event, store or service to more people on LinkUp. This feature is still being built.
            </p>
        </div>
    </SettingsSubModal>
</template>

<script setup>
import SettingsSubModal from './SettingsSubModal.vue';

defineProps({ modelValue: Boolean });
defineEmits(['update:modelValue', 'close-all']);
</script>
