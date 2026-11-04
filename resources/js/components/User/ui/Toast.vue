<template>
  <div class="toast" :class="{ show: isVisible }">
    {{ message }}
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const isVisible = ref(false);
const message = ref('');
let timeoutId = null;

// Expose a method to be called from a global event bus or provide/inject
const showToast = (msg, duration = 2200) => {
  message.value = msg;
  isVisible.value = true;
  
  if (timeoutId) {
    clearTimeout(timeoutId);
  }
  
  timeoutId = setTimeout(() => {
    isVisible.value = false;
  }, duration);
};

defineExpose({
  showToast
});
</script>
