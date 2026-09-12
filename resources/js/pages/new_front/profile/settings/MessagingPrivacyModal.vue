<template>
    <SettingsSubModal
        :model-value="modelValue"
        title="Who can message me"
        icon="message-circle"
        color="#8b5cf6"
        @back="$emit('update:modelValue', false)"
        @close-all="$emit('close-all')"
    >
        <div class="space-y-1.5">
            <button
                v-for="option in options"
                :key="option"
                type="button"
                @click="selectOption(option)"
                :class="[
                    'flex w-full items-center justify-between gap-3 rounded-xl border p-3 text-left transition',
                    current === option ? 'border-lkblue bg-blue-50 text-blue-700' : 'border-slate-100 hover:bg-slate-50',
                ]"
            >
                <span class="text-sm font-bold">{{ option }}</span>
                <i v-if="current === option" data-lucide="check" class="text-lkblue2 h-4 w-4"></i>
            </button>
        </div>
    </SettingsSubModal>
</template>

<script setup>
import { DB } from '@/components/new_frontend/MockDataStore.js';
import SettingsSubModal from './SettingsSubModal.vue';

const props = defineProps({
    modelValue: Boolean,
    current: { type: String, default: 'Everyone' },
});

const emit = defineEmits(['update:modelValue', 'close-all', 'updated', 'toast']);
const options = ['Everyone', 'Followers Only', 'No One'];

const selectOption = (option) => {
    const messaging = { whoCanMessage: option };
    DB.set('lk_settings_messaging', messaging);
    emit('updated', messaging);
    emit('toast', `Messaging set to ${option}`);
};
</script>
