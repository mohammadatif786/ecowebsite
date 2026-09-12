<template>
  <div class="modal-wrap" :class="{ open: isOpen }" @click.self="close">
    <div :class="['card w-full max-h-[88vh] overflow-y-auto fade', maxWidth]">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  maxWidth: {
    type: String,
    default: 'max-w-lg'
  }
});

const isOpen = ref(false);

const open = () => isOpen.value = true;
const close = () => isOpen.value = false;

defineExpose({ open, close });
</script>
