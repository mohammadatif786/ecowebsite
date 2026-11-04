<template>
    <div
        v-if="modelValue"
        class="fade fixed inset-0 z-[110] flex items-end justify-center bg-slate-900/60 p-2 backdrop-blur-sm sm:items-center sm:p-4"
    >
        <div :class="['card custom-scroll max-h-[85vh] w-full overflow-y-auto bg-white p-4 shadow-2xl', maxWidth]">
            <div class="mb-4 flex items-center gap-3 border-b border-slate-100 pb-4">
                <button
                    type="button"
                    @click="$emit('back')"
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-slate-200 transition hover:bg-slate-50"
                >
                    <i data-lucide="arrow-left" class="h-5 w-5"></i>
                </button>
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl" :style="{ background: `${color}1a`, color }">
                    <i :data-lucide="icon" class="h-5 w-5"></i>
                </span>
                <h3 class="flex-1 truncate text-xl font-black text-slate-900">{{ title }}</h3>
                <button
                    type="button"
                    @click="$emit('close-all')"
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl transition hover:bg-slate-100"
                >
                    <i data-lucide="x" class="h-5 w-5 text-slate-500"></i>
                </button>
            </div>
            <slot />
        </div>
    </div>
</template>

<script setup>
import { watch } from 'vue';

const props = defineProps({
    modelValue: Boolean,
    title: { type: String, required: true },
    icon: { type: String, default: 'settings' },
    color: { type: String, default: '#2563eb' },
    maxWidth: { type: String, default: 'max-w-lg' },
});

defineEmits(['back', 'close-all']);

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
.custom-scroll::-webkit-scrollbar {
    width: 5px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
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
