<template>
  <div class="flex justify-center gap-3" :aria-label="label">
    <input
      v-for="(_, index) in digits"
      :key="index"
      :ref="(el) => (inputs[index] = el)"
      :value="digits[index]"
      type="password"
      inputmode="numeric"
      pattern="[0-9]*"
      maxlength="1"
      autocomplete="off"
      class="h-14 w-12 sm:h-16 sm:w-14 rounded-2xl border-2 border-slate-200 bg-white text-center text-2xl font-black text-slate-900 outline-none transition focus:border-lkblue"
      :aria-label="`${label} digit ${index + 1}`"
      @input="onInput(index, $event)"
      @keydown.backspace="onBackspace(index, $event)"
      @keydown.enter="$emit('enter')"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'PIN' },
});
const emit = defineEmits(['update:modelValue', 'enter']);

const digits = ref(['', '', '', '']);
const inputs = ref([]);

watch(() => props.modelValue, (value) => {
  if (value === digits.value.join('')) return;
  digits.value = [0, 1, 2, 3].map((i) => (value || '')[i] || '');
}, { immediate: true });

const sync = () => emit('update:modelValue', digits.value.join(''));

const onInput = (index, event) => {
  const digit = String(event.target.value || '').replace(/\D/g, '').slice(-1);
  digits.value[index] = digit;
  event.target.value = digit;
  sync();
  if (digit && index < 3) inputs.value[index + 1]?.focus();
};

const onBackspace = (index, event) => {
  if (!digits.value[index] && index > 0) {
    event.preventDefault();
    digits.value[index - 1] = '';
    sync();
    inputs.value[index - 1]?.focus();
  }
};

const focus = () => inputs.value[digits.value.findIndex((d) => !d)]?.focus() ?? inputs.value[3]?.focus();
const clear = () => { digits.value = ['', '', '', '']; sync(); inputs.value[0]?.focus(); };

defineExpose({ focus, clear });
</script>
