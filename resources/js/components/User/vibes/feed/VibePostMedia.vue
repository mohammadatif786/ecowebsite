<template>
  <!-- A post's picture area: a text card, or its photos / videos (same carousel as U Vibe posts). -->
  <div class="relative bg-black w-full">
    <VibeTextCard v-if="isText" :post="post" />
    <template v-else>
      <PostMedia :media="post.media || []" />
      <span v-if="hasVideo" class="absolute top-3 left-3 bg-black/55 text-white text-[10px] font-black px-2 py-1 rounded-full flex items-center gap-1 pointer-events-none">
        <Play class="w-3 h-3 fill-white" />Video
      </span>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Play } from 'lucide-vue-next';
import PostMedia from '../../uvibe/feed/PostMedia.vue';
import VibeTextCard from './VibeTextCard.vue';

const props = defineProps({ post: { type: Object, required: true } });
const isText = computed(() => props.post.kind === 'text' || (!props.post.media?.length && !!props.post.text_bg));
const hasVideo = computed(() => (props.post.media || []).some((m) => m.type === 'video'));
</script>
