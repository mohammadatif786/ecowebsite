<template>
  <!-- A words-only post: the caption on its colour or gradient, in the look the poster picked. -->
  <div class="w-full h-[400px] md:h-[500px] p-8 grid place-items-center text-center overflow-hidden"
       :style="{ background: post.text_bg || 'linear-gradient(135deg, #a855f7, #ec4899)' }">
    <p class="text-white text-2xl md:text-3xl leading-relaxed px-4 whitespace-pre-line break-words"
       :class="font?.class || 'font-black'" :style="font?.style || ''">{{ post.caption }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { vibeFont } from '../../../../lib/vibeFonts';

const props = defineProps({ post: { type: Object, required: true } });
const font = computed(() => vibeFont(props.post.text_font));
</script>
