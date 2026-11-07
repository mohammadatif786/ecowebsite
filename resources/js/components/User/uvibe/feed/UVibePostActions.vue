<template>
  <!--
    The row under a U Vibe post, as in the design: ❤ likes · 💬 comments · share · 🔁 repost to your campus,
    then "Send a Boost" and "On Vibes". Likes and comments are the same as on Vibes (a U Vibe post is a Vibe),
    repost and Boost are the U Vibe ones.
  -->
  <div>
    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
      <button type="button" :aria-pressed="!!post.is_liked" aria-label="Like"
              :class="['flex items-center gap-1.5 text-sm font-bold transition-colors', post.is_liked ? 'text-rose-500' : 'text-slate-500 hover:text-rose-500']" @click="like">
        <Heart class="w-4 h-4" :class="post.is_liked ? 'fill-current' : ''" />{{ num(post.likes) }}
      </button>
      <button type="button" aria-label="Comments" :aria-expanded="showComments"
              :class="['flex items-center gap-1.5 text-sm font-bold transition-colors', showComments ? 'text-violet-600' : 'text-slate-500 hover:text-violet-600']" @click="showComments = !showComments">
        <MessageCircle class="w-4 h-4" />{{ num(post.comments) }}
      </button>
      <PostShareButton :post="post" />
      <PostRepostButton :post="post" @reposted="$emit('reposted', $event)" @need-university="$emit('needUniversity')" />
      <div class="ml-auto flex items-center gap-2">
        <PostBoostButton v-if="post.can_boost" :post="post" @open="$emit('boost', $event)" />
        <span v-if="post.onVibes" class="chip flex items-center gap-1 shrink-0" style="background:#05966914;color:#059669;border-color:#05966922">
          <CheckCircle class="w-3.5 h-3.5" />On Vibes
        </span>
      </div>
    </div>

    <div v-if="showComments" class="mt-4 border-t border-slate-100 pt-3">
      <CommentThread kind="vibe" mode="top" :target-id="post.id" :owner-id="post.poster_id" @count="onCount">
        <template #after-list>
          <button v-if="post.comments > 3" type="button" class="text-xs font-black text-slate-500 hover:text-violet-600 px-2"
                  @click="actions.openComments(commentsTarget, onCount)">View all {{ num(post.comments) }} comments</button>
        </template>
      </CommentThread>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { CheckCircle, Heart, MessageCircle } from 'lucide-vue-next';
import { num } from '../../../../services/uvibeFormat';
import PostShareButton from './PostShareButton.vue';
import PostRepostButton from './PostRepostButton.vue';
import PostBoostButton from './PostBoostButton.vue';
import CommentThread from '../../vibes/comments/CommentThread.vue';
import { likePost } from '../../vibes/api';
import { toast, useVibeActions } from '../../vibes/useVibeActions';

const props = defineProps({ post: { type: Object, required: true } });
defineEmits(['reposted', 'boost', 'needUniversity']);

const actions = useVibeActions();
const showComments = ref(false);

// The "all comments" window reads the Vibes field names.
const commentsTarget = computed(() => ({ id: props.post.id, created_by: props.post.poster_id, comments_count: props.post.comments }));

async function like() {
  const p = props.post;
  p.is_liked = !p.is_liked;
  p.likes = Math.max(0, Number(p.likes || 0) + (p.is_liked ? 1 : -1));
  try {
    const data = await likePost(p.id);
    p.is_liked = !!data.is_liked;
    p.likes = data.likes_count;
  } catch (e) {
    p.is_liked = !p.is_liked;
    p.likes = Math.max(0, p.likes + (p.is_liked ? 1 : -1));
    toast(e.response?.data?.message || 'Couldn’t like this post');
  }
}

function onCount(change) {
  const p = props.post;
  p.comments = typeof change === 'object' ? change.total : Math.max(0, Number(p.comments || 0) + change);
  commentsTarget.value.comments_count = p.comments;
}
</script>
