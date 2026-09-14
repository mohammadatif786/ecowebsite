<template>
  <div class="fade">
    <!-- Section Head -->
    <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
      <div>
        <h1 class="text-2xl md:text-3xl font-black text-lkink">✨ Vibes</h1>
        <p class="text-slate-500 font-semibold mt-1">Photos, reels & shoppable moments</p>
      </div>
      <div class="flex items-center gap-2">
        <button @click="openCompose" class="btn btn-primary px-4 py-2.5 flex items-center gap-2">
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
            <button @click="openCompose" class="flex flex-col items-center gap-1 shrink-0">
              <span class="w-16 h-16 rounded-full border-2 border-dashed border-slate-300 grid place-items-center text-slate-400">
                <i data-lucide="plus" class="w-6 h-6"></i>
              </span>
              <span class="text-xs font-bold text-slate-500">Add</span>
            </button>
            <button v-for="story in stories" :key="story.handle" class="flex flex-col items-center gap-1 shrink-0">
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
        <div class="card p-4">
          <h3 class="font-black mb-3">Trending tags</h3>
          <div class="flex flex-wrap gap-2">
            <span v-for="t in trendingTags" :key="t" class="chip"  @click="showToast(t)">{{ t }}</span>
          </div>
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
    <ComposeVibeModal ref="composeModalRef" :publishers="vibePublishers" @postCreated="onPostCreated" />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import VibeCard from '../../../components/new_frontend/cards/VibeCard.vue';
import ComposeVibeModal from '../../../components/new_frontend/modals/ComposeVibeModal.vue';
import { getVibes, SEED, getUser } from '../../../components/new_frontend/MockDataStore';

defineOptions({ layout: MainLayout });

const props = defineProps({
  vibePublishers: { type: Object, default: () => ({ organizations: [], groups: [] }) },
  vibes: { type: Array, default: () => [] },
});

const user = getUser();
const posts = ref([...props.vibes]);

// Use mock data as fallback if database is empty for now
if (posts.value.length === 0) {
  posts.value = getVibes().map(v => ({
    ...v,
    media: [{ id: v.id, type: v.kind === 'reel' ? 'video' : 'image', url: v.media }]
  }));
}

const stories = SEED.stories;

const trendingTags = ['#carnival2026', '#soca', '#fete', '#amapiano', '#islandlife', '#linkup', '#shopcaribbean'];
const suggestedCreators = SEED.stories.slice(0, 4);

const composeModalRef = ref(null);

const openCompose = () => {
  if (composeModalRef.value) {
    composeModalRef.value.open();
  }
};

const onPostCreated = (newPost) => {
  // The ComposeVibeModal already emits a formatted post object
  posts.value.unshift(newPost);
};

const followCreator = (event) => {
  event.target.textContent = 'Following';
  event.target.classList.add('opacity-60');
};
const showToast = (msg) => {
  if (window.toast) window.toast(msg);
};
</script>
