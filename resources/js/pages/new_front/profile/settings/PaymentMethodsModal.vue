<template>
    <SettingsSubModal
        :model-value="modelValue"
        title="Payment methods"
        icon="credit-card"
        color="#8b5cf6"
        @back="$emit('update:modelValue', false)"
        @close-all="$emit('close-all')"
    >
        <div class="mb-4 space-y-2">
            <div v-if="localMethods.length">
                <div v-for="method in localMethods" :key="method.id" class="payment-row mb-2">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-violet-50 text-violet-600">
                        <i data-lucide="credit-card" class="h-4 w-4"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold">
                            {{ method.brand }} &bull;&bull;&bull;&bull; {{ method.last4 }}
                            <span v-if="method.isDefault" class="text-[10px] font-black text-emerald-600">DEFAULT</span>
                        </p>
                        <p class="text-[11px] text-slate-400">Expires {{ method.expiry }}</p>
                    </div>
                    <button
                        type="button"
                        @click="removeMethod(method.id)"
                        class="grid h-8 w-8 shrink-0 place-items-center rounded-lg text-rose-600 hover:bg-rose-50"
                    >
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>
            <p v-else class="py-6 text-center text-sm text-slate-400">No cards added yet.</p>

            <div class="payment-row">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-red-50 text-xs font-black text-red-600">S</span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold">{{ bank.bankName }} {{ bank.type }} &bull;&bull;{{ bank.last4 }}</p>
                    <p class="text-[11px] text-slate-400">Linked bank account</p>
                </div>
            </div>
        </div>
        <button type="button" @click="$emit('open-add')" class="btn btn-primary w-full py-3">
            <i data-lucide="plus" class="mr-1 inline h-4 w-4"></i>Add Card
        </button>
    </SettingsSubModal>
</template>

<script setup>
import { DB } from '@/components/new_frontend/MockDataStore.js';
import { ref, watch } from 'vue';
import SettingsSubModal from './SettingsSubModal.vue';

const props = defineProps({
    modelValue: Boolean,
    methods: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue', 'close-all', 'updated', 'toast', 'open-add']);
const localMethods = ref([]);
const bank = DB.get('lk_my_bank', { bankName: 'Scotiabank', type: 'Checking', last4: '0100' });

const sync = () => {
    localMethods.value = props.methods.map((method) => ({ ...method }));
};

const removeMethod = (id) => {
    localMethods.value = localMethods.value.filter((method) => method.id !== id);
    if (localMethods.value.length && !localMethods.value.some((method) => method.isDefault)) {
        localMethods.value[0].isDefault = true;
    }
    DB.set('lk_payment_methods', localMethods.value);
    emit('updated', localMethods.value);
    emit('toast', 'Card removed');
};

watch(() => props.methods, sync, { immediate: true, deep: true });
</script>

<style scoped>
.payment-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-radius: 0.75rem;
    border: 1px solid #f1f5f9;
    padding: 0.75rem;
}
</style>
