<template>
  <article class="card overflow-hidden">
    <div class="flex items-center gap-3 p-4">
      <img :src="p.avatar" class="w-11 h-11 rounded-full object-cover" />
      <div class="flex-1 min-w-0">
        <p class="font-black text-sm flex items-center gap-1.5">
          {{ p.handle }}
          <span v-if="p.org" class="text-[9px] font-black text-white px-1.5 py-0.5 rounded-full" style="background:var(--lk-blue)">
            {{ p.org.toUpperCase() }}
          </span>
        </p>
        <p class="text-xs text-slate-500 flex items-center gap-1">
          <i data-lucide="map-pin" class="w-3 h-3"></i>{{ p.location }}
          <template v-if="p.kind === 'reel'">
            · <span class="text-lkblue2 font-bold">Reel</span>
          </template>
        </p>
      </div>
      <span v-if="p.shoppable" class="text-xs font-black text-purple-600 bg-purple-50 px-2.5 py-1 rounded-full flex items-center gap-1">
        <i data-lucide="shopping-bag" class="w-3 h-3"></i>Shop
      </span>
      <button @click="showToast('More')" class="text-slate-400"><i data-lucide="more-horizontal" class="w-5 h-5"></i></button>
    </div>

    <div class="relative bg-black group">
      <!-- Media Carousel -->
      <div class="relative overflow-hidden w-full">
        <div class="flex transition-transform duration-300 ease-in-out" :style="{ transform: `translateX(-${currentIndex * 100}%)` }">
          <div v-for="(item, index) in p.media" :key="item.id || index" class="w-full shrink-0 flex items-center justify-center bg-black">
            <video v-if="item.type === 'video'" :src="item.url" controls class="w-full object-contain"></video>
            <img v-else :src="item.url" class="w-full object-contain" />
          </div>
        </div>
      </div>

      <!-- Navigation Arrows (only show if more than 1 media) -->
      <template v-if="p.media && p.media.length > 1">
        <button
          @click="prev"
          class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white p-1.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
        >
          <i data-lucide="chevron-left" class="w-5 h-5"></i>
        </button>
        <button
          @click="next"
          class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white p-1.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
        >
          <i data-lucide="chevron-right" class="w-5 h-5"></i>
        </button>

        <!-- Pagination dots -->
        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
          <span
            v-for="(_, idx) in p.media"
            :key="idx"
            class="w-1.5 h-1.5 rounded-full transition-all"
            :class="currentIndex === idx ? 'bg-white scale-125' : 'bg-white/40'"
          ></span>
        </div>
      </template>

      <!-- Reel Indicator -->
      <span v-if="p.media && p.media[currentIndex]?.type === 'video'" class="absolute top-3 right-3 bg-black/50 text-white text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1">
        <i data-lucide="play" class="w-3 h-3"></i>Reel
      </span>
    </div>

    <div class="p-4">
      <div class="flex items-center gap-5 mb-2">
        <button
          @click="toggleLike"
          class="flex items-center gap-1.5 font-black transition-colors"
          :class="isLiked ? 'text-rose-500' : 'text-slate-700'"
        >
          <i data-lucide="heart" class="w-5 h-5" :fill="isLiked ? 'currentColor' : 'none'"></i>
          <span>{{ num(likesCount) }}</span>
        </button>
        <button @click="toggleComments" class="flex items-center gap-1.5 font-black text-slate-700">
          <i data-lucide="message-circle" class="w-5 h-5"></i><span>{{ num(commentsCount) }}</span>
        </button>
        <button @click="shareVibe" class="flex items-center gap-1.5 font-black text-slate-700">
          <i data-lucide="send" class="w-5 h-5"></i>
        </button>
        <button class="ml-auto flex items-center gap-1.5 text-white bg-gradient-to-r from-orange-500 to-amber-500 px-3 py-1.5 rounded-full text-sm font-black">
          <i data-lucide="zap" class="w-4 h-4"></i>Big Up {{ num(p.bigup || 0) }}
        </button>
        <button @click="showToast('🔖 Saved')" class="text-slate-500"><i data-lucide="bookmark" class="w-5 h-5"></i></button>
      </div>
      <p class="text-sm"><span class="font-black">{{ p.handle }}</span> {{ p.caption }}</p>
      <p v-if="p.sound" class="text-xs text-slate-500 mt-1 flex items-center gap-1">
        <i data-lucide="music" class="w-3 h-3"></i>{{ p.sound }}
      </p>

      <!-- Inline Comments Section -->
      <div v-if="showInlineComments" class="mt-4 border-t border-slate-100 pt-3 space-y-4">
        <!-- Top Comments -->
        <div v-if="topComments.length" class="space-y-3">
          <VibeCommentItem
            v-for="c in topComments"
            :key="c.id"
            :c="c"
            @reply="handleInlineReply"
          />
        </div>

        <button
          v-if="commentsCount > 3"
          @click="openFullComments"
          class="text-xs font-black text-slate-500 hover:text-lkblue transition px-2"
        >
          View all {{ num(commentsCount) }} comments
        </button>

        <!-- Inline Input -->
        <div class="mt-2">
          <!-- Reply indicator -->
          <div v-if="inlineReplyingTo" class="flex items-center justify-between bg-slate-50 px-3 py-1 rounded-lg mb-2 text-[10px] font-bold text-slate-500 animate-in slide-in-from-bottom-1 transition mx-1">
            <span>Replying to <span class="text-lkblue">{{ inlineReplyingTo.user.name }}</span></span>
            <button @click="inlineReplyingTo = null" class="text-slate-400 hover:text-rose-500"><i data-lucide="x" class="w-3 h-3"></i></button>
          </div>

          <div class="flex items-center gap-2 px-1">
            <img :src="user.avatar" class="h-8 w-8 rounded-full object-cover" />
            <div class="flex-1 relative flex items-center">
              <input
                ref="inlineInputRef"
                v-model="inlineCommentText"
                @keydown.enter="postInlineComment"
                class="w-full rounded-2xl bg-[#F0F2F5] border-transparent px-4 py-1.5 pr-10 text-[13px] font-medium outline-none focus:ring-1 focus:ring-lkblue focus:bg-white transition"
                placeholder="Write a comment..."
              />
              <button
                @click="postInlineComment"
                class="absolute right-2 p-1 text-lkblue hover:bg-slate-200 rounded-full transition disabled:opacity-30"
                :disabled="!inlineCommentText.trim() || submittingComment"
              >
                <i data-lucide="send-horizontal" class="w-4 h-4"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- VibeTagCard -->
      <button v-if="p.tag" @click="openVibeTag" class="w-full flex items-center gap-3 rounded-2xl border border-slate-200 p-2 mt-3 text-left hover:border-lkblue transition">
        <div class="relative shrink-0">
          <img :src="p.tag.image" class="h-12 w-12 rounded-xl object-cover" />
          <span class="absolute -top-1 -left-1 text-white text-[9px] font-black px-1 py-0.5 rounded-full" :style="{ background: p.tag.kind === 'event' ? '#2563eb' : '#8b5cf6' }">
            {{ p.tag.kind === 'event' ? '🎟️' : '🛍️' }}
          </span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-black text-sm truncate">{{ p.tag.title }}</p>
          <p class="text-[11px] text-slate-500 truncate">
            {{ p.tag.kind === 'event' ? `${p.tag.date} · ${p.tag.location || 'Location TBA'}` : (p.tag.seller || 'Marketplace') }}
          </p>
        </div>
        <span class="btn btn-primary px-3 py-2 text-xs shrink-0">
          {{ p.tag.kind === 'event' ? 'Get Tickets' : 'Shop' }} · {{ p.tag.price ? money(p.tag.price) : 'Free' }}
        </span>
      </button>
    </div>

    <VibeTagModal ref="vibeTagModalRef" />
    <VibeCommentsModal ref="vibeCommentsModalRef" @commentAdded="onCommentAdded" />
  </article>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import VibeTagModal from '../modals/VibeTagModal.vue';
import VibeCommentsModal from '../modals/VibeCommentsModal.vue';
import VibeCommentItem from './VibeCommentItem.vue';

const props = defineProps({
  p: Object
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});

const vibeTagModalRef = ref(null);
const vibeCommentsModalRef = ref(null);
const currentIndex = ref(0);

const isLiked = ref(props.p.is_liked || false);
const likesCount = ref(props.p.likes_count || props.p.likes || 0);
const commentsCount = ref(props.p.comments_count || props.p.comments || 0);

const next = () => {
  if (props.p.media && props.p.media.length) {
    currentIndex.value = (currentIndex.value + 1) % props.p.media.length;
  }
};

const prev = () => {
  if (props.p.media && props.p.media.length) {
    currentIndex.value = (currentIndex.value - 1 + props.p.media.length) % props.p.media.length;
  }
};

const showInlineComments = ref(false);
const topComments = ref([]);
const inlineCommentText = ref('');
const submittingComment = ref(false);
const inlineReplyingTo = ref(null);
const inlineInputRef = ref(null);

const handleInlineReply = (comment) => {
  inlineReplyingTo.value = comment;
  nextTick(() => {
    if (inlineInputRef.value) inlineInputRef.value.focus();
  });
};

const toggleComments = async () => {
  showInlineComments.value = !showInlineComments.value;
  if (showInlineComments.value && !topComments.value.length) {
    fetchTopComments();
  }
};

const fetchTopComments = async () => {
  try {
    const response = await axios.get(route('new_frontend.vibes.top-comments', { vibe: props.p.id }));
    topComments.value = response.data;
  } catch (error) {
    console.error('Failed to fetch top comments', error);
  }
};

const postInlineComment = async () => {
  if (!inlineCommentText.value.trim() || submittingComment.value) return;

  submittingComment.value = true;
  try {
    const response = await axios.post(route('new_frontend.vibes.comments.store', { vibe: props.p.id }), {
      comment: inlineCommentText.value,
      parent_id: inlineReplyingTo.value ? inlineReplyingTo.value.id : null
    });

    if (inlineReplyingTo.value) {
      const parent = topComments.value.find(c => c.id === inlineReplyingTo.value.id);
      if (parent) {
        if (!parent.replies) parent.replies = [];
        parent.replies.push(response.data);
      }
    } else {
      topComments.value.unshift(response.data);
      if (topComments.value.length > 3) topComments.value.pop();
    }

    commentsCount.value++;
    inlineCommentText.value = '';
    inlineReplyingTo.value = null;
    showToast('Comment posted!');
  } catch (error) {
    console.error('Failed to post comment', error);
  } finally {
    submittingComment.value = false;
  }
};

const openFullComments = () => {
  if (vibeCommentsModalRef.value) {
    vibeCommentsModalRef.value.open(props.p.id);
  }
};

const onCommentAdded = () => {
  commentsCount.value++;
  fetchTopComments();
};

const toggleLike = async () => {
  isLiked.value = !isLiked.value;
  likesCount.value += isLiked.value ? 1 : -1;

  try {
    const response = await axios.post(route('new_frontend.vibes.like', { vibe: props.p.id }));
    isLiked.value = response.data.is_liked;
    likesCount.value = response.data.likes_count;
  } catch (error) {
    console.error('Failed to toggle like', error);
    isLiked.value = !isLiked.value;
    likesCount.value += isLiked.value ? 1 : -1;
  }
};

const shareVibe = async () => {
  const shareData = {
    title: 'LinkUp Vibe',
    text: props.p.caption,
    url: window.location.origin + '/vibes?id=' + props.p.id,
  };

  try {
    // Record share on server
    axios.post(route('new_frontend.vibes.share', { vibe: props.p.id }));

    if (navigator.share) {
      await navigator.share(shareData);
    } else {
      await navigator.clipboard.writeText(shareData.url);
      showToast('Link copied to clipboard!');
    }
  } catch (err) {
    console.error('Error sharing:', err);
  }
};

const num = (n) => Number(n || 0).toLocaleString();
const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const openVibeTag = () => {
  if (vibeTagModalRef.value && props.p.tag) {
    vibeTagModalRef.value.open(props.p.tag);
  }
};

const showToast = (msg) => {
  if (window.toast) window.toast(msg);
};
</script>
