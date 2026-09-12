<template>
    <SettingsSubModal
        :model-value="modelValue"
        title="Add Card"
        icon="plus"
        color="#8b5cf6"
        @back="$emit('back-to-payment')"
        @close-all="$emit('close-all')"
    >
        <div class="space-y-3">
            <label class="block">
                <span class="text-xs font-black text-slate-500">Card Brand</span>
                <select v-model="brand" class="field">
                    <option>Visa</option>
                    <option>Mastercard</option>
                    <option>Amex</option>
                </select>
            </label>
            <label class="block">
                <span class="text-xs font-black text-slate-500">Last 4 Digits</span>
                <input v-model="last4" maxlength="4" placeholder="4242" class="field" />
            </label>
            <label class="block">
                <span class="text-xs font-black text-slate-500">Expiry (MM/YY)</span>
                <input v-model="expiry" placeholder="09/28" class="field" />
            </label>
        </div>
        <button type="button" @click="save" class="btn btn-primary mt-4 w-full py-3">Save Card</button>
    </SettingsSubModal>
</template>

<script setup>
import { DB } from '@/components/new_frontend/MockDataStore.js';
import { ref, watch } from 'vue';
import SettingsSubModal from './SettingsSubModal.vue';

const props = defineProps({ modelValue: Boolean });
const emit = defineEmits(['back-to-payment', 'close-all', 'updated', 'toast']);

const brand = ref('Visa');
const last4 = ref('');
const expiry = ref('');

const save = () => {
    const methods = DB.get('lk_payment_methods', []);
    const cleanLast4 = last4.value.replace(/\D/g, '').slice(-4) || '0000';
    const nextMethods = [
        ...methods,
        {
            id: Date.now(),
            brand: brand.value || 'Visa',
            last4: cleanLast4,
            expiry: expiry.value || '12/29',
            isDefault: methods.length === 0,
        },
    ];
    DB.set('lk_payment_methods', nextMethods);
    emit('updated', nextMethods);
    emit('toast', 'Card added');
    emit('back-to-payment');
};

watch(
    () => props.modelValue,
    (open) => {
        if (!open) return;
        brand.value = 'Visa';
        last4.value = '';
        expiry.value = '';
    },
);
</script>

<style scoped>
.field {
    margin-top: 0.25rem;
    width: 100%;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    padding: 0.75rem 1rem;
    outline: none;
}
.field:focus {
    border-color: #2f9bef;
}
</style>
