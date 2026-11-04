<template>
  <!-- The campus a post is on. Tapping it opens that campus's U Vibe feed. -->
  <button v-if="campus" type="button" @click="$emit('open', campus)" :title="'Open ' + post.campus + ' on U Vibe'"
          class="rounded-full px-2 py-0.5 text-[11px] font-black flex items-center gap-1 min-w-0 hover:bg-violet-100"
          style="background:#f5f3ff;color:#5b21b6">
    <span class="shrink-0">{{ post.campus_flag || '🎓' }}</span><span class="truncate">{{ post.campus }}</span><span class="shrink-0">›</span>
  </button>
  <span v-else class="text-[11px] font-bold text-slate-500 truncate">🎓 {{ post.campus || '' }}</span>
</template>

<script setup>
defineProps({
  post: { type: Object, required: true },
  // The campus to open (null when the post's campus isn't known).
  campus: { type: Object, default: null },
});
defineEmits(['open']);
</script>
