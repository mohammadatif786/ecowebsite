<template>
  <!-- "View all comments" for a post: open(post, onCount). -->
  <Teleport to="body">
    <div v-if="post" class="fixed inset-0 z-[1000] bg-black/60 backdrop-blur-sm flex items-end md:items-center justify-center md:p-5" role="dialog" aria-modal="true" aria-label="Comments" @click.self="close">
      <div class="bg-white rounded-t-[32px] md:rounded-[32px] max-w-md w-full shadow-2xl overflow-hidden flex flex-col" style="height:85vh">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0">
          <h3 class="font-black text-xs uppercase tracking-[0.2em]">Comments · {{ compact(post.comments_count) }}</h3>
          <button type="button" aria-label="Close" class="h-9 w-9 rounded-full bg-slate-50 grid place-items-center hover:bg-slate-100" @click="close"><X class="w-5 h-5 text-slate-500" /></button>
        </div>
        <CommentThread class="flex-1 min-h-0" kind="vibe" :target-id="post.id" :owner-id="post.created_by" scroll @count="onCount" />
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue';
import { X } from 'lucide-vue-next';
import CommentThread from './CommentThread.vue';
import { compact } from '../format';

const post = ref(null);
let notify = null;

function open(p, onCount) { post.value = p; notify = onCount || null; }
function close() { post.value = null; notify = null; }
function onCount(change) {
  const p = post.value;
  if (!p) return;
  if (notify) notify(change);
  else p.comments_count = typeof change === 'object' ? change.total : Math.max(0, Number(p.comments_count || 0) + change);
}

defineExpose({ open, close });
</script>
