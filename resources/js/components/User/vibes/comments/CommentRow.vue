<template>
  <!-- One comment (and its replies) on a post or a reel. The writer, or the post's creator, can delete it. -->
  <div class="flex gap-2 items-start">
    <img :src="avatar" alt="" :class="[reply ? 'h-6 w-6' : 'h-8 w-8', 'rounded-full object-cover shrink-0 mt-1 bg-slate-200']" />
    <div class="flex flex-col min-w-0 max-w-[90%]">
      <div :class="['relative rounded-2xl px-3 py-1.5', dark ? 'bg-white/10' : 'bg-[#F0F2F5]']">
        <p :class="['text-[12px] font-black leading-tight', dark ? 'text-white' : 'text-slate-900']">{{ comment.user?.name || 'Member' }}</p>
        <p :class="['text-[13px] leading-normal mt-0.5 break-words whitespace-pre-line', dark ? 'text-white/90' : 'text-slate-800']">{{ comment.comment }}</p>
        <span v-if="likes > 0" class="absolute -right-2 -bottom-2 bg-white rounded-full shadow-sm border border-slate-100 px-1 py-0.5 flex items-center gap-0.5">
          <span class="w-3.5 h-3.5 bg-rose-500 rounded-full grid place-items-center"><Heart class="w-2 h-2 text-white fill-white" /></span>
          <span class="text-[10px] font-black text-slate-500">{{ likes }}</span>
        </span>
      </div>

      <div :class="['flex items-center gap-3 px-2 mt-1 text-[11px] font-black', dark ? 'text-white/60' : 'text-slate-500']">
        <button type="button" :class="liked ? 'text-rose-500' : 'hover:text-lkblue'" @click="toggleLike">Like</button>
        <button v-if="!reply" type="button" class="hover:text-lkblue" @click="$emit('reply', comment)">Reply</button>
        <button v-if="canDelete" type="button" class="hover:text-rose-500" :disabled="busy" @click="remove">Delete</button>
        <span class="font-bold uppercase opacity-80">{{ timeAgo(comment.created_at) }}</span>
      </div>

      <div v-if="comment.replies?.length" :class="['mt-2 space-y-3 pl-2 border-l-2', dark ? 'border-white/10' : 'border-slate-100']">
        <CommentRow v-for="r in comment.replies" :key="r.id" :comment="r" :kind="kind" :owner-id="ownerId" :dark="dark" reply
                    @deleted="(id) => removeReply(id)" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Heart } from 'lucide-vue-next';
import { comments as api } from '../api';
import { timeAgo } from '../format';
import { toast } from '../useVibeActions';

defineOptions({ name: 'CommentRow' });

const props = defineProps({
  comment: { type: Object, required: true },
  kind: { type: String, default: 'vibe' }, // 'vibe' | 'reel'
  ownerId: { type: [Number, String], default: null }, // the post / reel creator
  reply: { type: Boolean, default: false },
  dark: { type: Boolean, default: false },
});
const emit = defineEmits(['reply', 'deleted']);

const page = usePage();
const me = computed(() => page.props.auth?.user?.id);
const avatar = computed(() => props.comment.user?.avatar || `https://i.pravatar.cc/80?u=${props.comment.user_id}`);
const canDelete = computed(() => me.value && (String(props.comment.user_id) === String(me.value) || String(props.ownerId) === String(me.value)));

const liked = ref(!!props.comment.is_liked);
const likes = ref(Number(props.comment.likes_count || 0));
const busy = ref(false);

async function toggleLike() {
  liked.value = !liked.value;
  likes.value += liked.value ? 1 : -1;
  try {
    const data = await api[props.kind].like(props.comment.id);
    liked.value = !!data.is_liked;
    likes.value = Number(data.likes_count || 0);
  } catch {
    liked.value = !liked.value;
    likes.value += liked.value ? 1 : -1;
  }
}

async function remove() {
  busy.value = true;
  try {
    const data = await api[props.kind].remove(props.comment.id);
    emit('deleted', props.comment.id, data.comments_count);
    toast('Comment deleted');
  } catch (e) {
    toast(e.response?.data?.message || 'Couldn’t delete that comment');
  } finally {
    busy.value = false;
  }
}

function removeReply(id) {
  props.comment.replies = (props.comment.replies || []).filter((r) => r.id !== id);
}
</script>
