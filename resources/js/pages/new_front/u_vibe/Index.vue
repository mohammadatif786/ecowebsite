<template>
  <div class="fade">
    <!-- Header Section -->
    <div class="rounded-3xl overflow-hidden mb-6" style="background:linear-gradient(120deg,#7c3aed,#a855f7)">
      <div class="p-6 text-white">
        <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
          <div class="flex items-center gap-3">
            <span class="h-14 w-14 rounded-2xl bg-white/20 grid place-items-center shrink-0">
              <i data-lucide="graduation-cap" class="w-7 h-7"></i>
            </span>
            <div>
              <h1 class="text-2xl font-black">U Vibe</h1>
              <p class="text-white/75 text-sm font-bold">University Vibes · cross-campus network</p>
            </div>
          </div>
          <button @click="openCompose" class="btn bg-white text-violet-700 px-4 py-2.5 font-black flex items-center gap-1.5 shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i>Post
          </button>
        </div>

        <!-- Campus Filters -->
        <div class="flex gap-2 overflow-x-auto hide-scroll pb-2">
          <button @click="setCampus('All Campuses')"
                  :class="['rounded-full px-4 py-2 text-sm font-black shrink-0', activeCampus === 'All Campuses' ? 'bg-white text-violet-700' : 'bg-white/15 text-white']">
            🎓 All Campuses
          </button>
          <button v-for="[name, flag] in UVIBE_CAMPUSES" :key="name" @click="setCampus(name)"
                  :class="['rounded-full px-4 py-2 text-sm font-black shrink-0', activeCampus === name ? 'bg-white text-violet-700' : 'bg-white/15 text-white']">
            {{ flag }} {{ name }}
          </button>
        </div>

        <!-- Category Filters -->
        <div class="flex gap-2 overflow-x-auto hide-scroll mt-2">
          <button v-for="[id, label, icon] in UVIBE_CATS" :key="id" @click="setCategory(id)"
                  :class="['rounded-full px-4 py-2 text-sm font-black shrink-0 flex items-center gap-1.5', activeCategory === id ? 'bg-white text-violet-700' : 'bg-white/15 text-white']">
            <i :data-lucide="icon" class="w-3.5 h-3.5"></i>{{ label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Leaderboard -->
    <div class="max-w-2xl mx-auto">
      <div class="card p-5 mb-4">
        <div class="flex items-center gap-2.5 mb-3">
          <span class="h-9 w-9 rounded-xl grid place-items-center shrink-0" style="background:#7c3aed1a;color:#7c3aed">
            <i data-lucide="trophy" class="w-4 h-4"></i>
          </span>
          <div>
            <p class="font-black text-sm">Campus Leaderboard</p>
            <p class="text-[11px] text-slate-400 font-bold">This week's most active campus · tap to view</p>
          </div>
        </div>
        <div class="space-y-2.5">
          <button v-for="(r, i) in leaderboard" :key="r.name" @click="setCampus(r.name)"
                  :class="['w-full text-left', activeCampus === r.name ? 'opacity-100' : 'opacity-90 hover:opacity-100']">
            <div class="flex items-center gap-3">
              <span class="w-6 text-center font-black text-sm shrink-0">{{ medal(i) }}</span>
              <span class="text-lg shrink-0">{{ r.flag }}</span>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <p class="font-black text-sm truncate">
                    {{ r.name }}
                    <span v-if="activeCampus === r.name" class="text-violet-600">· viewing</span>
                  </p>
                  <p class="text-xs font-black text-violet-600 shrink-0">{{ r.score }} pts</p>
                </div>
                <div class="h-1.5 rounded-full bg-slate-100 mt-1 overflow-hidden">
                  <div class="h-full rounded-full" :style="{ width: Math.round(r.score / leaderboardMaxScore * 100) + '%', background: 'linear-gradient(90deg,#7c3aed,#a855f7)' }"></div>
                </div>
              </div>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Posts Feed -->
    <div class="space-y-4 max-w-2xl mx-auto">
      <div v-if="filteredPosts.length === 0" class="text-slate-400 font-bold text-center py-16">
        No posts match this filter yet.
      </div>

      <div v-for="p in filteredPosts" :key="p.id" class="card overflow-hidden">
        <div class="p-4 flex items-center gap-3">
          <span class="h-11 w-11 rounded-xl bg-violet-100 text-violet-700 grid place-items-center font-black shrink-0">
            {{ (typeof p.org === 'string' ? p.org : p.org?.name || 'U').slice(0, 1) }}
          </span>
          <div class="flex-1 min-w-0">
            <p class="font-black text-sm flex items-center gap-1 truncate">
              {{ typeof p.org === 'string' ? p.org : p.org?.name || 'University' }}
              <i v-if="p.verified" data-lucide="badge-check" class="w-3.5 h-3.5 text-lkblue shrink-0"></i>
            </p>
            <p class="text-[11px] text-slate-400 truncate">{{ p.group }} · {{ p.time }}</p>
          </div>
          <span class="chip shrink-0" style="background:#7c3aed14;color:#7c3aed;border-color:#7c3aed22">{{ p.tagLabel }}</span>
        </div>

        <img v-if="p.image" :src="p.image" class="w-full h-56 object-cover" />

        <div class="p-4">
          <div class="flex items-center justify-between gap-2 mb-1">
            <p class="font-black text-lg">{{ p.title }}</p>
            <span v-if="p.price" class="text-emerald-600 font-black shrink-0">{{ money(p.price) }}</span>
          </div>
          <p class="text-sm text-slate-600">{{ p.desc }}</p>
          <div class="flex items-center justify-between mt-3">
            <div class="flex items-center gap-4 text-slate-500 text-sm font-bold">
              <span class="flex items-center gap-1.5"><i data-lucide="heart" class="w-4 h-4"></i>{{ num(p.likes) }}</span>
              <span class="flex items-center gap-1.5"><i data-lucide="message-circle" class="w-4 h-4"></i>{{ num(p.comments) }}</span>
              <button><i data-lucide="share-2" class="w-4 h-4"></i></button>
            </div>
            <span v-if="p.onVibes" class="chip flex items-center gap-1" style="background:#05966914;color:#059669;border-color:#05966922">
              <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>On Vibes
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Compose Modal -->
    <ComposeUVibeModal ref="composeModalRef" @postCreated="onPostCreated" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import ComposeUVibeModal from '../../../components/new_frontend/modals/ComposeUVibeModal.vue';
import { getUVibes, UVIBE_CAMPUSES, UVIBE_CATS } from '../../../components/new_frontend/MockDataStore';

defineOptions({ layout: MainLayout });

const activeCampus = ref('All Campuses');
const activeCategory = ref('all');
const posts = ref(getUVibes());

const setCampus = (c) => activeCampus.value = c;
const setCategory = (c) => activeCategory.value = c;

const composeModalRef = ref(null);

const openCompose = () => {
  if (composeModalRef.value) {
    composeModalRef.value.open();
  }
};

const onPostCreated = (newPost) => {
  posts.value.unshift(newPost);
};

const filteredPosts = computed(() => {
  let filtered = posts.value;
  if (activeCampus.value !== 'All Campuses') {
    filtered = filtered.filter(p => p.campus === activeCampus.value);
  }
  if (activeCategory.value !== 'all') {
    filtered = filtered.filter(p => p.category === activeCategory.value);
  }
  return filtered;
});

const leaderboard = computed(() => {
  return UVIBE_CAMPUSES.map(([name, flag]) => {
    const cp = posts.value.filter(p => p.campus === name);
    const likes = cp.reduce((s, p) => s + (p.likes || 0), 0);
    const comments = cp.reduce((s, p) => s + (p.comments || 0), 0);
    const score = cp.length * 25 + likes + comments * 3;
    return { name, flag, posts: cp.length, likes, comments, score };
  }).sort((a, b) => b.score - a.score);
});

const leaderboardMaxScore = computed(() => {
  return leaderboard.value.length ? Math.max(1, leaderboard.value[0].score) : 1;
});

const medal = (i) => {
  const medals = ['🥇', '🥈', '🥉'];
  return medals[i] || (i + 1);
};

const num = (n) => Number(n).toLocaleString();
const money = (n) => '$' + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
</script>
