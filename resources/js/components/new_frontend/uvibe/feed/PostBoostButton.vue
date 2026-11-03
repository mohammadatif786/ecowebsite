<template>
  <!-- "Send a Boost" — the big button or the small 🌹. Both open the Boost window (on your own post they explain why not). -->
  <button v-if="variant === 'rose'" type="button" @click="press" title="Send a Boost" aria-label="Send a Boost" class="text-lg shrink-0">🌹</button>
  <button v-else type="button" @click="press"
          class="flex items-center gap-1.5 text-white bg-gradient-to-r from-orange-500 to-pink-500 px-4 py-2 rounded-full text-sm font-black shadow-md hover:shadow-lg">
    <Zap class="w-4 h-4" />Send a Boost
  </button>
</template>

<script setup>
import { Zap } from 'lucide-vue-next';
import { notify } from '../../../../services/uvibe';

const props = defineProps({
  post: { type: Object, required: true },
  variant: { type: String, default: 'button' }, // 'button' | 'rose'
});
const emit = defineEmits(['open']);

// Boosts are coins from classmates, so you can't Boost your own post (the server refuses it too).
const press = () => {
  if (props.post.mine) { notify('You can’t Boost your own post — your classmates can ⚡'); return; }
  emit('open', props.post);
};
</script>
