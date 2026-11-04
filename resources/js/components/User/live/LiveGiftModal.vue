<template>
    <div v-if="open" class="fixed inset-0 z-[260] grid place-items-center bg-black/45 p-4 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="w-full max-w-[514px] overflow-hidden rounded-[20px] bg-white shadow-2xl ring-1 ring-white/50">
            <div class="flex items-center justify-between px-5 py-4 text-white" style="background: linear-gradient(120deg, #e11d48, #f59e0b)">
                <div class="flex min-w-0 items-center gap-3">
                    <i data-lucide="gift" class="h-6 w-6 shrink-0"></i>
                    <div class="min-w-0">
                        <h2 class="truncate text-2xl leading-none font-black">Send a Live Gift</h2>
                        <p class="mt-1 text-sm font-bold text-white/90">Balance: {{ num(balance) }}</p>
                    </div>
                </div>
                <button @click="$emit('close')" class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-white transition hover:bg-white/15">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <div class="grid grid-cols-3 gap-2.5 p-3">
                <p v-if="!gifts.length" class="col-span-3 rounded-xl bg-slate-50 px-4 py-8 text-center text-sm font-bold text-slate-500">
                    No live gifts are available right now.
                </p>
                <button
                    v-for="gift in gifts"
                    :key="gift.id"
                    @click="$emit('select', gift)"
                    :disabled="isSending"
                    class="group flex min-h-[96px] flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white px-2 py-3 text-center shadow-sm transition hover:-translate-y-0.5 hover:border-orange-300 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-45"
                >
                    <span class="gift-icon text-3xl leading-none drop-shadow-sm">{{ gift.emoji }}</span>
                    <span class="mt-2 text-[13px] leading-tight font-black text-slate-950">{{ gift.name }}</span>
                    <span class="mt-1 text-[11px] leading-none font-black text-orange-600">{{ gift.coins }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { nextTick, onMounted, watch } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    balance: { type: Number, default: 0 },
    gifts: { type: Array, default: () => [] },
    isSending: { type: Boolean, default: false },
});

defineEmits(['close', 'select']);

const num = (n) => Number(n || 0).toLocaleString();

const refreshIcons = () => {
    nextTick(() => {
        if (window.lucide) window.lucide.createIcons();
    });
};

onMounted(refreshIcons);
watch(() => props.open, refreshIcons);
</script>

<style scoped>
.gift-icon {
    -webkit-text-stroke: 1px rgba(15, 23, 42, 0.9);
}
</style>
