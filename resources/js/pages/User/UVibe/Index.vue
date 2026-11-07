<template>
  <div class="fade uv-page">
    <!-- Header Section -->
    <div class="rounded-3xl overflow-hidden mb-6" style="background:linear-gradient(120deg,#7c3aed,#a855f7)">
      <div class="p-6 text-white">
        <!-- Top Title Row -->
        <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
          <div class="flex items-center gap-3">
            <span class="h-14 w-14 rounded-2xl bg-white/20 grid place-items-center shrink-0">
              <CampusLogo :size="30" />
            </span>
            <div>
              <h1 class="text-2xl font-black">U Vibe</h1>
              <p class="text-white/75 text-sm font-bold">University Vibes · cross-campus network</p>
            </div>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <!-- Each header button is its own feature component (components/User/uvibe/…) -->
            <UVibeSearch @visit="goCampus" />
            <UVibeChat />
            <UVibeThankYou />
            <button @click="openCompose" class="btn bg-white text-violet-700 px-4 py-2.5 font-black flex items-center gap-1.5">
              <Plus class="w-4 h-4" />Post
            </button>
          </div>
        </div>

        <!-- Campus bar: main university, other own universities, followed campuses, All, Explore -->
        <div>
          <div class="cp-bar">
            <button v-if="!campus.main" type="button" @click="openMyUniversities" class="cp-chip dashed">
              ＋ Set your university
            </button>
            <button v-else type="button" @click="goCampus(campus.main)" :class="['cp-chip', { on: activeCampusId === campus.main.id }]">
              🏠 {{ campus.main.name }}
            </button>

            <button v-for="u in otherMine" :key="'m' + u.id" type="button" @click="goCampus(u)" :class="['cp-chip', { on: activeCampusId === u.id }]">
              🎓 {{ u.name }}
            </button>
            <button v-for="u in campus.following" :key="'f' + u.id" type="button" @click="goCampus(u)" :class="['cp-chip', { on: activeCampusId === u.id }]">
              {{ u.flag }} {{ u.name }}
            </button>
            <button v-if="visiting" type="button" @click="goCampus(visiting)" class="cp-chip on">
              {{ visiting.flag }} {{ visiting.name }}
            </button>

            <button type="button" @click="goCampus(null)" :class="['cp-chip', { on: activeCampusId === 'all' }]">
              🎓 All Campuses
            </button>
            <button type="button" @click="openCampusExplorer('browse')" class="cp-chip outlined">
              🧭 Explore{{ exploreCount ? ' · ' + exploreCount : '' }}
            </button>
          </div>

          <!-- Strip under the bar when viewing a campus other than your main one -->
          <div v-if="activeUni && !activeUni.is_main" class="cp-strip">
            <span class="cp-strip-text">
              <template v-if="activeUni.is_mine">Your university · {{ activeUni.status === 'alumni' ? 'Alumni' : 'Student' }}</template>
              <template v-else>{{ activeUni.is_following ? 'Following ' : 'Visiting ' }}{{ activeUni.full_name }}</template>
            </span>
            <button v-if="activeUni.is_mine" type="button" @click="openMyUniversities" class="cp-strip-btn">Manage</button>
            <button v-else type="button" @click="toggleFollowActive" :disabled="followBusy"
                    :class="['cp-strip-btn', { light: !activeUni.is_following }]">
              {{ activeUni.is_following ? 'Unfollow' : '⭐ Follow campus' }}
            </button>
          </div>
        </div>

        <!-- Category Filters -->
        <UVibeCategoryTabs v-model="activeCategory" :options="props.composeOptions" />
      </div>
    </div>

    <!-- Main Grid Layout -->
    <div class="grid lg:grid-cols-3 gap-6">
      <!-- Main Feed Column -->
      <div class="lg:col-span-2 space-y-4 min-w-0">

        <!-- Reels bar: the same component as Vibes, in U Vibe's violet -->
        <ReelStoriesBar :stories="props.stories" :my-reels-count="myReelsCount" ring="violet" @add="openNewReelModal" @mine="openCreatorReels(user.id)" @open="(s) => openCreatorReels(s.user_id)" />

        <!-- Share on your campus bar -->
        <div class="card p-4 flex items-center gap-3">
          <img :src="user.avatar" class="w-11 h-11 rounded-full object-cover" />
          <button @click="openCompose"
                  class="flex-1 text-left bg-slate-100 hover:bg-slate-200 rounded-full px-5 py-3 text-slate-500 font-semibold transition">
            Share on your campus…
          </button>
          <button @click="openCompose"
                  class="w-11 h-11 rounded-full bg-gradient-to-tr from-violet-600 to-purple-600 text-white grid place-items-center">
            <ImagePlus class="w-5 h-5" />
          </button>
        </div>

        <!-- Posts Feed: every card is a UVibePostCard; its parts (poster, repost, Boost, gig…) are components in components/User/uvibe/feed/ -->
        <div class="space-y-4">
          <p v-if="filteredPosts.length === 0" class="text-slate-400 font-bold text-center py-16">
            No posts match this filter yet.
          </p>

          <UVibePostCard v-for="p in filteredPosts" :key="p.id" :post="p" :campus="campusOf(p)"
                         @openCampus="goCampus" @menu="openPostMenu" @boost="openBoost" @report="openReport"
                         @applicants="openApplicants" @openTag="openTag"
                         @updated="replacePost" @reposted="onReposted" @needUniversity="openMyUniversities" />
        </div>
      </div>

      <!-- Right Sidebar Column -->
      <div class="space-y-4 min-w-0">
        <UVibeEarningsCard :stats="earnings" :items="props.affiliateItems" />
        <UVibeQuickLinks @browseGigs="setCategory('opportunity')" />
        <UVibeCategoriesCard :options="props.composeOptions" @pick="setCategory" />
      </div>
    </div>

    <!-- Compose Modal -->
    <ComposeUVibeModal ref="composeModalRef" :affiliateItems="props.affiliateItems" :publishers="props.publishers" :userEvents="props.userEvents"
                       :composeOptions="props.composeOptions"
                       :myUniversities="campus.my_universities" :defaultUniversityId="composeUniversityId"
                       @postCreated="onPostCreated" @openUniversityModal="openMyUniversities" />

    <!-- Keep Vibes Clean Modal -->
    <KeepVibesCleanModal ref="keepVibesCleanModalRef" />

    <!-- My Universities Modal -->
    <MyUniversitiesModal ref="myUniversitiesModalRef" :state="campus" @changed="onCampusChanged" @addUniversity="openCampusExplorer('add')" />

    <!-- Campus Explorer Modal -->
    <CampusExplorerModal ref="campusExplorerModalRef" :state="campus" @changed="onCampusChanged" @visit="goCampus" @manageUniversities="openMyUniversities" />

    <!-- Reels Viewer & New Reel Modal -->
    <ReelsViewer ref="reelsRef" @deleted="onReelDeleted" />
    <NewReelModal ref="newReelRef" :isUvibe="true" :campus="activeCampusId" @reel-created="onReelCreated" />

    <!-- Post windows: one of each serves the whole feed -->
    <PostMenuModal ref="postMenuRef" @edit="openEdit" @report="openReport" @deleted="removePost" @hidden="removePost" />
    <PostEditModal ref="postEditRef" :userEvents="props.userEvents" @updated="replacePost" />
    <PostReportModal ref="postReportRef" :reasons="props.composeOptions?.report_reasons || []" />
    <PostBoostModal ref="postBoostRef" @boosted="onBoosted" />
    <GigApplicantsModal ref="applicantsRef" @updated="replacePost" />
    <VibeTagModal ref="tagModalRef" />

    <!-- Action Modals shared by posts -->
    <CommentsModal ref="commentsRef" />
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Plus, ImagePlus } from 'lucide-vue-next';
import MainLayout from '../../../layouts/User/MainLayout.vue';
import CampusLogo from '../../../components/User/CampusLogo.vue';
import ComposeUVibeModal from '../../../components/User/modals/ComposeUVibeModal.vue';
import UVibePostCard from '../../../components/User/uvibe/feed/UVibePostCard.vue';
import PostMenuModal from '../../../components/User/uvibe/feed/PostMenuModal.vue';
import PostEditModal from '../../../components/User/uvibe/feed/PostEditModal.vue';
import PostReportModal from '../../../components/User/uvibe/feed/PostReportModal.vue';
import PostBoostModal from '../../../components/User/uvibe/feed/PostBoostModal.vue';
import GigApplicantsModal from '../../../components/User/uvibe/feed/GigApplicantsModal.vue';
import VibeTagModal from '../../../components/User/modals/VibeTagModal.vue';
import { focusLinkedPost } from '../../../services/uvibeDeepLink';
import UVibeSearch from '../../../components/User/uvibe/search/UVibeSearch.vue';
import UVibeChat from '../../../components/User/uvibe/chat/UVibeChat.vue';
import UVibeThankYou from '../../../components/User/uvibe/thanks/UVibeThankYou.vue';
import UVibeEarningsCard from '../../../components/User/uvibe/earnings/UVibeEarningsCard.vue';
import UVibeQuickLinks from '../../../components/User/uvibe/quicklinks/UVibeQuickLinks.vue';
import UVibeCategoryTabs from '../../../components/User/uvibe/categories/UVibeCategoryTabs.vue';
import UVibeCategoriesCard from '../../../components/User/uvibe/categories/UVibeCategoriesCard.vue';
import KeepVibesCleanModal from '../../../components/User/modals/KeepVibesCleanModal.vue';
import MyUniversitiesModal from '../../../components/User/modals/MyUniversitiesModal.vue';
import CampusExplorerModal from '../../../components/User/modals/CampusExplorerModal.vue';
import ReelStoriesBar from '../../../components/User/vibes/stories/ReelStoriesBar.vue';
import ReelsViewer from '../../../components/User/vibes/reels/ReelsViewer.vue';
import NewReelModal from '../../../components/User/modals/NewReelModal.vue';
import { provideVibeActions } from '../../../components/User/vibes/useVibeActions';
import CommentsModal from '../../../components/User/vibes/comments/CommentsModal.vue';
import { getUser } from '../../../components/User/MockDataStore';

defineOptions({ layout: MainLayout });

const props = defineProps({
  affiliateItems: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  // Same numbers as `stats`; this is the one the Promote & Earn window refreshes after a payout.
  earningsStats: { type: Object, default: null },
  uvibes: { type: Array, default: () => [] },
  stories: { type: Array, default: () => [] },
  myReelsCount: { type: Number, default: 0 },
  allReels: { type: Array, default: () => [] },
  publishers: { type: Object, default: () => ({ organizations: [], custom: [] }) },
  // Post types, organization types and text-card looks for the composer (same data the app gets).
  composeOptions: { type: Object, default: null },
  userEvents: { type: Array, default: () => [] },
  // { main, my_universities, following, max, total_campuses } from the server
  campusState: { type: Object, default: () => ({ main: null, my_universities: [], following: [], max: 5, total_campuses: 0 }) },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || getUser());
const earnings = computed(() => props.earningsStats || props.stats || {});

const reels = ref([...props.allReels]);
const myReelsCount = ref(props.myReelsCount);
watch(() => props.allReels, (v) => (reels.value = [...(v || [])]));
watch(() => props.myReelsCount, (v) => (myReelsCount.value = v));

const newReelRef = ref(null);
const reelsRef = ref(null);
const commentsRef = ref(null);


function openNewReelModal() {
  newReelRef.value?.open();
}
function openCreatorReels(userId) {
  reelsRef.value?.openCreator(userId);
}
function onReelCreated(reel) {
  myReelsCount.value += 1;
  reels.value.unshift({ ...reel, is_mine: true, handle: 'Your Reel' });
}
function onReelDeleted(id) {
  reels.value = reels.value.filter((r) => r.id !== id);
  myReelsCount.value = Math.max(0, myReelsCount.value - 1);
}

// The comments window and the reels viewer are the Vibes ones; U Vibe's own windows (menu, Boost,
// edit, report) are opened through the post card's events below.
provideVibeActions({
  openComments: (post, onCount) => commentsRef.value?.open(post, onCount),
  openTag: (post) => post?.tag && tagModalRef.value?.open(post.tag),
  openReels: (list, index) => reelsRef.value?.open(list, index),
  openCreatorReels,
  openProfile: (userId) => userId && router.visit(route('user.vibes.user-profile', { user: userId })),
});

// Campus bar state comes from the server (same data the mobile API returns).
const campus = ref(props.campusState);
watch(() => props.campusState, (v) => { campus.value = v; });
// Feed opens on your main university (as in the design); otherwise all campuses.
const activeCampusId = ref(props.campusState?.main?.id ?? 'all');
const visitedUni = ref(null); // a campus opened from the Explorer that isn't yours or followed
const followBusy = ref(false);
const activeCategory = ref('all');
const posts = ref(props.uvibes || []);
watch(() => props.uvibes, (v) => { posts.value = v || []; });

const setCategory = (c) => activeCategory.value = c;

const composeModalRef = ref(null);
const keepVibesCleanModalRef = ref(null);
const myUniversitiesModalRef = ref(null);
const campusExplorerModalRef = ref(null);
// Post windows
const postMenuRef = ref(null);
const postEditRef = ref(null);
const postReportRef = ref(null);
const postBoostRef = ref(null);
const applicantsRef = ref(null);
const tagModalRef = ref(null);

const openMyUniversities = () => {
  myUniversitiesModalRef.value?.open();
};

const openCampusExplorer = (mode = 'browse') => {
  campusExplorerModalRef.value?.open(typeof mode === 'string' ? mode : 'browse');
};

/* ---- campus bar ---- */
const otherMine = computed(() => (campus.value.my_universities || []).filter(u => !u.is_main));
const knownUni = (id) =>
  (campus.value.my_universities || []).find(u => u.id === id) ||
  (campus.value.following || []).find(u => u.id === id) || null;
const activeUni = computed(() => activeCampusId.value === 'all' ? null : (knownUni(activeCampusId.value) || visitedUni.value));
// A chip for a campus you're only visiting (not yours, not followed).
const visiting = computed(() => activeUni.value && !knownUni(activeUni.value.id) ? activeUni.value : null);

const goCampus = (u) => {
  if (!u) { activeCampusId.value = 'all'; visitedUni.value = null; return; }
  activeCampusId.value = u.id;
  visitedUni.value = knownUni(u.id) ? null : u;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const onCampusChanged = (state) => {
  if (!state) return;
  campus.value = state;
  if (visitedUni.value && knownUni(visitedUni.value.id)) visitedUni.value = null;
  // Viewing a campus you just removed: fall back to your main one.
  if (activeCampusId.value !== 'all' && !activeUni.value) {
    activeCampusId.value = state.main?.id ?? 'all';
  }
  // First university added: open its feed.
  if (activeCampusId.value === 'all' && state.main && (state.my_universities || []).length === 1) {
    activeCampusId.value = state.main.id;
  }
};

const toggleFollowActive = async () => {
  const u = activeUni.value;
  if (!u || u.is_mine || followBusy.value) return;
  followBusy.value = true;
  try {
    const wasFollowing = !!u.is_following;
    const res = wasFollowing
      ? await axios.delete(route('user.universities.unfollow', u.id))
      : await axios.post(route('user.universities.follow', u.id));
    campus.value = res.data.state;
    // After unfollowing you're still looking at that campus, now as a visitor.
    visitedUni.value = wasFollowing ? { ...u, is_following: false } : null;
    showToast(res.data.message);
  } catch (e) {
    showToast(e?.response?.data?.message || 'Could not update');
  } finally {
    followBusy.value = false;
  }
};

// "Posting to" default: the campus you're looking at if it's yours, else your main one.
const composeUniversityId = computed(() => (activeUni.value?.is_mine ? activeUni.value.id : campus.value.main?.id) || null);

onMounted(() => {
  if (keepVibesCleanModalRef.value) {
    keepVibesCleanModalRef.value.openIfDue();
  }

  // A shared link (?post=ID) opens the feed on that post.
  focusLinkedPost({
    posts, showEverything: () => { activeCampusId.value = 'all'; visitedUni.value = null; activeCategory.value = 'all'; },
    notFound: () => showToast('That post is no longer available'),
  });
});

const openCompose = () => {
  // You can post only to your own universities (enforced on the server too).
  if (!(campus.value.my_universities || []).length) {
    showToast('Add your university to your profile first');
    openMyUniversities();
    return;
  }
  composeModalRef.value?.open();
};

const showToast = (msg) => {
  if (window.toast) window.toast(msg);
};

/* ---- post windows + keeping the list in step with what happened ---- */
const openPostMenu = (p) => postMenuRef.value?.open(p);
const openEdit = (p) => postEditRef.value?.open(p);
const openReport = (p) => postReportRef.value?.open(p);
const openBoost = (p) => postBoostRef.value?.open(p);
const openApplicants = (p) => applicantsRef.value?.open(p);
const openTag = (p) => tagModalRef.value?.open(p.tag);

const replacePost = (np) => { posts.value = posts.value.map(x => (x.id === np.id ? np : x)); };
const removePost = (id) => { posts.value = posts.value.filter(x => x.id !== id); };
const onBoosted = ({ post_id, boost_coins }) => {
  posts.value = posts.value.map(x => (x.id === post_id ? { ...x, boost_coins } : x));
};
// A repost is a new post on your campus; the original shows its new count.
const onReposted = ({ post: copy, original }) => {
  posts.value = [copy, ...posts.value.map(x => (x.id === original.id ? { ...x, reposts: original.reposts, reposted: true } : x))];
};

// The create endpoint returns a Vibes-format post, not the U Vibe card format,
// so reload the U Vibe list from the server instead of inserting it by hand.
const onPostCreated = () => {
  router.reload({ only: ['uvibes', 'stats', 'campusState'] }); // campusState: post counts per campus
};

const exploreCount = computed(() => campusExplorerModalRef.value?.campusCount || campus.value.total_campuses || 0);

/* ---- post card helpers ---- */
// The campus pill opens that campus's feed.
const campusOf = (p) => {
  if (!p.university_id) return null;
  return knownUni(p.university_id) || {
    id: p.university_id, name: p.campus, full_name: p.campus, flag: p.campus_flag || '🎓',
    is_mine: false, is_following: false,
  };
};

const filteredPosts = computed(() => {
  let filtered = posts.value;
  if (activeCampusId.value !== 'all') {
    filtered = filtered.filter(p => p.university_id === activeCampusId.value);
  }
  if (activeCategory.value !== 'all') {
    filtered = filtered.filter(p => p.category === activeCategory.value);
  }
  return filtered;
});

</script>

<style scoped>
/* Buttons show the pointer cursor, as in the design. */
.uv-page button:not(:disabled) { cursor: pointer; }

/* The post a shared link points at. */
.uv-flash { animation: uv-flash 2.2s ease-out; }
@keyframes uv-flash { 0%, 60% { box-shadow: 0 0 0 4px #a855f7; } 100% { box-shadow: 0 0 0 0 transparent; } }

/* Campus bar */
.cp-bar { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; }
.cp-chip {
  flex: none; border-radius: 999px; padding: 7px 13px; font-size: 12px; font-weight: 900; white-space: nowrap;
  background: rgba(255, 255, 255, .18); color: #fff;
}
.cp-chip.on { background: #fff; color: #4c1d95; }
.cp-chip.dashed { border: 1.5px dashed rgba(255, 255, 255, .75); }
.cp-chip.outlined { border: 1.5px solid rgba(255, 255, 255, .6); }

/* Strip under the bar when viewing another campus */
.cp-strip {
  display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 6px;
  border-radius: 14px; background: rgba(255, 255, 255, .14); padding: 8px 12px; color: #fff; font-size: 12px; font-weight: 700;
}
.cp-strip-text { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cp-strip-btn {
  flex: none; border-radius: 999px; padding: 6px 12px; font-size: 12px; font-weight: 900;
  background: rgba(255, 255, 255, .2); color: #fff;
}
.cp-strip-btn.light { background: #fff; color: #4c1d95; }
.cp-strip-btn:disabled { opacity: .6; }
</style>
