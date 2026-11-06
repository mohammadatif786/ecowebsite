<template>
  <!-- Like · comments · share · repost · Big Up · save. Counts update straight away and are corrected by the server. -->
  <div class="flex flex-wrap items-center gap-x-4 sm:gap-x-5 gap-y-2 mb-2">
    <button type="button" :aria-pressed="post.is_liked" :class="['flex items-center gap-1.5 font-black transition-colors', post.is_liked ? 'text-rose-500' : 'text-slate-700']" @click="like">
      <Heart class="w-5 h-5" :class="post.is_liked ? 'fill-current' : ''" /><span>{{ compact(post.likes_count) }}</span>
    </button>
    <button type="button" aria-label="Comments" class="flex items-center gap-1.5 font-black text-slate-700" @click="$emit('comments')">
      <MessageCircle class="w-5 h-5" /><span>{{ compact(post.comments_count) }}</span>
    </button>
    <button type="button" aria-label="Share" class="flex items-center gap-1.5 font-black text-slate-700" @click="share">
      <Send class="w-5 h-5" />
    </button>
    <button type="button" aria-label="Repost" :disabled="mine || post.is_reposed"
            :class="['flex items-center gap-1.5 font-black transition-colors disabled:cursor-default', post.is_reposed ? 'text-emerald-600' : 'text-slate-700']" @click="repost">
      <Repeat2 class="w-5 h-5" /><span>{{ compact(post.reposts_count) }}</span>
    </button>
    <button v-if="post.allow_coin_gifts && !mine" type="button"
            class="ml-auto flex items-center gap-1.5 text-white bg-gradient-to-r from-orange-500 to-amber-500 px-3 py-1.5 rounded-full text-sm font-black active:scale-95 transition shadow-lg shadow-orange-500/20"
            @click="actions.openBigUp(post, (count) => (post.bigups_count = count))">
      <Zap class="w-4 h-4 fill-white" />Big Up {{ compact(post.bigups_count) }}
    </button>
    <span v-else-if="post.allow_coin_gifts" class="ml-auto flex items-center gap-1.5 text-orange-600 text-sm font-black"><Zap class="w-4 h-4" />{{ compact(post.bigups_count) }}</span>
    <button type="button" aria-label="Copy link" :class="['text-slate-500', post.allow_coin_gifts ? '' : 'ml-auto']" @click="copyLink">
      <Link2 class="w-5 h-5" />
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Heart, Link2, MessageCircle, Repeat2, Send, Zap } from 'lucide-vue-next';
import { likePost, repostPost, sharePost } from '../api';
import { compact } from '../format';
import { toast, useVibeActions } from '../useVibeActions';

const props = defineProps({ post: { type: Object, required: true } });
const emit = defineEmits(['comments', 'reposted']);
const actions = useVibeActions();
const page = usePage();
const mine = computed(() => String(props.post.created_by) === String(page.props.auth?.user?.id));
const link = () => `${window.location.origin}/user/vibes?id=${props.post.id}`;

async function like() {
  const p = props.post;
  p.is_liked = !p.is_liked;
  p.likes_count = Number(p.likes_count || 0) + (p.is_liked ? 1 : -1);
  try {
    const data = await likePost(p.id);
    p.is_liked = !!data.is_liked;
    p.likes_count = data.likes_count;
  } catch {
    p.is_liked = !p.is_liked;
    p.likes_count += p.is_liked ? 1 : -1;
  }
}

async function share() {
  sharePost(props.post.id).then((d) => (props.post.shares_count = d.shares_count)).catch(() => {});
  try {
    if (navigator.share) await navigator.share({ title: 'LinkUp Vibe', text: props.post.caption || '', url: link() });
    else { await navigator.clipboard.writeText(link()); toast('Link copied'); }
  } catch { /* the member closed the share sheet */ }
}

async function repost() {
  const p = props.post;
  if (mine.value || p.is_reposed) return;
  try {
    const data = await repostPost(p.id);
    p.is_reposed = true;
    p.reposts_count = data.reposts_count;
    toast('🔁 Reposted to your feed');
    if (data.new_vibe) emit('reposted', data.new_vibe);
  } catch (e) {
    toast(e.response?.data?.message || 'Couldn’t repost this');
  }
}

async function copyLink() {
  try { await navigator.clipboard.writeText(link()); toast('🔖 Link copied'); } catch { toast(link()); }
}
</script>
