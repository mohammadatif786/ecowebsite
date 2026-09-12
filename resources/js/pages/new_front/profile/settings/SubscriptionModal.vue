<template>
    <SettingsSubModal
        :model-value="modelValue"
        title="Subscription"
        icon="crown"
        color="#eab308"
        @back="$emit('update:modelValue', false)"
        @close-all="$emit('close-all')"
    >
        <div class="space-y-1.5">
            <button
                v-for="plan in plans"
                :key="plan"
                type="button"
                @click="selectPlan(plan)"
                :class="[
                    'flex w-full items-center justify-between gap-3 rounded-xl border p-3 text-left transition',
                    current === plan ? 'border-lkblue bg-blue-50 text-blue-700' : 'border-slate-100 hover:bg-slate-50',
                ]"
            >
                <span class="text-sm font-bold">{{ plan }}</span>
                <i v-if="current === plan" data-lucide="check" class="text-lkblue2 h-4 w-4"></i>
            </button>
        </div>
    </SettingsSubModal>
</template>

<script setup>
import { DB } from '@/components/new_frontend/MockDataStore.js';
import SettingsSubModal from './SettingsSubModal.vue';

const props = defineProps({
    modelValue: Boolean,
    current: { type: String, default: 'Brisa' },
});

const emit = defineEmits(['update:modelValue', 'close-all', 'updated', 'toast']);
const plans = ['Free', 'Brisa', 'Premium'];

const selectPlan = (plan) => {
    const extra = DB.get('lk_settings_account_extra', { subscriptionPlan: 'Brisa', verified: false });
    extra.subscriptionPlan = plan;
    DB.set('lk_settings_account_extra', extra);
    emit('updated', extra);
    emit('toast', `Subscription set to ${plan}`);
};
</script>
