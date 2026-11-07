<template>
  <!-- The list of posts, with the "Showing posts tagged #x" line when a tag is picked. -->
  <div class="space-y-4">
    <div v-if="activeTag" class="card p-3 flex items-center justify-between bg-blue-50 border-blue-100">
      <p class="text-sm font-bold text-lkblue2">Showing posts tagged {{ activeTag }}</p>
      <button type="button" class="text-xs font-black text-slate-500 hover:text-slate-800" @click="actions.filterTag(null)">Clear ✕</button>
    </div>
    <VibePostCard v-for="p in posts" :key="p.id" :post="p" @reposted="(v) => $emit('reposted', v)" />
    <div v-if="!posts.length" class="card p-10 text-center">
      <p class="text-slate-400 font-bold">{{ activeTag ? 'No posts with this tag yet.' : 'No posts yet — share the first vibe!' }}</p>
    </div>
  </div>
</template>

<script setup>
import VibePostCard from './VibePostCard.vue';
import { useVibeActions } from '../useVibeActions';

defineProps({
  posts: { type: Array, default: () => [] },
  activeTag: { type: String, default: null },
});
defineEmits(['reposted']);
const actions = useVibeActions();
</script>
