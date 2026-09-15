<template>
  <div v-if="isOpen" class="fixed inset-0 z-[1000] bg-black/60 backdrop-blur-sm flex items-end justify-center p-0 md:p-5 animate-in slide-in-from-bottom-full duration-300" @click.self="close">
    <div class="bg-white rounded-t-[56px] md:rounded-[56px] max-w-md w-full shadow-2xl overflow-hidden flex flex-col border border-white/20" style="max-height:85vh">
      <div class="p-8 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 shrink-0">
        <h3 class="text-xl font-black tracking-tight text-slate-950 uppercase text-xs tracking-[0.2em]">Comments</h3>
        <button @click="close" class="h-10 w-10 rounded-full bg-white shadow-sm grid place-items-center hover:bg-slate-50 transition">
          <i data-lucide="x" class="w-6 h-6 text-slate-400"></i>
        </button>
      </div>

      <div class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4 hide-scroll bg-slate-50/30" ref="scrollContainer">
        <div v-if="loading && !comments.length" class="flex justify-center py-10">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-lkblue"></div>
        </div>

        <template v-else-if="comments.length">
          <VibeCommentItem
            v-for="c in comments"
            :key="c.id"
            :c="c"
            @reply="handleReply"
          />
        </template>

        <div v-else-if="!loading" class="flex flex-col items-center justify-center py-10 text-slate-400">
          <i data-lucide="message-circle" class="w-12 h-12 mb-3 opacity-20"></i>
          <p class="font-bold">No comments yet</p>
          <p class="text-xs">Be the first to share a vibe!</p>
        </div>

        <div v-if="hasMore" class="flex justify-center pt-4">
          <button @click="loadComments(true)" class="text-xs font-black text-lkblue hover:underline">Load more</button>
        </div>
      </div>

      <div class="p-4 border-t border-slate-100 bg-white shrink-0">
        <!-- Reply indicator -->
        <div v-if="replyingTo" class="flex items-center justify-between bg-slate-50 px-3 py-1.5 rounded-lg mb-2 text-[11px] font-bold text-slate-500 animate-in slide-in-from-bottom-1 transition">
          <span>Replying to <span class="text-lkblue">{{ replyingTo.user.name }}</span></span>
          <button @click="replyingTo = null" class="text-slate-400 hover:text-rose-500"><i data-lucide="x" class="w-3 h-3"></i></button>
        </div>

        <div class="flex items-center gap-3">
          <img :src="currentUserAvatar" class="h-9 w-9 rounded-full object-cover flex-shrink-0" />
          <div class="flex-1 relative flex items-center">
            <input
              ref="inputRef"
              v-model="newComment"
              @keydown.enter="postComment"
              class="w-full rounded-[20px] bg-[#F0F2F5] border-transparent px-4 py-2.5 pr-12 text-[14px] font-medium focus:ring-1 focus:ring-lkblue focus:bg-white transition outline-none"
              placeholder="Write a comment..."
              :disabled="submitting"
            >
            <button
              @click="postComment"
              class="absolute right-2 p-1.5 text-lkblue hover:bg-slate-200 rounded-full transition disabled:opacity-30"
              :disabled="!newComment.trim() || submitting"
            >
              <i data-lucide="send-horizontal" class="w-5 h-5"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import VibeCommentItem from '../cards/VibeCommentItem.vue';

const isOpen = ref(false);
const loading = ref(false);
const submitting = ref(false);
const comments = ref([]);
const newComment = ref('');
const vibeId = ref(null);
const page = ref(1);
const hasMore = ref(false);
const scrollContainer = ref(null);
const inputRef = ref(null);
const replyingTo = ref(null);

const emit = defineEmits(['commentAdded']);

const pageData = usePage();
const currentUserAvatar = computed(() => pageData.props.auth?.user?.avatar || '/images/default-avatar.png');

const handleReply = (comment) => {
  replyingTo.value = comment;
  nextTick(() => {
    if (inputRef.value) inputRef.value.focus();
  });
};

const open = (id) => {
  vibeId.value = id;
  isOpen.value = true;
  comments.value = [];
  page.value = 1;
  replyingTo.value = null;
  loadComments();
  nextTick(() => window.lucide?.createIcons());
};

const close = () => {
  isOpen.value = false;
};

const loadComments = async (more = false) => {
  if (loading.value) return;
  if (more) page.value++;

  loading.value = true;
  try {
    const response = await axios.get(route('new_frontend.vibes.comments.index', { vibe: vibeId.value }), {
      params: { page: page.value }
    });

    if (more) {
      comments.value.push(...response.data.data);
    } else {
      comments.value = response.data.data;
    }

    hasMore.value = !!response.data.next_page_url;
  } catch (error) {
    console.error('Failed to load comments', error);
  } finally {
    loading.value = false;
    nextTick(() => window.lucide?.createIcons());
  }
};

const postComment = async () => {
  if (!newComment.value.trim() || submitting.value) return;

  submitting.value = true;
  try {
    const response = await axios.post(route('new_frontend.vibes.comments.store', { vibe: vibeId.value }), {
      comment: newComment.value,
      parent_id: replyingTo.value ? replyingTo.value.id : null
    });

    if (replyingTo.value) {
      const parent = comments.value.find(c => c.id === replyingTo.value.id);
      if (parent) {
        if (!parent.replies) parent.replies = [];
        parent.replies.push(response.data);
      }
    } else {
      comments.value.unshift(response.data);
    }

    newComment.value = '';
    replyingTo.value = null;
    emit('commentAdded');

    nextTick(() => {
      window.lucide?.createIcons();
      if (!replyingTo.value && scrollContainer.value) {
        scrollContainer.value.scrollTop = 0;
      }
    });
  } catch (error) {
    console.error('Failed to post comment', error);
  } finally {
    submitting.value = false;
  }
};

const timeAgo = (date) => {
  const seconds = Math.floor((new Date() - new Date(date)) / 1000);
  let interval = seconds / 31536000;
  if (interval > 1) return Math.floor(interval) + "y ago";
  interval = seconds / 2592000;
  if (interval > 1) return Math.floor(interval) + "mo ago";
  interval = seconds / 86400;
  if (interval > 1) return Math.floor(interval) + "d ago";
  interval = seconds / 3600;
  if (interval > 1) return Math.floor(interval) + "h ago";
  interval = seconds / 60;
  if (interval > 1) return Math.floor(interval) + "m ago";
  return "just now";
};

defineExpose({ open, close });
</script>


<style scoped>
.hide-scroll::-webkit-scrollbar { display: none; }
.hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
</style>
