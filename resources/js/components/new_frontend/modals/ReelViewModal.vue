<template>
  <Modal ref="modalRef" class="reel-modal">
    <div class="relative h-full bg-black">
      <!-- Close Button -->
      <button
        @click="close"
        class="absolute top-4 right-4 z-20 rounded-full bg-black/50 p-2 text-white hover:bg-black/70 transition"
      >
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>

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
          class="flex flex-col items-center gap-1 text-white transition hover:scale-110"
        >
          <div class="rounded-full bg-black/50 p-3">
            <i
              data-lucide="heart"
              class="w-6 h-6"
              :class="isLiked ? 'fill-red-500 text-red-500' : ''"
            ></i>
          </div>
          <span class="text-xs font-bold">{{ reel?.likes_count || 0 }}</span>
        </button>

        <!-- Comment Button -->
        <button
          @click="openComments"
          class="flex flex-col items-center gap-1 text-white transition hover:scale-110"
        >
          <div class="rounded-full bg-black/50 p-3">
            <i data-lucide="message-circle" class="w-6 h-6"></i>
          </div>
          <span class="text-xs font-bold">{{ reel?.comments_count || 0 }}</span>
        </button>

        <!-- Gift Button -->
        <button
          @click="sendGift"
          class="flex flex-col items-center gap-1 text-white transition hover:scale-110"
        >
          <div class="rounded-full bg-black/50 p-3">
            <i data-lucide="gift" class="w-6 h-6"></i>
          </div>
          <span class="text-xs font-bold">{{ reel?.gifts_count || 0 }}</span>
        </button>

        <!-- Share Button -->
        <button
          @click="shareReel"
          class="flex flex-col items-center gap-1 text-white transition hover:scale-110"
        >
          <div class="rounded-full bg-black/50 p-3">
            <i data-lucide="share-2" class="w-6 h-6"></i>
          </div>
          <span class="text-xs font-bold">{{ reel?.shares_count || 0 }}</span>
        </button>
      </div>

      <!-- Bottom Right Overlay - User Info -->
      <div class="absolute bottom-4 right-4 left-20 z-10">
        <div class="flex items-center gap-3 bg-black/50 rounded-2xl p-3 backdrop-blur-sm">
          <button
            @click="openUserProfile"
            class="flex items-center gap-3 flex-1"
          >
            <img
              :src="reel?.avatar"
              class="w-12 h-12 rounded-full object-cover border-2 border-white"
            />
            <div class="flex-1">
              <p class="text-white font-bold text-sm">{{ reel?.handle }}</p>
              <p v-if="reel?.location" class="text-white/70 text-xs">📍 {{ reel.location }}</p>
            </div>
          </button>
        </div>

        <!-- Caption -->
        <div v-if="reel?.caption" class="mt-2 bg-black/50 rounded-2xl p-3 backdrop-blur-sm">
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
  // For now, just emit an event - comments modal can be added later
  emit('commentAdded', { reelId: props.reel?.id });
};

const sendGift = () => {
  // For now, just emit an event - gift modal can be added later
  emit('giftSent', { reelId: props.reel?.id });
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
  max-height: 100vh;
  height: 100vh;
}
</style>