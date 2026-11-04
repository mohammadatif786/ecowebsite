<template>
    <div
        v-if="modelValue"
        class="fade fixed inset-0 z-[115] flex items-end justify-center bg-slate-900/60 p-2 backdrop-blur-sm sm:items-center sm:p-4"
    >
        <div class="card w-full max-w-sm bg-white p-5 text-center shadow-2xl">
            <span class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-rose-50 text-rose-600">
                <i data-lucide="triangle-alert" class="h-6 w-6"></i>
            </span>
            <h3 class="mb-1 text-lg font-black">Delete your account?</h3>
            <p class="mb-5 text-sm text-slate-400">This is a demo build, so account deletion is disabled here and your test data stays intact.</p>
            <button type="button" @click="$emit('update:modelValue', false)" class="btn btn-ghost w-full py-2.5">Okay, got it</button>
        </div>
    </div>
</template>

<script setup>
import { watch } from 'vue';

const props = defineProps({ modelValue: Boolean });
defineEmits(['update:modelValue']);

watch(
    () => props.modelValue,
    (open) => {
        if (open)
            setTimeout(() => {
                if (window.lucide) window.lucide.createIcons();
            }, 10);
    },
);
</script>

<style scoped>
.card {
    border-radius: 32px;
}
@media (max-width: 640px) {
    .card {
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }
}
</style>
