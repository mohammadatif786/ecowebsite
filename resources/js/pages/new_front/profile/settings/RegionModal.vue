<template>
    <SettingsSubModal
        :model-value="modelValue"
        title="Region / Country"
        icon="map"
        color="#f97316"
        @back="$emit('update:modelValue', false)"
        @close-all="$emit('close-all')"
    >
        <div class="space-y-1.5">
            <button
                v-for="region in regions"
                :key="region"
                type="button"
                @click="selectRegion(region)"
                :class="[
                    'flex w-full items-center justify-between gap-3 rounded-xl border p-3 text-left transition',
                    current === region ? 'border-lkblue bg-blue-50 text-blue-700' : 'border-slate-100 hover:bg-slate-50',
                ]"
            >
                <span class="text-sm font-bold">{{ region }}</span>
                <i v-if="current === region" data-lucide="check" class="text-lkblue2 h-4 w-4"></i>
            </button>
        </div>
    </SettingsSubModal>
</template>

<script setup>
import { DB } from '@/components/new_frontend/MockDataStore.js';
import SettingsSubModal from './SettingsSubModal.vue';

const props = defineProps({
    modelValue: Boolean,
    current: { type: String, default: 'Anguilla' },
});

const emit = defineEmits(['update:modelValue', 'close-all', 'updated', 'toast']);
const regions = ['Anguilla', 'Jamaica', 'Trinidad & Tobago', 'Barbados', 'Bahamas', 'Dominican Republic'];

const selectRegion = (region) => {
    const pref = DB.get('lk_settings_preferences', { currency: 'USD (B$)', region: 'Anguilla', darkMode: false });
    pref.region = region;
    DB.set('lk_settings_preferences', pref);
    emit('updated', pref);
    emit('toast', `Region set to ${region}`);
};
</script>
