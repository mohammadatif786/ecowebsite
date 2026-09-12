<template>
    <SettingsSubModal
        :model-value="modelValue"
        title="Currency"
        icon="dollar-sign"
        color="#059669"
        @back="$emit('update:modelValue', false)"
        @close-all="$emit('close-all')"
    >
        <div class="space-y-1.5">
            <button
                v-for="currency in currencies"
                :key="currency"
                type="button"
                @click="selectCurrency(currency)"
                :class="[
                    'flex w-full items-center justify-between gap-3 rounded-xl border p-3 text-left transition',
                    current === currency ? 'border-lkblue bg-blue-50 text-blue-700' : 'border-slate-100 hover:bg-slate-50',
                ]"
            >
                <span class="text-sm font-bold">{{ currency }}</span>
                <i v-if="current === currency" data-lucide="check" class="text-lkblue2 h-4 w-4"></i>
            </button>
        </div>
    </SettingsSubModal>
</template>

<script setup>
import { DB } from '@/components/new_frontend/MockDataStore.js';
import SettingsSubModal from './SettingsSubModal.vue';

const props = defineProps({
    modelValue: Boolean,
    current: { type: String, default: 'USD (B$)' },
});

const emit = defineEmits(['update:modelValue', 'close-all', 'updated', 'toast']);
const currencies = ['USD (B$)', 'JMD ($)', 'TTD ($)', 'BBD ($)', 'DOP (RD$)', 'XCD ($)'];

const selectCurrency = (currency) => {
    const pref = DB.get('lk_settings_preferences', { currency: 'USD (B$)', region: 'Anguilla', darkMode: false });
    pref.currency = currency;
    DB.set('lk_settings_preferences', pref);
    emit('updated', pref);
    emit('toast', `Currency set to ${currency}`);
};
</script>
