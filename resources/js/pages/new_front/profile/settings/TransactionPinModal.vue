<template>
    <div
        v-if="modelValue"
        class="fade fixed inset-0 z-[120] flex items-end justify-center bg-slate-900/60 p-2 backdrop-blur-sm sm:items-center sm:p-4"
        @click.self="close"
    >
        <div class="card w-full max-w-md overflow-hidden bg-white shadow-2xl">
            <!-- Header -->
            <div class="flex items-center justify-between p-5 text-white" style="background: linear-gradient(135deg, #0b2942, #1e3a5f)">
                <h3 class="text-xl font-black">Transaction PIN</h3>
                <button @click="close" type="button" class="rounded-lg p-1 transition hover:bg-white/20">
                    <i data-lucide="x" class="h-6 w-6"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="p-8 text-center">
                <!-- Back button when multi-step -->
                <div v-if="canGoBack" class="mb-2 flex justify-start">
                    <button
                        type="button"
                        @click="goBackStep"
                        class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500 hover:text-slate-800 transition"
                    >
                        <i data-lucide="arrow-left" class="h-4 w-4"></i> Back
                    </button>
                </div>

                <!-- Shield Icon -->
                <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-sky-500 text-white shadow-lg shadow-sky-500/30">
                    <i data-lucide="shield" class="h-8 w-8"></i>
                </div>

                <h2 class="mb-1 text-2xl font-black text-slate-900">{{ titleText }}</h2>
                <p class="mb-6 font-semibold text-slate-500">{{ subtitleText }}</p>

                <p v-if="errorMessage" class="mb-4 rounded-xl bg-red-50 p-2.5 text-xs font-extrabold text-red-600">
                    {{ errorMessage }}
                </p>

                <!-- PIN Inputs -->
                <div class="mb-8 flex justify-center gap-3">
                    <input
                        v-for="i in 4"
                        :key="i"
                        ref="pinInputs"
                        v-model="pinDigits[i - 1]"
                        type="password"
                        inputmode="numeric"
                        maxlength="1"
                        class="h-16 w-14 rounded-2xl border-2 border-slate-100 bg-slate-50 text-center text-2xl font-black text-slate-900 outline-none transition focus:border-lkblue focus:bg-white focus:ring-4 focus:ring-lkblue/10"
                        @input="handleInput($event, i - 1)"
                        @keydown.backspace="handleBackspace($event, i - 1)"
                        @keydown.enter="handleContinue"
                    />
                </div>

                <!-- Continue Button -->
                <button
                    type="button"
                    @click="handleContinue"
                    :disabled="form.processing"
                    class="w-full rounded-2xl bg-gradient-to-r from-sky-500 to-blue-600 py-4 text-lg font-black text-white shadow-lg transition hover:brightness-110 active:scale-[0.98] disabled:opacity-50"
                >
                    {{ form.processing ? 'Saving...' : 'Continue' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';

const props = defineProps({
    modelValue: Boolean,
    hasPin: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'toast', 'updated', 'close-all']);

const isOpen = ref(false);
const step = ref('create'); // 'current' | 'create' | 'confirm_create'
const pinDigits = ref(['', '', '', '']);
const pinInputs = ref([]);
const errorMessage = ref('');

const form = useForm({
    current_pin: '',
    pin: '',
    pin_confirmation: '',
});

const titleText = computed(() => {
    if (step.value === 'current') return 'Enter Current PIN';
    if (step.value === 'create') return props.hasPin ? 'Enter New 4-digit PIN' : 'Create a 4-digit PIN';
    if (step.value === 'confirm_create') return 'Confirm 4-digit PIN';
    return 'Transaction PIN';
});

const subtitleText = computed(() => {
    if (step.value === 'current') return 'Verify your current 4-digit PIN to proceed';
    if (step.value === 'create') return 'Used every time you send money or make transactions';
    if (step.value === 'confirm_create') return 'Re-enter your 4-digit PIN to confirm';
    return '';
});

const canGoBack = computed(() => {
    if (step.value === 'confirm_create') return true;
    if (step.value === 'create' && props.hasPin) return true;
    return false;
});

const focusFirst = () => {
    nextTick(() => {
        if (window.lucide) window.lucide.createIcons();
        if (pinInputs.value && pinInputs.value[0]) {
            pinInputs.value[0].focus();
        }
    });
};

const resetDigits = () => {
    pinDigits.value = ['', '', '', ''];
    focusFirst();
};

const resetAll = () => {
    form.reset();
    form.clearErrors();
    errorMessage.value = '';
    step.value = props.hasPin ? 'current' : 'create';
    resetDigits();
};

const open = () => {
    isOpen.value = true;
    resetAll();
};

const close = () => {
    isOpen.value = false;
    emit('update:modelValue', false);
};

const goBackStep = () => {
    errorMessage.value = '';
    if (step.value === 'confirm_create') {
        step.value = 'create';
    } else if (step.value === 'create' && props.hasPin) {
        step.value = 'current';
    }
    resetDigits();
};

const handleInput = (e, index) => {
    const val = e.data || e.target.value;
    if (!/^\d$/.test(val)) {
        pinDigits.value[index] = '';
        return;
    }
    pinDigits.value[index] = val;
    if (index < 3 && val) {
        if (pinInputs.value[index + 1]) {
            pinInputs.value[index + 1].focus();
        }
    }
};

const handleBackspace = (e, index) => {
    if (e.key === 'Backspace' && !pinDigits.value[index] && index > 0) {
        if (pinInputs.value[index - 1]) {
            pinInputs.value[index - 1].focus();
        }
    }
};

const handleContinue = () => {
    errorMessage.value = '';
    const digits = pinDigits.value.join('');
    if (digits.length < 4) {
        errorMessage.value = 'Please enter a 4-digit PIN';
        return;
    }

    if (step.value === 'current') {
        form.current_pin = digits;
        step.value = 'create';
        resetDigits();
        return;
    }

    if (step.value === 'create') {
        form.pin = digits;
        step.value = 'confirm_create';
        resetDigits();
        return;
    }

    if (step.value === 'confirm_create') {
        if (digits !== form.pin) {
            errorMessage.value = 'PINs do not match. Please try again.';
            resetDigits();
            return;
        }
        form.pin_confirmation = digits;

        form.put('/new_frontend/profile/transaction-pin', {
            preserveScroll: true,
            onSuccess: () => {
                resetAll();
                emit('toast', props.hasPin ? 'Transaction PIN updated successfully' : 'Transaction PIN created successfully');
                emit('updated', true);
                close();
            },
            onError: (errors) => {
                const msg = errors.current_pin || errors.pin || errors.pin_confirmation || 'Failed to update PIN. Please try again.';
                errorMessage.value = Array.isArray(msg) ? msg[0] : msg;
                if (errors.current_pin && props.hasPin) {
                    step.value = 'current';
                } else {
                    step.value = 'create';
                }
                resetDigits();
            },
        });
    }
};

watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal) open();
        else isOpen.value = false;
    },
);

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
