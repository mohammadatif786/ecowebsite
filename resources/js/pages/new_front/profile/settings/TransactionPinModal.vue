<template>
    <div v-if="modelValue" class="fade fixed inset-0 z-[120] flex items-end justify-center bg-slate-900/60 p-2 backdrop-blur-sm sm:items-center sm:p-4" @click.self="close">
        <div class="card w-full max-w-md overflow-hidden bg-white shadow-2xl">
            <!-- Header -->
            <div class="flex items-center justify-between p-5 text-white" style="background: linear-gradient(135deg, #0b2942, #1e3a5f)">
                <h3 class="text-xl font-black">Transaction PIN</h3>
                <button @click="close" class="rounded-lg p-1 transition hover:bg-white/20">
                    <i data-lucide="x" class="h-6 w-6"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="p-8 text-center">
                <!-- Shield Icon -->
                <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-sky-500 text-white shadow-lg shadow-sky-500/30">
                    <i data-lucide="shield" class="h-8 w-8"></i>
                </div>

                <h2 class="mb-1 text-2xl font-black text-slate-900">Create a 4-digit PIN</h2>
                <p class="mb-8 font-semibold text-slate-500">Used every time you send money</p>

                <!-- PIN Inputs -->
                <div class="mb-10 flex justify-center gap-3">
                    <input
                        v-for="i in 4"
                        :key="i"
                        ref="pinInputs"
                        v-model="pin[i - 1]"
                        type="text"
                        inputmode="numeric"
                        maxlength="1"
                        class="h-16 w-14 rounded-2xl border-2 border-slate-100 bg-slate-50 text-center text-2xl font-black text-slate-900 outline-none transition focus:border-lkblue focus:bg-white focus:ring-4 focus:ring-lkblue/10"
                        @input="handleInput($event, i - 1)"
                        @keydown.backspace="handleBackspace($event, i - 1)"
                    />
                </div>

                <!-- Continue Button -->
                <button
                    @click="handleContinue"
                    class="w-full rounded-2xl bg-gradient-to-r from-sky-500 to-blue-600 py-4 text-lg font-black text-white shadow-lg transition hover:brightness-110 active:scale-[0.98]"
                >
                    Continue
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { nextTick, ref, watch } from 'vue';

const props = defineProps({
    modelValue: Boolean
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const pin = ref(['', '', '', '']);
const pinInputs = ref([]);

const open = () => {
    isOpen.value = true;
    pin.value = ['', '', '', ''];
    nextTick(() => {
        if (window.lucide) window.lucide.createIcons();
        if (pinInputs.value[0]) pinInputs.value[0].focus();
    });
};

const close = () => {
    isOpen.value = false;
    emit('update:modelValue', false);
};

watch(() => props.modelValue, (newVal) => {
    if (newVal) open();
    else isOpen.value = false;
});

const handleInput = (e, index) => {
    const val = e.data || e.target.value;
    if (!/^\d$/.test(val)) {
        pin.value[index] = '';
        return;
    }
    pin.value[index] = val;
    if (index < 3 && val) {
        pinInputs.value[index + 1].focus();
    }
};

const handleBackspace = (e, index) => {
    if (e.key === 'Backspace' && !pin.value[index] && index > 0) {
        pinInputs.value[index - 1].focus();
    }
};

const handleContinue = () => {
    const finalPin = pin.value.join('');
    if (finalPin.length < 4) {
        alert('Please enter a 4-digit PIN');
        return;
    }
    // Logic for setting PIN would go here
    alert('PIN set successfully: ' + finalPin);
    close();
};

defineExpose({ open, close });
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
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
