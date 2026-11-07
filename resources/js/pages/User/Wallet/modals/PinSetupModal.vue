<template>
  <div v-if="isOpen" class="fixed inset-0 z-[130] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="close">
    <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden">
      <div class="p-5 text-white flex items-center justify-between" style="background:linear-gradient(135deg,#0b2942,#1e3a5f)">
        <h3 class="text-xl font-black">Transaction PIN</h3>
        <button @click="close" class="hover:bg-white/20 p-1 rounded transition" aria-label="Close"><i data-lucide="x" class="w-5 h-5"></i></button>
      </div>

      <div class="p-5 text-center">
        <span class="h-12 w-12 rounded-2xl bg-lkblue text-white grid place-items-center mx-auto shadow">
          <i data-lucide="shield" class="w-6 h-6"></i>
        </span>
        <p class="font-black text-lg mt-3">{{ stepTitle }}</p>
        <p class="text-[12px] text-slate-500 font-bold">Used every time you send money</p>

        <div class="mt-5">
          <PinBoxes v-if="step === 'current'" ref="boxes" v-model="current" label="Current PIN" @enter="next" />
          <PinBoxes v-else-if="step === 'new'" ref="boxes" v-model="pin" label="New PIN" @enter="next" />
          <PinBoxes v-else ref="boxes" v-model="confirm" label="Confirm PIN" @enter="next" />
        </div>

        <p v-if="error" class="text-[12px] font-bold text-rose-600 mt-4 bg-rose-50 p-2 rounded-xl">{{ error }}</p>

        <button @click="next" :disabled="saving" class="btn btn-primary w-full py-3 mt-5 disabled:opacity-60">
          {{ step === 'confirm' ? (saving ? 'Saving…' : 'Save PIN') : 'Continue' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import PinBoxes from '../components/PinBoxes.vue';

const props = defineProps({ hasPin: { type: Boolean, default: false } });
const emit = defineEmits(['saved']);

const isOpen = ref(false);
const step = ref('new');
const current = ref('');
const pin = ref('');
const confirm = ref('');
const error = ref('');
const saving = ref(false);
const boxes = ref(null);

const stepTitle = computed(() => ({
  current: 'Enter your current PIN',
  new: props.hasPin ? 'Choose a new 4-digit PIN' : 'Create a 4-digit PIN',
  confirm: 'Confirm your PIN',
}[step.value]));

const refresh = () => nextTick(() => { window.lucide?.createIcons(); boxes.value?.focus(); });

const open = () => {
  step.value = props.hasPin ? 'current' : 'new';
  current.value = pin.value = confirm.value = error.value = '';
  isOpen.value = true;
  refresh();
};
const close = () => { isOpen.value = false; };

const next = () => {
  error.value = '';
  if (step.value === 'current') {
    if (current.value.length < 4) { error.value = 'Enter your current 4-digit PIN.'; return; }
    step.value = 'new'; refresh(); return;
  }
  if (step.value === 'new') {
    if (pin.value.length < 4) { error.value = 'Enter 4 digits.'; return; }
    step.value = 'confirm'; refresh(); return;
  }
  if (confirm.value !== pin.value) { error.value = "PINs don't match. Try again."; confirm.value = ''; refresh(); return; }

  saving.value = true;
  router.put(route('user.profile.transaction_pin.update'), {
    pin: pin.value,
    pin_confirmation: confirm.value,
    ...(props.hasPin ? { current_pin: current.value } : {}),
  }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: (page) => {
      if (page.props?.errors && Object.keys(page.props.errors).length) return;
      close();
      window.toast?.('🔒 Transaction PIN saved');
      emit('saved');
    },
    onError: (errors) => {
      error.value = errors.current_pin || errors.pin || errors.pin_confirmation || 'Could not save your PIN.';
      if (errors.current_pin) { step.value = 'current'; current.value = ''; }
      else { step.value = 'new'; pin.value = confirm.value = ''; }
      refresh();
    },
    onFinish: () => { saving.value = false; },
  });
};

defineExpose({ open });
</script>
