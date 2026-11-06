<template>
  <!--
    One post in the Vibes feed. It only lays the card out: each part is its own component in this folder,
    and the windows they open (comments, Big Up, options, tagged item, Send a Vibe) live once on the page
    (see useVibeActions).
  -->
  <article :id="'vibe-' + post.id" class="card overflow-hidden max-w-[720px] mx-auto w-full border border-slate-200 shadow-sm">
    <VibePostHeader :post="post" />
    <p v-if="post.repost_of" class="px-4 pb-2 -mt-1 text-xs font-bold text-slate-500 flex items-center gap-1.5">
      <Repeat2 class="w-3.5 h-3.5 text-emerald-600" />{{ post.handle }} reposted from {{ post.repost_of.name }}
    </p>
    <VibePostMedia :post="post" />
    <div class="p-4">
      <VibePostActions :post="post" @comments="showComments = !showComments" @reposted="(v) => $emit('reposted', v)" />
      <p v-if="isText" class="text-[11px] text-slate-400 font-bold">{{ post.handle }}</p>
      <VibeCaption v-else-if="post.caption" :post="post" />
      <VibeTagCard :tag="post.tag" @open="actions.openTag(post)" />

      <div v-if="showComments" class="mt-4 border-t border-slate-100 pt-3">
        <CommentThread kind="vibe" mode="top" :target-id="post.id" :owner-id="post.created_by" @count="onCount">
          <template #after-list>
            <button v-if="post.comments_count > 3" type="button" class="text-xs font-black text-slate-500 hover:text-lkblue px-2"
                    @click="actions.openComments(post, onCount)">View all {{ compact(post.comments_count) }} comments</button>
          </template>
        </CommentThread>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Repeat2 } from 'lucide-vue-next';
import VibePostHeader from './VibePostHeader.vue';
import VibePostMedia from './VibePostMedia.vue';
import VibePostActions from './VibePostActions.vue';
import VibeCaption from './VibeCaption.vue';
import VibeTagCard from './VibeTagCard.vue';
import CommentThread from '../comments/CommentThread.vue';
import { compact } from '../format';
import { useVibeActions } from '../useVibeActions';

const props = defineProps({ post: { type: Object, required: true } });
defineEmits(['reposted']);
const actions = useVibeActions();
const showComments = ref(false);
const isText = computed(() => props.post.kind === 'text' || (!props.post.media?.length && !!props.post.text_bg));

function onCount(change) {
  const p = props.post;
  p.comments_count = typeof change === 'object' ? change.total : Math.max(0, Number(p.comments_count || 0) + change);
}
</script>
