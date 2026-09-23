<template>
  <Modal ref="modalRef" class="user-profile-modal">
    <div class="relative">
      <!-- Close Button -->
      <button
        @click="close"
        class="absolute top-4 right-4 z-20 rounded-full bg-white/20 p-2 text-white hover:bg-white/30 transition"
      >
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>

      <!-- Gradient Header -->
      <div class="h-48 bg-gradient-to-br from-blue-600 via-purple-600 to-pink-500 relative">
        <div class="absolute inset-0 bg-black/20"></div>
      </div>

      <!-- Profile Info -->
      <div class="relative px-6 pb-6">
        <!-- Profile Image -->
        <div class="absolute -top-16 left-6">
          <img
            :src="user?.avatar || ('https://i.pravatar.cc/150?u=' + (user?.id || props.userId))"
            @error="$event.target.src = 'https://i.pravatar.cc/150?u=' + (user?.id || props.userId)"
            class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg bg-slate-100"
          />
        </div>

        <!-- User Details -->
        <div class="pt-20">
          <h2 class="text-2xl font-black text-slate-800">{{ user?.name || 'User' }}</h2>
          <p class="text-slate-500 font-semibold">@{{ user?.linkup_id || user?.name || 'user' }}</p>
          <p v-if="user?.city || user?.country" class="text-slate-400 text-sm mt-1">
            📍 {{ [user?.city, user?.country].filter(Boolean).join(', ') }}
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 mt-4">
          <button
            @click="messageUser"
            class="flex-1 btn btn-primary py-2.5 font-bold flex items-center justify-center gap-2"
          >
            <i data-lucide="message-square" class="w-4 h-4"></i>
            Message
          </button>
          <button
            @click="toggleFollow"
            :class="['flex-1 btn py-2.5 font-bold transition flex items-center justify-center gap-2', isFollowing ? 'bg-slate-100 hover:bg-slate-200 text-slate-700' : 'btn-primary']"
          >
            <i :data-lucide="isFollowing ? 'user-check' : 'user-plus'" class="w-4 h-4"></i>
            {{ isFollowing ? 'Following' : 'Follow' }}
          </button>
        </div>

        <!-- Reels Grid -->
        <div class="mt-6">
          <h3 class="font-black text-slate-800 mb-4">Reels</h3>

          <div v-if="loading" class="text-center py-8">
            <p class="text-slate-400 font-semibold">Loading reels...</p>
          </div>

          <div v-else-if="reels.length === 0" class="text-center py-8">
            <p class="text-slate-400 font-semibold">No reels yet</p>
          </div>

          <div v-else class="grid grid-cols-3 gap-2">
            <div
              v-for="reel in reels"
              :key="reel.id"
              @click="viewReel(reel)"
              class="relative aspect-[9/16] rounded-xl overflow-hidden cursor-pointer group"
            >
              <video
                v-if="reel.type === 'video'"
                :src="reel.file_path"
                class="w-full h-full object-cover"
                muted
              />
              <img
                v-else
                :src="reel.file_path"
                class="w-full h-full object-cover"
              />
              <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition">
                <div class="absolute bottom-2 left-2 right-2 flex items-center gap-2 text-white text-xs font-bold">
                  <i data-lucide="heart" class="w-3 h-3"></i>
                  {{ reel.likes_count }}
                  <i data-lucide="message-circle" class="w-3 h-3"></i>
                  {{ reel.comments_count }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import axios from 'axios';
import { ref, watch, nextTick } from 'vue';
import Modal from '../ui/Modal.vue';

const props = defineProps({
  userId: { type: [Number, String], default: null }
});

const emit = defineEmits(['reelClicked', 'messageClicked', 'followToggled']);
const modalRef = ref();
const user = ref(null);
const reels = ref([]);
const loading = ref(false);
const isFollowing = ref(false);
const activeUserId = ref(null);

const open = async (id) => {
  activeUserId.value = id || props.userId;
  modalRef.value?.open();
  await loadUserDataAndReels();
  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
  });
};

const close = () => {
  modalRef.value?.close();
};

const loadUserDataAndReels = async () => {
  const targetId = activeUserId.value || props.userId;
  if (!targetId) return;
  loading.value = true;
  try {
    const response = await axios.get(route('new_frontend.reels.user', { user: targetId }));
    user.value = response.data.user || null;
    reels.value = response.data.reels || [];
    isFollowing.value = !!response.data.user?.is_following;
  } catch (e) {
    console.error('Failed to load user profile data:', e);
    reels.value = [];
  } finally {
    loading.value = false;
  }
};

const viewReel = (reel) => {
  emit('reelClicked', reel);
};

const messageUser = () => {
  close();
  emit('messageClicked', {
    userId: props.userId,
    user_id: props.userId,
    handle: user.value?.linkup_id || user.value?.name,
    avatar: user.value?.avatar,
    name: user.value?.name
  });
};

const toggleFollow = async () => {
  try {
    const response = await axios.post(route('new_frontend.creators.toggle-follow', { user: props.userId }));
    isFollowing.value = response.data.is_following;
    if (user.value) {
      user.value.is_following = response.data.is_following;
    }
    if (window.toast) {
      window.toast(isFollowing.value ? 'Following @' + (user.value?.linkup_id || user.value?.name) : 'Unfollowed @' + (user.value?.linkup_id || user.value?.name));
    }
    emit('followToggled', {
      userId: props.userId,
      isFollowing: isFollowing.value,
      reels: response.data.reels || []
    });
  } catch (e) {
    console.error('Failed to toggle follow status:', e);
  }
};

watch(() => props.userId, () => {
  if (modalRef.value?.isOpen) {
    loadUserDataAndReels();
  }
});

defineExpose({ open, close });
</script>

<style scoped>
.user-profile-modal :deep(.modal-content) {
  max-width: 600px;
  border-radius: 1.5rem;
  overflow: hidden;
}
</style>
