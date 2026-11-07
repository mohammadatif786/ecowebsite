<template>
  <!-- "✨ Vibes" title with the section's buttons: messages, reels, say thank you, new post. -->
  <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
    <div>
      <h1 class="text-2xl md:text-3xl font-black text-lkink">{{ title }}</h1>
      <p class="text-slate-500 font-semibold mt-1">{{ subtitle }}</p>
    </div>
    <div class="flex items-center gap-2">
      <button type="button" title="Messages" aria-label="Messages" class="btn btn-ghost h-11 w-11 grid place-items-center" @click="$emit('messages')"><MessageCircle class="w-4 h-4" /></button>
      <button type="button" title="Reels" aria-label="Reels" class="btn btn-ghost h-11 w-11 grid place-items-center" @click="$emit('reels')"><Clapperboard class="w-4 h-4" /></button>
      <button v-if="showThanks" type="button" title="Say Thank You" aria-label="Say Thank You" class="btn btn-ghost h-11 w-11 grid place-items-center text-lg" @click="$emit('thanks')">🙏</button>
      <button type="button" class="btn btn-primary px-4 py-2.5 flex items-center gap-2" @click="$emit('post')"><Plus class="w-4 h-4" />Post</button>
    </div>
  </div>
</template>

<script setup>
import { Clapperboard, MessageCircle, Plus } from 'lucide-vue-next';

defineProps({
  title: { type: String, default: '✨ Vibes' },
  subtitle: { type: String, default: 'Photos, reels & shoppable moments' },
  showThanks: { type: Boolean, default: true },
});
defineEmits(['messages', 'reels', 'thanks', 'post']);
</script>
