<template>
  <!-- The tags people are using most, with how many posts use each. Tap one to filter the feed. -->
  <div class="card p-4">
    <h3 class="font-black mb-3">Trending tags</h3>
    <div v-if="tags.length" class="flex flex-wrap gap-2">
      <button v-for="t in tags" :key="t.tag" type="button" :class="['chip', active === t.tag ? 'on' : '']" @click="actions.filterTag(active === t.tag ? null : t.tag)">
        {{ t.tag }} <b class="opacity-60">· {{ t.count }}</b>
      </button>
    </div>
    <p v-else class="text-[11px] text-slate-400">No tags yet — add a #hashtag to your caption when you post to start one.</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useVibeActions } from '../useVibeActions';

const props = defineProps({
  // [{ tag: '#fete', count: 12 }] — older pages sent plain strings, which still work.
  items: { type: Array, default: () => [] },
  active: { type: String, default: null },
});
const actions = useVibeActions();
const tags = computed(() => props.items.map((t) => (typeof t === 'string' ? { tag: t, count: 1 } : t)));
</script>
