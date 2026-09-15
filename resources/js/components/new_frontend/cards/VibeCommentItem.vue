<template>
  <div class="flex gap-2 items-start animate-in fade-in duration-300">
    <img :src="c.user.avatar" :class="isReply ? 'h-6 w-6' : 'h-8 w-8'" class="rounded-full object-cover shadow-sm flex-shrink-0 mt-1" />
    <div class="flex flex-col max-w-[90%]">
      <div class="rounded-2xl bg-[#F0F2F5] px-3 py-1.5 transition hover:bg-[#E4E6E9] relative group">
        <p class="text-[12px] font-black text-slate-900 leading-tight">{{ c.user.name }}</p>
        <p class="text-[13px] font-medium leading-normal text-slate-800 mt-0.5">{{ c.comment }}</p>

        <!-- Like count badge -->
        <div v-if="localLikesCount > 0" class="absolute -right-2 -bottom-2 bg-white rounded-full shadow-sm border border-slate-100 px-1 py-0.5 flex items-center gap-0.5 z-10">
          <div class="w-3.5 h-3.5 bg-rose-500 rounded-full grid place-items-center">
            <i data-lucide="heart" class="w-2 h-2 text-white" fill="white"></i>
          </div>
          <span class="text-[10px] font-black text-slate-500">{{ localLikesCount }}</span>
        </div>
      </div>

      <div class="flex items-center gap-3 px-2 mt-1">
        <button
          @click="toggleLike"
          class="text-[11px] font-black transition"
          :class="localIsLiked ? 'text-rose-500' : 'text-slate-500 hover:text-lkblue'"
        >
          Like
        </button>
        <button v-if="!isReply" @click="$emit('reply', c)" class="text-[11px] font-black text-slate-500 hover:text-lkblue transition">Reply</button>
        <span class="text-[11px] text-slate-400 font-bold uppercase">{{ timeAgo(c.created_at) }}</span>
      </div>

      <!-- Nested Replies -->
      <div v-if="c.replies && c.replies.length" class="mt-2 space-y-3 pl-2 border-l-2 border-slate-100">
        <VibeCommentItem
          v-for="reply in c.replies"
          :key="reply.id"
          :c="reply"
          is-reply
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
  c: Object,
  isReply: { type: Boolean, default: false }
});

const emit = defineEmits(['reply']);

const localIsLiked = ref(props.c.is_liked || false);
const localLikesCount = ref(props.c.likes_count || 0);

const toggleLike = async () => {
  localIsLiked.value = !localIsLiked.value;
  localLikesCount.value += localIsLiked.value ? 1 : -1;

  try {
    const response = await axios.post(route('new_frontend.vibes.comments.like', { comment: props.c.id }));
    localIsLiked.value = response.data.is_liked;
    localLikesCount.value = response.data.likes_count;
  } catch (error) {
    console.error('Failed to like comment', error);
    localIsLiked.value = !localIsLiked.value;
    localLikesCount.value += localIsLiked.value ? 1 : -1;
  }
};

const timeAgo = (date) => {
  const seconds = Math.floor((new Date() - new Date(date)) / 1000);
  let interval = seconds / 31536000;
  if (interval > 1) return Math.floor(interval) + "y";
  interval = seconds / 2592000;
  if (interval > 1) return Math.floor(interval) + "mo";
  interval = seconds / 86400;
  if (interval > 1) return Math.floor(interval) + "d";
  interval = seconds / 3600;
  if (interval > 1) return Math.floor(interval) + "h";
  interval = seconds / 60;
  if (interval > 1) return Math.floor(interval) + "m";
  return "now";
};

onMounted(() => {
  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
  });
});
</script>
