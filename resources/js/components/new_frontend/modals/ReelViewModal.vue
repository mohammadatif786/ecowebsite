<template>
  <Modal ref="modalRef" class="reel-modal">
    <div class="relative h-full bg-black">
      <!-- Top Left - Close Button -->
      <button
        @click="close"
        class="absolute top-4 left-4 z-20 p-2 text-white hover:text-slate-300 transition"
      >
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>

      <!-- Top Right - Reels Icon -->
      <div class="absolute top-4 right-4 z-20 flex items-center gap-2 text-white">
        <i data-lucide="play" class="w-5 h-5"></i>
        <span class="font-black text-sm">Reels</span>
      </div>

      <!-- Media Display -->
      <div class="h-full flex items-center justify-center">
        <video
          v-if="reel?.type === 'video'"
          ref="videoRef"
          :src="reel?.file_path"
          autoplay
          loop
          playsinline
          class="h-full w-full object-contain"
          @click="togglePlayPause"
        />
        <img
          v-else
          :src="reel?.file_path"
          class="h-full w-full object-contain"
        />
      </div>

      <!-- Right Side Overlay - Interactions -->
      <div class="absolute right-4 top-1/2 -translate-y-1/2 flex flex-col gap-4 z-10">
        <!-- Like Button -->
        <button
          @click="toggleLike"
          class="flex items-center gap-1.5 font-black transition-colors text-white hover:text-rose-500"
          :class="isLiked ? 'text-rose-500' : ''"
        >
          <i data-lucide="heart" class="w-7 h-7" :fill="isLiked ? 'currentColor' : 'none'"></i>
          <span class="text-xs">{{ reel?.likes_count || 0 }}</span>
        </button>

        <!-- Comment Button -->
        <button
          @click="openComments"
          class="flex items-center gap-1.5 font-black text-white hover:text-lkblue"
        >
          <i data-lucide="message-circle" class="w-7 h-7"></i>
          <span class="text-xs">{{ reel?.comments_count || 0 }}</span>
        </button>

        <!-- Gift Button -->
        <button
          @click="sendGift"
          class="flex items-center gap-1.5 text-white bg-gradient-to-r from-orange-500 to-amber-500 px-3 py-1.5 rounded-full text-sm font-black transition-transform active:scale-95 shadow-lg shadow-orange-500/20 hover:scale-105"
        >
          <i data-lucide="zap" class="w-4 h-4 fill-white"></i>
          <span class="text-xs">{{ reel?.gifts_count || 0 }}</span>
        </button>

        <!-- Share Button -->
        <button
          @click="shareReel"
          class="flex items-center gap-1.5 font-black text-white hover:text-lkblue"
        >
          <i data-lucide="send" class="w-7 h-7"></i>
          <span class="text-xs">{{ reel?.shares_count || 0 }}</span>
        </button>
      </div>

      <!-- Bottom Left Overlay - User Info -->
      <div class="absolute bottom-4 left-4 right-20 z-10">
        <div class="flex items-center gap-3">
          <button
            @click="openUserProfile"
            class="flex items-center gap-3"
          >
            <img
              :src="reel?.avatar"
              class="w-10 h-10 rounded-full object-cover border-2 border-white"
            />
            <div>
              <p class="text-white font-bold text-sm">{{ reel?.handle }}</p>
              <p v-if="reel?.location" class="text-white/70 text-xs">📍 {{ reel.location }}</p>
            </div>
          </button>
        </div>

        <!-- Caption -->
        <div v-if="reel?.caption" class="mt-2">
          <p class="text-white text-sm">{{ reel.caption }}</p>
        </div>
      </div>

      <!-- Play/Pause Overlay for Video -->
      <div
        v-if="reel?.type === 'video' && !isPlaying"
        class="absolute inset-0 flex items-center justify-center z-10"
      >
        <button
          @click="togglePlayPause"
          class="rounded-full bg-black/50 p-4 text-white hover:bg-black/70 transition"
        >
          <i data-lucide="play" class="w-12 h-12"></i>
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import axios from 'axios';
import { ref, watch, nextTick } from 'vue';
import Modal from '../ui/Modal.vue';

const props = defineProps({
  reel: { type: Object, default: null }
});

const emit = defineEmits(['likeToggled', 'commentAdded', 'giftSent', 'shareClicked', 'userProfileClicked']);
const modalRef = ref();
const videoRef = ref();
const isPlaying = ref(true);
const isLiked = ref(false);

const open = () => {
  modalRef.value?.open();
  if (props.reel?.type === 'video') {
    nextTick(() => {
      videoRef.value?.play();
    });
  }
  checkLikeStatus();
};

const close = () => {
  modalRef.value?.close();
  if (videoRef.value) {
    videoRef.value.pause();
  }
};

const togglePlayPause = () => {
  if (!videoRef.value) return;
  
  if (isPlaying.value) {
    videoRef.value.pause();
  } else {
    videoRef.value.play();
  }
  isPlaying.value = !isPlaying.value;
};

const toggleLike = async () => {
  if (!props.reel) return;
  
  try {
    const response = await axios.post(route('new_frontend.reels.like', { reel: props.reel.id }));
    isLiked.value = response.data.is_liked;
    emit('likeToggled', {
      reelId: props.reel.id,
      isLiked: response.data.is_liked,
      likesCount: response.data.likes_count
    });
  } catch (e) {
    console.error('Failed to toggle like', e);
  }
};

const checkLikeStatus = async () => {
  if (!props.reel) return;
  
  try {
    // Assuming the reel data includes is_liked from the backend
    isLiked.value = props.reel.is_liked || false;
  } catch (e) {
    console.error('Failed to check like status', e);
  }
};

const openComments = () => {
  // Open comments modal
  emit('commentAdded', { reelId: props.reel?.id });
  // For now, just show a toast message
  if (window.toast) window.toast('Comments feature coming soon!');
};

const sendGift = () => {
  // Open gift modal
  emit('giftSent', { reelId: props.reel?.id });
  // For now, just show a toast message
  if (window.toast) window.toast('Gift feature coming soon!');
};

const shareReel = async () => {
  if (!props.reel) return;
  
  try {
    await axios.post(route('new_frontend.reels.share', { reel: props.reel.id }));
    emit('shareClicked', {
      reelId: props.reel.id
    });
    
    // Native share if available
    if (navigator.share) {
      await navigator.share({
        title: 'Check out this reel!',
        text: props.reel.caption || '',
        url: window.location.href
      });
    }
  } catch (e) {
    console.error('Failed to share reel', e);
  }
};

const openUserProfile = () => {
  emit('userProfileClicked', {
    userId: props.reel?.user_id,
    handle: props.reel?.handle
  });
};

watch(() => props.reel, (newReel) => {
  if (newReel) {
    isLiked.value = newReel.is_liked || false;
  }
});

defineExpose({ open, close });
</script>

<style scoped>
.reel-modal :deep(.modal-content) {
  padding: 0;
  border-radius: 0;
  max-height: 90vh;
  height: 80vh;
  max-width: 500px;
  margin: auto;
}
</style>