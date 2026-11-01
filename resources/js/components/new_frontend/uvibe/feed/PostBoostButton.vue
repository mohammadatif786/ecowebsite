<template>
  <!-- "Send a Boost" — the big button or the small 🌹. Both open the Boost window. -->
  <button v-if="variant === 'rose'" type="button" @click="$emit('open', post)" title="Send a Boost" aria-label="Send a Boost" class="text-lg shrink-0">🌹</button>
  <button v-else type="button" @click="$emit('open', post)"
          class="flex items-center gap-1.5 text-white bg-gradient-to-r from-orange-500 to-pink-500 px-4 py-2 rounded-full text-sm font-black shadow-md hover:shadow-lg">
    <Zap class="w-4 h-4" />Send a Boost
  </button>
</template>

<script setup>
import { Zap } from 'lucide-vue-next';

defineProps({
  post: { type: Object, required: true },
  variant: { type: String, default: 'button' }, // 'button' | 'rose'
});
defineEmits(['open']);
</script>
