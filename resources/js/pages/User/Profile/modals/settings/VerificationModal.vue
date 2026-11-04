<template>
    <SettingsSubModal
        :model-value="modelValue"
        title="Verification"
        icon="badge-check"
        color="#2563eb"
        @back="$emit('update:modelValue', false)"
        @close-all="$emit('close-all')"
    >
        <div class="mb-3 rounded-2xl border border-slate-100 p-4 text-center">
            <span
                :class="[
                    'mx-auto mb-2 grid h-14 w-14 place-items-center rounded-2xl',
                    verified ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400',
                ]"
            >
                <i data-lucide="badge-check" class="h-6 w-6"></i>
            </span>
            <p class="font-black">{{ verified ? 'Verified' : 'Not verified' }}</p>
            <p class="mt-1 text-[12px] text-slate-400">Required to send/withdraw money, sell, and get the verified badge.</p>
        </div>

        <button type="button" @click="$emit('toast', 'Photo ID upload coming soon')" class="kyc-row mb-2">
            <span class="kyc-icon"><i data-lucide="id-card" class="h-4 w-4"></i></span>
            <span class="flex-1 text-sm font-bold">Upload a photo ID</span>
            <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300"></i>
        </button>
        <button type="button" @click="$emit('toast', 'Selfie capture coming soon')" class="kyc-row">
            <span class="kyc-icon"><i data-lucide="camera" class="h-4 w-4"></i></span>
            <span class="flex-1 text-sm font-bold">Take a selfie</span>
            <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300"></i>
        </button>
    </SettingsSubModal>
</template>

<script setup>
import SettingsSubModal from './SettingsSubModal.vue';

defineProps({
    modelValue: Boolean,
    verified: Boolean,
});

defineEmits(['update:modelValue', 'close-all', 'toast']);
</script>

<style scoped>
.kyc-row {
    display: flex;
    width: 100%;
    align-items: center;
    gap: 0.75rem;
    border-radius: 0.75rem;
    border: 1px solid #f1f5f9;
    padding: 0.75rem;
    text-align: left;
    transition: background 0.15s ease;
}
.kyc-row:hover {
    background: #f8fafc;
}
.kyc-icon {
    display: grid;
    height: 2.25rem;
    width: 2.25rem;
    place-items: center;
    border-radius: 0.5rem;
    background: #eff6ff;
    color: #2563eb;
}
</style>
