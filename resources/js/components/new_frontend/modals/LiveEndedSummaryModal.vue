<template>
    <Modal ref="modalRef" maxWidth="max-w-md">
        <div class="overflow-hidden rounded-3xl bg-white p-7 text-center text-slate-950 shadow-2xl">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-800 text-white">
                <i data-lucide="radio" class="h-7 w-7"></i>
            </div>

            <h3 class="mt-4 text-2xl font-black">Stream ended</h3>
            <p class="mt-1 text-sm font-bold text-slate-500">{{ stream.title }}</p>
            <p class="mt-1 text-xs font-semibold text-slate-400">{{ stream.host }} · {{ stream.category }}</p>

            <div class="mt-6 grid grid-cols-3 gap-3">
                <div class="rounded-2xl border border-slate-200 p-3">
                    <p class="text-xl font-black">{{ num(stream.viewers) }}</p>
                    <p class="text-[10px] font-black uppercase text-slate-400">Viewers</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-3">
                    <p class="text-xl font-black">{{ num(stream.likes) }}</p>
                    <p class="text-[10px] font-black uppercase text-slate-400">Hearts</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-3">
                    <p class="text-xl font-black">{{ num(stream.gifts) }}</p>
                    <p class="text-[10px] font-black uppercase text-slate-400">Gifts</p>
                </div>
            </div>

            <p class="mt-5 text-xs font-bold text-slate-400">
                {{ duration(stream.duration_seconds) }} · No replay is available.
            </p>
            <button type="button" @click="close" class="mt-6 h-11 w-full rounded-xl bg-blue-600 text-sm font-black text-white hover:bg-blue-700">
                Done
            </button>
        </div>
    </Modal>
</template>

<script setup>
import { nextTick, ref } from 'vue';
import Modal from '../ui/Modal.vue';

const modalRef = ref(null);
const stream = ref({});
const num = (value) => Number(value || 0).toLocaleString();
const duration = (seconds) => {
    const total = Math.max(0, Number(seconds || 0));
    const minutes = Math.floor(total / 60);
    const remaining = Math.floor(total % 60);
    return `${String(minutes).padStart(2, '0')}:${String(remaining).padStart(2, '0')}`;
};

const open = (value) => {
    stream.value = { ...value };
    modalRef.value?.open?.();
    nextTick(() => window.lucide?.createIcons?.());
};

const close = () => modalRef.value?.close?.();

defineExpose({ open, close });
</script>
