<template>
  <!-- A list of comments with the box to add one: used under a post, in the "all comments" window and in reels. -->
  <div class="flex flex-col min-h-0">
    <div :class="['space-y-4', scroll ? 'flex-1 overflow-y-auto hide-scroll p-4' : '']">
      <p v-if="loading && !items.length" :class="['text-center text-xs font-bold py-6', dark ? 'text-white/60' : 'text-slate-400']">Loading comments…</p>
      <p v-else-if="!items.length" :class="['text-center text-xs font-bold py-6', dark ? 'text-white/60' : 'text-slate-400']">No comments yet — be the first.</p>
      <CommentRow v-for="c in items" :key="c.id" :comment="c" :kind="kind" :owner-id="ownerId" :dark="dark"
                  @reply="(x) => (replyingTo = x)" @deleted="onDeleted" />
      <slot name="after-list" />
    </div>
    <div :class="scroll ? (dark ? 'p-3 border-t border-white/10' : 'p-3 border-t border-slate-100 bg-white') : 'mt-3'">
      <CommentComposer :busy="busy" :replying-to="replyingTo" :dark="dark" @cancel-reply="replyingTo = null" @submit="add" />
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import CommentRow from './CommentRow.vue';
import CommentComposer from './CommentComposer.vue';
import { comments as api } from '../api';
import { toast } from '../useVibeActions';

const props = defineProps({
  kind: { type: String, default: 'vibe' },
  targetId: { type: [Number, String], required: true },
  ownerId: { type: [Number, String], default: null },
  // 'top' = the latest 3 (under a post); 'all' = every comment.
  mode: { type: String, default: 'all' },
  scroll: { type: Boolean, default: false },
  dark: { type: Boolean, default: false },
});
const emit = defineEmits(['count']);

const items = ref([]);
const loading = ref(false);
const busy = ref(false);
const replyingTo = ref(null);

async function load() {
  loading.value = true;
  try {
    const loader = props.mode === 'top' && api[props.kind].top ? api[props.kind].top : api[props.kind].list;
    items.value = await loader(props.targetId);
  } catch {
    items.value = [];
  } finally {
    loading.value = false;
  }
}

async function add(text, clear) {
  busy.value = true;
  try {
    const comment = await api[props.kind].add(props.targetId, text, replyingTo.value?.id);
    if (replyingTo.value) {
      const parent = items.value.find((c) => c.id === replyingTo.value.id);
      if (parent) parent.replies = [...(parent.replies || []), comment];
    } else {
      items.value.unshift(comment);
      if (props.mode === 'top' && items.value.length > 3) items.value.pop();
    }
    replyingTo.value = null;
    clear();
    emit('count', +1);
  } catch (e) {
    toast(e.response?.data?.message || 'Couldn’t post your comment');
  } finally {
    busy.value = false;
  }
}

function onDeleted(id, total) {
  items.value = items.value.filter((c) => c.id !== id);
  emit('count', typeof total === 'number' ? { total } : -1);
}

onMounted(load);
watch(() => props.targetId, () => { replyingTo.value = null; load(); });

defineExpose({ reload: load });
</script>
