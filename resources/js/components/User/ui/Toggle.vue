<template>
  <button
    @click="$emit('update:modelValue', !modelValue)"
    :class="[
      'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none',
      modelValue ? 'bg-lkblue' : 'bg-slate-200'
    ]"
  >
    <span
      aria-hidden="true"
      :class="[
        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
        modelValue ? 'translate-x-5' : 'translate-x-0'
      ]"
    />
  </button>
</template>

<script setup>
defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
});
defineEmits(['update:modelValue']);
</script>
