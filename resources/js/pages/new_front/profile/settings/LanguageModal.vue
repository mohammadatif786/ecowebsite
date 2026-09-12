<template>
    <SettingsSubModal
        :model-value="modelValue"
        title="Language"
        icon="globe"
        color="#0ea5e9"
        @back="$emit('update:modelValue', false)"
        @close-all="$emit('close-all')"
    >
        <div class="space-y-1.5">
            <button
                v-for="language in languages"
                :key="language"
                type="button"
                @click="selectLanguage(language)"
                :class="[
                    'flex w-full items-center justify-between gap-3 rounded-xl border p-3 text-left transition',
                    current === language ? 'border-lkblue bg-blue-50 text-blue-700' : 'border-slate-100 hover:bg-slate-50',
                ]"
            >
                <span class="text-sm font-bold">{{ language }}</span>
                <i v-if="current === language" data-lucide="check" class="text-lkblue2 h-4 w-4"></i>
            </button>
        </div>
    </SettingsSubModal>
</template>

<script setup>
import { DB } from '@/components/new_frontend/MockDataStore.js';
import SettingsSubModal from './SettingsSubModal.vue';

const props = defineProps({
    modelValue: Boolean,
    current: { type: String, default: 'English' },
});

const emit = defineEmits(['update:modelValue', 'close-all', 'updated', 'toast']);
const languages = ['English', 'Espanol', 'Francais', 'Kreyol Ayisyen', 'Papiamento'];

const selectLanguage = (language) => {
    DB.set('lk_settings_language', language);
    emit('updated', language);
    emit('toast', `Language set to ${language}`);
};
</script>
