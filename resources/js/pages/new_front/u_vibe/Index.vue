<template>
  <div class="fade">
    <!-- Header Section -->
    <div class="rounded-3xl overflow-hidden mb-6" style="background:linear-gradient(120deg,#7c3aed,#a855f7)">
      <div class="p-5 sm:p-6 text-white space-y-4">
        <!-- Top Title Row -->
        <div class="flex items-center justify-between gap-3 flex-wrap">
          <div class="flex items-center gap-3">
            <span class="h-12 w-12 rounded-2xl bg-white/20 grid place-items-center shrink-0">
              <i data-lucide="graduation-cap" class="w-6 h-6"></i>
            </span>
            <div>
              <h1 class="text-2xl font-black tracking-tight">U Vibe</h1>
              <p class="text-white/80 text-xs sm:text-sm font-bold">University Vibes · cross-campus network</p>
            </div>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <button @click="openCampusExplorer" class="h-10 w-10 rounded-xl bg-white/20 hover:bg-white/30 grid place-items-center text-white transition active:scale-95" title="Search">
              <i data-lucide="search" class="w-4 h-4"></i>
            </button>
            <button @click="openMessages" class="h-10 w-10 rounded-xl bg-white/20 hover:bg-white/30 grid place-items-center text-white transition active:scale-95" title="Messages">
              <i data-lucide="message-circle" class="w-4 h-4"></i>
            </button>
            <button @click="openThankYou" class="h-10 w-10 rounded-xl bg-white/20 hover:bg-white/30 grid place-items-center text-lg text-white transition active:scale-95" title="Say Thank You">
              🙏
            </button>
            <button @click="openCompose" class="btn bg-white text-violet-700 hover:bg-violet-50 px-4 py-2 rounded-xl font-black text-sm flex items-center gap-1.5 shadow-md active:scale-95">
              <i data-lucide="plus" class="w-4 h-4"></i>Post
            </button>
          </div>
        </div>

        <!-- Campus Action Filter Pills -->
        <div class="flex gap-2 overflow-x-auto hide-scroll pt-1 pb-1">
          <button @click="openMyUniversities"
                  class="rounded-full px-4 py-2 text-xs sm:text-sm font-black shrink-0 transition bg-white/20 hover:bg-white/30 text-white flex items-center gap-1.5 border border-white/30 active:scale-95">
            + Set your university
          </button>
          <button @click="openCampusExplorer"
                  class="rounded-full px-4 py-2 text-xs sm:text-sm font-black shrink-0 transition bg-white text-violet-700 shadow-sm flex items-center gap-1.5 active:scale-95">
            🎓 All Campuses
          </button>
          <button @click="openCampusExplorer"
                  class="rounded-full px-4 py-2 text-xs sm:text-sm font-black shrink-0 transition bg-white/20 hover:bg-white/30 text-white flex items-center gap-1.5 border border-white/30 active:scale-95">
            🧭 Explore · 57
          </button>
        </div>

        <!-- Category Filters -->
        <div class="flex gap-2 overflow-x-auto hide-scroll pt-1">
          <button v-for="cat in categoryList" :key="cat.id" @click="setCategory(cat.id)"
                  :class="['rounded-full px-4 py-2 text-xs sm:text-sm font-black shrink-0 flex items-center gap-1.5 transition',
                           activeCategory === cat.id ? 'bg-white text-violet-700 shadow-sm' : 'bg-white/20 text-white hover:bg-white/30']">
            <span>{{ cat.icon }}</span> {{ cat.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Main Grid Layout -->
    <div class="grid lg:grid-cols-3 gap-6">
      <!-- Main Feed Column -->
      <div class="lg:col-span-2 space-y-4">

        <!-- Stories Carousel -->
        <div class="card p-4">
          <div class="flex gap-4 overflow-x-auto hide-scroll">
            <button @click="openCompose" class="flex flex-col items-center gap-1 shrink-0">
              <span class="w-16 h-16 rounded-full border-2 border-dashed border-slate-300 grid place-items-center text-slate-400 hover:border-violet-400 transition">
                <i data-lucide="plus" class="w-6 h-6"></i>
              </span>
              <span class="text-xs font-bold text-slate-500">Add</span>
            </button>
            <button v-for="s in SEED.stories" :key="s.handle" @click="showToast('Story · ' + s.handle)"
                    class="flex flex-col items-center gap-1 shrink-0">
              <span class="w-16 h-16 rounded-full p-[3px] bg-gradient-to-tr from-violet-500 via-fuchsia-500 to-purple-600 shadow-sm">
                <img :src="s.avatar" class="w-full h-full rounded-full object-cover border-2 border-white"/>
              </span>
              <span class="text-xs font-semibold text-slate-600 max-w-[64px] truncate">{{ s.handle }}</span>
            </button>
          </div>
        </div>

        <!-- Share on your campus bar -->
        <div class="card p-4 flex items-center gap-3 shadow-sm">
          <img :src="user.avatar" class="w-11 h-11 rounded-full object-cover shadow-sm"/>
          <button @click="openCompose"
                  class="flex-1 text-left bg-slate-100 hover:bg-slate-200 rounded-full px-5 py-3 text-slate-500 font-semibold transition">
            Share on your campus…
          </button>
          <button @click="openCompose"
                  class="w-11 h-11 rounded-full bg-gradient-to-tr from-violet-600 to-purple-600 text-white grid place-items-center shadow-md active:scale-95 transition">
            <i data-lucide="image-plus" class="w-5 h-5"></i>
          </button>
        </div>

        <!-- Posts Feed -->
        <div class="space-y-4">
          <div v-if="filteredPosts.length === 0" class="text-slate-400 font-bold text-center py-16">
            No posts match this filter yet.
          </div>

          <div v-for="p in filteredPosts" :key="p.id" class="card overflow-hidden shadow-sm">
            <div class="p-4 flex items-center gap-3">
              <div class="flex items-center gap-3 flex-1 min-w-0 text-left cursor-pointer" @click="showToast('Profile · ' + p.org)">
                <span class="h-11 w-11 rounded-xl bg-violet-100 text-violet-700 grid place-items-center font-black shrink-0">
                  {{ (typeof p.org === 'string' ? p.org : p.org?.name || 'U').slice(0, 1) }}
                </span>
                <div class="flex-1 min-w-0">
                  <p class="font-black text-sm flex items-center gap-1 truncate">
                    {{ typeof p.org === 'string' ? p.org : p.org?.name || 'University' }}
                    <i v-if="p.verified" data-lucide="badge-check" class="w-3.5 h-3.5 text-lkblue shrink-0"></i>
                  </p>
                  <p class="text-[11px] text-slate-400 truncate">{{ p.group }} · 1.2k followers · {{ p.time }}</p>
                </div>
              </div>
              <button @click="showToast('🌹 Vibe sent to ' + p.org)" title="Send a Vibe" class="text-lg shrink-0 hover:scale-110 transition">🌹</button>
              <span class="chip shrink-0" style="background:#7c3aed14;color:#7c3aed;border-color:#7c3aed22">{{ p.tagLabel }}</span>
            </div>

            <img v-if="p.image" :src="p.image" class="w-full h-56 object-cover" />

            <div class="p-4">
              <div class="flex items-center justify-between gap-2 mb-1">
                <p class="font-black text-lg">{{ p.title }}</p>
                <span v-if="p.price !== undefined && p.price !== null" class="text-emerald-600 font-black shrink-0">
                  {{ p.price > 0 ? money(p.price) : 'Free' }}
                </span>
              </div>
              <p class="text-sm text-slate-600 leading-relaxed">{{ p.desc }}</p>
              <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                <div class="flex items-center gap-4 text-slate-500 text-sm font-bold">
                  <span class="flex items-center gap-1.5 cursor-pointer hover:text-rose-500 transition"><i data-lucide="heart" class="w-4 h-4"></i>{{ num(p.likes) }}</span>
                  <span class="flex items-center gap-1.5 cursor-pointer hover:text-violet-600 transition"><i data-lucide="message-circle" class="w-4 h-4"></i>{{ num(p.comments) }}</span>
                  <button @click="showToast('🔗 Link copied')" class="hover:text-lkblue transition"><i data-lucide="share-2" class="w-4 h-4"></i></button>
                </div>
                <span v-if="p.onVibes" class="chip flex items-center gap-1" style="background:#05966914;color:#059669;border-color:#05966922">
                  <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>On Vibes
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Sidebar Column -->
      <div class="space-y-4">
        <!-- My Earnings Card -->
        <button @click="openEarnings"
                class="w-full rounded-2xl p-4 text-white text-left flex items-center justify-between shadow-lg shadow-emerald-600/20 hover:scale-[1.02] transition-transform active:scale-95"
                style="background:linear-gradient(135deg,#059669,#10b981)">
          <div>
            <p class="font-black text-sm flex items-center gap-1.5">
              <i data-lucide="trending-up" class="w-4 h-4"></i>My Earnings
            </p>
            <p class="text-[11px] text-white/80 mt-0.5">Commission from tagged sales</p>
          </div>
          <p class="text-xl font-black">{{ money(props.stats?.total_commission || 0) }}</p>
        </button>

        <!-- Quick Links Card -->
        <div class="card p-4 space-y-2 shadow-sm">
          <h3 class="font-black mb-3">Quick Links</h3>
          <button @click="router.visit(route('new_frontend.marketplace'))" class="w-full flex items-center gap-3 rounded-2xl border border-slate-100 p-2.5 text-left hover:border-violet-200 transition">
            <span class="h-9 w-9 rounded-xl grid place-items-center shrink-0" style="background:#7c3aed14;color:#7c3aed">
              <i data-lucide="store" class="w-4 h-4"></i>
            </span>
            <span class="font-bold text-sm text-slate-700">Sell on the Marketplace</span>
          </button>
          <button @click="setCategory('opportunity')" class="w-full flex items-center gap-3 rounded-2xl border border-slate-100 p-2.5 text-left hover:border-violet-200 transition">
            <span class="h-9 w-9 rounded-xl grid place-items-center shrink-0" style="background:#7c3aed14;color:#7c3aed">
              <i data-lucide="briefcase" class="w-4 h-4"></i>
            </span>
            <span class="font-bold text-sm text-slate-700">Browse Campus Gigs</span>
          </button>
        </div>

        <!-- Categories Card -->
        <div class="card p-4 shadow-sm">
          <h3 class="font-black mb-3">Categories</h3>
          <div class="flex flex-wrap gap-2">
            <span v-for="[id, label] in UVIBE_CATS.filter(c => c[0] !== 'all')" :key="id"
                  @click="setCategory(id)"
                  class="chip cursor-pointer hover:bg-violet-50 hover:text-violet-700 transition">
              {{ label }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Compose Modal -->
    <ComposeUVibeModal ref="composeModalRef" :affiliateItems="props.affiliateItems" @postCreated="onPostCreated" />

    <!-- Say Thank You Modal -->
    <SayThankYouModal ref="sayThankYouModalRef" />

    <!-- Vibes Messages Modal -->
    <VibesMessagesModal ref="vibesMessagesModalRef" @selectUser="onSelectChatUser" />

    <!-- Vibes Chat Modal -->
    <VibesChatModal ref="vibesChatModalRef" @back="openMessages" />

    <!-- Affiliate Hub Modal -->
    <AffiliateHubModal ref="affiliateHubModalRef" :items="props.affiliateItems" :stats="props.stats" />

    <!-- Keep Vibes Clean Modal -->
    <KeepVibesCleanModal ref="keepVibesCleanModalRef" />

    <!-- My Universities Modal -->
    <MyUniversitiesModal ref="myUniversitiesModalRef" @addUniversity="openCampusExplorer" />

    <!-- Campus Explorer Modal -->
    <CampusExplorerModal ref="campusExplorerModalRef" @manageUniversities="openMyUniversities" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import ComposeUVibeModal from '../../../components/new_frontend/modals/ComposeUVibeModal.vue';
import SayThankYouModal from '../../../components/new_frontend/modals/SayThankYouModal.vue';
import VibesMessagesModal from '../../../components/new_frontend/modals/VibesMessagesModal.vue';
import VibesChatModal from '../../../components/new_frontend/modals/VibesChatModal.vue';
import AffiliateHubModal from '../../../components/new_frontend/modals/AffiliateHubModal.vue';
import KeepVibesCleanModal from '../../../components/new_frontend/modals/KeepVibesCleanModal.vue';
import MyUniversitiesModal from '../../../components/new_frontend/modals/MyUniversitiesModal.vue';
import CampusExplorerModal from '../../../components/new_frontend/modals/CampusExplorerModal.vue';
import { getUVibes, UVIBE_CAMPUSES, UVIBE_CATS, SEED, getUser } from '../../../components/new_frontend/MockDataStore';

defineOptions({ layout: MainLayout });

const props = defineProps({
  affiliateItems: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) }
});

const page = usePage();
const user = computed(() => page.props.auth?.user || getUser());

const activeCampus = ref('All Campuses');
const activeCategory = ref('all');
const posts = ref(getUVibes());

const setCampus = (c) => activeCampus.value = c;
const setCategory = (c) => activeCategory.value = c;

const composeModalRef = ref(null);
const sayThankYouModalRef = ref(null);
const vibesMessagesModalRef = ref(null);
const vibesChatModalRef = ref(null);
const affiliateHubModalRef = ref(null);
const keepVibesCleanModalRef = ref(null);
const myUniversitiesModalRef = ref(null);
const campusExplorerModalRef = ref(null);

const categoryList = [
  { id: 'all', label: 'All', icon: ' ' },
  { id: 'events', label: 'Events', icon: '📅' },
  { id: 'parties', label: 'Parties', icon: '🎉' },
  { id: 'campus-life', label: 'Campus Life', icon: '🏫' },
  { id: 'clubs', label: 'Clubs', icon: '👥' },
  { id: 'food', label: 'Food', icon: '🍴' },
  { id: 'opportunity', label: 'Gigs', icon: '🧳' },
];

const openMyUniversities = () => {
  if (myUniversitiesModalRef.value) {
    myUniversitiesModalRef.value.open();
  }
};

const openCampusExplorer = () => {
  if (campusExplorerModalRef.value) {
    campusExplorerModalRef.value.open();
  }
};

onMounted(() => {
  if (keepVibesCleanModalRef.value) {
    keepVibesCleanModalRef.value.open();
  }
});

const openCompose = () => {
  if (composeModalRef.value) {
    composeModalRef.value.open();
  }
};

const openMessages = async () => {
  try {
    const res = await axios.get(route('new_frontend.vibes.chats.conversations'));
    const conversations = res.data?.conversations || [];
    if (vibesMessagesModalRef.value) {
      vibesMessagesModalRef.value.open(conversations);
    }
  } catch (e) {
    if (vibesMessagesModalRef.value) {
      vibesMessagesModalRef.value.open([]);
    }
  }
};

const onSelectChatUser = (targetUser) => {
  if (vibesChatModalRef.value) {
    vibesChatModalRef.value.open(targetUser);
  }
};

const openThankYou = async () => {
  try {
    const res = await axios.get(route('new_frontend.vibes.gifts-received'));
    const gifts = res.data?.gifts_received || [];
    if (sayThankYouModalRef.value) {
      sayThankYouModalRef.value.open(gifts);
    }
  } catch (e) {
    if (sayThankYouModalRef.value) {
      sayThankYouModalRef.value.open([]);
    }
  }
};

const openEarnings = () => {
  if (affiliateHubModalRef.value) {
    affiliateHubModalRef.value.open('earnings');
  }
};

const showToast = (msg) => {
  if (window.toast) window.toast(msg);
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

const num = (n) => Number(n).toLocaleString();
const money = (n) => '$' + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
</script>
