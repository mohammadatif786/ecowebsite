<template>
  <!-- Repost: puts a copy of the post on your own campus, under your name. -->
  <button type="button" @click="repost" :disabled="busy" title="Repost" aria-label="Repost"
          :class="['flex items-center gap-1.5 text-sm font-bold disabled:opacity-60', post.reposted ? 'text-emerald-600' : 'text-slate-500']">
    <Repeat2 class="w-4 h-4" />{{ num(post.reposts || 0) }}
  </button>
</template>

<script setup>
import { ref } from 'vue';
import { Repeat2 } from 'lucide-vue-next';
import { num } from '../../../../services/uvibeFormat';
import { firstError, notify } from '../../../../services/uvibe';
import { repostPost } from '../../../../services/uvibePosts';

const props = defineProps({ post: { type: Object, required: true } });
// reposted: {post, original} — the new post on your campus, and the original with its new count.
const emit = defineEmits(['reposted', 'needUniversity']);

const busy = ref(false);

const repost = async () => {
  if (busy.value) return;
  if (props.post.mine) { notify('That’s already your own post'); return; }
  if (props.post.reposted) { notify('You already reposted this'); return; }

  busy.value = true;
  try {
    const res = await repostPost(props.post.id);
    notify(res.message);
    emit('reposted', res);
  } catch (e) {
    const noUniversity = !!e?.response?.data?.errors?.university_id;
    notify(firstError(e, 'Could not repost'));
    if (noUniversity) emit('needUniversity');
  } finally {
    busy.value = false;
  }
};
</script>
