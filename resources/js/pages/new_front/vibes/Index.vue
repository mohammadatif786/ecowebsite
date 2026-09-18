<template>
  <div class="fade">
    <!-- Section Head -->
    <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
      <div>
        <h1 class="text-2xl md:text-3xl font-black text-lkink">✨ Vibes</h1>
        <p class="text-slate-500 font-semibold mt-1">Photos, reels & shoppable moments</p>
      </div>
      <div class="flex items-center gap-2">
        <button @click="openNewReelModal" class="btn btn-primary px-4 py-2.5 flex items-center gap-2">
          <i data-lucide="plus" class="w-4 h-4"></i>Post
        </button>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
      <!-- Main Feed Column -->
      <div class="lg:col-span-2 space-y-4">

        <!-- Stories Carousel -->
        <div class="card p-4">
          <div class="flex gap-4 overflow-x-auto hide-scroll">
            <button @click="openNewReelModal" class="flex flex-col items-center gap-1 shrink-0">
              <span class="w-16 h-16 rounded-full border-2 border-dashed border-slate-300 grid place-items-center text-slate-400">
                <i data-lucide="plus" class="w-6 h-6"></i>
              </span>
              <span class="text-xs font-bold text-slate-500">Add</span>
            </button>
            <button 
              v-for="story in stories" 
              :key="story.id" 
              @click="openReelView(story)"
              class="flex flex-col items-center gap-1 shrink-0"
            >
              <span class="w-16 h-16 rounded-full p-[3px] bg-gradient-to-tr from-lkyellow via-pink-500 to-lkblue">
                <img :src="story.avatar" class="w-full h-full rounded-full object-cover border-2 border-white" />
              </span>
              <span class="text-xs font-semibold text-slate-600 max-w-[64px] truncate">{{ story.handle }}</span>
            </button>
          </div>
        </div>

        <!-- Composer Input -->
        <div class="card p-4 flex items-center gap-3">
          <img :src="user.avatar" class="w-11 h-11 rounded-full object-cover" />
          <button @click="openCompose" class="flex-1 text-left bg-slate-100 hover:bg-slate-200 rounded-full px-5 py-3 text-slate-500 font-semibold transition">
            Share a vibe…
          </button>
          <button @click="openCompose" class="w-11 h-11 rounded-full bg-gradient-to-tr from-blue-600 to-purple-600 text-white grid place-items-center">
            <i data-lucide="image-plus" class="w-5 h-5"></i>
          </button>
        </div>

        <!-- Feed List -->
        <div id="vibeFeed" class="space-y-4">
          <VibeCard v-for="post in posts" :key="post.id" :p="post" />
        </div>
      </div>

      <!-- Right Column Sidebar -->
      <div class="space-y-4">
        <!-- My Earnings Card -->
        <div
          @click="openEarnings"
          class="bg-emerald-600 rounded-[1.5rem] p-5 text-white shadow-lg shadow-emerald-600/20 flex items-center justify-between cursor-pointer hover:scale-[1.02] transition-transform active:scale-95"
        >
          <div>
            <div class="flex items-center gap-2 mb-0.5">
              <i data-lucide="trending-up" class="w-5 h-5"></i>
              <h3 class="font-black text-[17px] tracking-tight">My Earnings</h3>
            </div>
            <p class="text-emerald-100 text-[11px] font-bold">Affiliate Commissions</p>
          </div>
          <div class="text-2xl font-black tracking-tighter">
            {{ money(props.earningsStats?.total_commission || 0) }}
          </div>
        </div>

        <div class="card p-4">
          <h3 class="font-black mb-3">Trending tags</h3>
          <div v-if="trendingTags.length" class="flex flex-wrap gap-2">
            <span v-for="t in trendingTags" :key="t" class="chip"  @click="showToast(t)">{{ t }}</span>
          </div>
          <p v-else class="text-xs font-bold text-slate-400 py-4 text-center">No trending tags yet</p>
        </div>

        <div class="card p-4">
          <h3 class="font-black mb-3">Suggested creators</h3>
          <div v-for="creator in suggestedCreators" :key="creator.handle" class="flex items-center gap-3 py-2">
            <img :src="creator.avatar" class="w-10 h-10 rounded-full object-cover" />
            <span class="font-bold text-sm flex-1">{{ creator.handle }}</span>
            <button class="btn btn-ghost text-xs px-3 py-1.5" @click="followCreator($event)">Follow</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Compose Modal -->
    <ComposeVibeModal ref="composeModalRef" :publishers="vibePublishers" :affiliateItems="affiliateItems" @postCreated="onPostCreated" />

    <!-- Affiliate Hub Modal -->
    <AffiliateHubModal ref="affiliateHubModalRef" :items="affiliateItems" :stats="earningsStats" />

    <!-- New Reel Modal -->
    <NewReelModal ref="newReelModalRef" @reelCreated="onReelCreated" />

    <!-- Reel View Modal -->
    <ReelViewModal 
      ref="reelViewModalRef" 
      :reel="currentReel"
      @likeToggled="onReelLikeToggled"
      @commentAdded="onReelCommentAdded"
      @giftSent="onReelGiftSent"
      @shareClicked="onReelShareClicked"
      @userProfileClicked="onUserProfileClicked"
    />

    <!-- User Profile Modal -->
    <UserProfileModal 
      ref="userProfileModalRef" 
      :user-id="currentUserId"
      @reelClicked="onProfileReelClicked"
      @messageClicked="onMessageClicked"
      @followToggled="onFollowToggled"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import VibeCard from '../../../components/new_frontend/cards/VibeCard.vue';
import ComposeVibeModal from '../../../components/new_frontend/modals/ComposeVibeModal.vue';
import AffiliateHubModal from '../../../components/new_frontend/modals/AffiliateHubModal.vue';
import NewReelModal from '../../../components/new_frontend/modals/NewReelModal.vue';
import ReelViewModal from '../../../components/new_frontend/modals/ReelViewModal.vue';
import UserProfileModal from '../../../components/new_frontend/modals/UserProfileModal.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
  vibePublishers: { type: Object, default: () => ({ organizations: [], groups: [], custom: [] }) },
  vibes: { type: Array, default: () => [] },
  affiliateItems: { type: Array, default: () => [] },
  earningsStats: { type: Object, default: () => ({}) },
  trendingTags: { type: Array, default: () => [] },
  stories: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const posts = ref([...props.vibes]);

const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const stories = computed(() => props.stories || []);

const trendingTags = computed(() => props.trendingTags || []);
const suggestedCreators = computed(() => stories.value.slice(0, 4));

const composeModalRef = ref(null);
const affiliateHubModalRef = ref(null);
const newReelModalRef = ref(null);
const reelViewModalRef = ref(null);
const userProfileModalRef = ref(null);

const openCompose = () => {
  if (composeModalRef.value) {
    composeModalRef.value.open();
  }
};

const openEarnings = () => {
  if (affiliateHubModalRef.value) {
    affiliateHubModalRef.value.open('earnings');
  }
};

const openNewReelModal = () => {
  if (newReelModalRef.value) {
    newReelModalRef.value.open();
  }
};

const currentReel = ref(null);
const currentUserId = ref(null);

const openReelView = (story) => {
  currentReel.value = story;
  if (reelViewModalRef.value) {
    reelViewModalRef.value.open();
  }
};

const onPostCreated = (newPost) => {
  // The ComposeVibeModal already emits a formatted post object
  posts.value.unshift(newPost);
};

const onReelCreated = (newReel) => {
  // Add the new reel to the stories
  stories.value.unshift({
    id: newReel.id,
    uid: newReel.uid,
    handle: newReel.handle,
    avatar: newReel.avatar,
    type: newReel.type,
    file_path: newReel.file_path,
    thumbnail_path: newReel.thumbnail_path,
  });
};

const onReelLikeToggled = (data) => {
  // Update the like status in the stories
  const story = stories.value.find(s => s.id === data.reelId);
  if (story) {
    story.likes_count = data.likesCount;
    story.is_liked = data.isLiked;
  }
};

const onReelCommentAdded = (data) => {
  // Update the comment count
  const story = stories.value.find(s => s.id === data.reelId);
  if (story) {
    story.comments_count = (story.comments_count || 0) + 1;
  }
};

const onReelGiftSent = (data) => {
  // Update the gift count
  const story = stories.value.find(s => s.id === data.reelId);
  if (story) {
    story.gifts_count = (story.gifts_count || 0) + 1;
  }
};

const onReelShareClicked = (data) => {
  // Update the share count
  const story = stories.value.find(s => s.id === data.reelId);
  if (story) {
    story.shares_count = (story.shares_count || 0) + 1;
  }
};

const onUserProfileClicked = (data) => {
  currentUserId.value = data.userId;
  if (userProfileModalRef.value) {
    userProfileModalRef.value.open();
  }
};

const onProfileReelClicked = (reel) => {
  currentReel.value = reel;
  if (reelViewModalRef.value) {
    reelViewModalRef.value.open();
  }
};

const onMessageClicked = (data) => {
  // Navigate to chat with the user
  // This would typically use Inertia router
  window.location.href = route('new_frontend.dating.chats');
};

const onFollowToggled = (data) => {
  // Handle follow toggle
  console.log('Follow toggled:', data);
};

const followCreator = (event) => {
  event.target.textContent = 'Following';
  event.target.classList.add('opacity-60');
};

const showToast = (msg) => {
  if (window.toast) window.toast(msg);
};
</script>
