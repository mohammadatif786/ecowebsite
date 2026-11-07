<template>
  <!--
    Vibes. The page only arranges the parts (components/User/vibes/*) and keeps one copy of each window
    the posts open — comments, Big Up, options, tagged item, Send a Vibe, reels — handed to the posts
    through provideVibeActions().
  -->
  <div class="fade">
    <VibesHeader @messages="openMessages" @reels="openAllReels" @thanks="openThanks" @post="openCompose" />

    <div class="grid lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-4 min-w-0">
        <ReelStoriesBar :stories="props.stories" :my-reels-count="myReelsCount" @add="newReelRef?.open()" @mine="openCreatorReels(user.id)" @open="(s) => openCreatorReels(s.user_id)" />
        <VibeComposerBar @open="openCompose" />
        <VibeFeed :posts="posts" :active-tag="props.activeTag" @reposted="(v) => posts.unshift(v)" />
      </div>

      <aside class="space-y-4 min-w-0">
        <VibeEarningsCard :total="Number(props.earningsStats?.total_commission || 0)" @open="affiliateRef?.open('earnings')" />
        <VibeTrendingTags :items="props.trendingTags" :active="props.activeTag" />
        <VibeSuggestedCreators :creators="creators" />
      </aside>
    </div>

    <!-- One of each window, shared by every post -->
    <CommentsModal ref="commentsRef" />
    <VibeBigUpModal ref="bigUpRef" :p="bigUpFor || {}" @sent="onBigUpSent" />
    <VibeOptionsModal ref="optionsRef" :vibe="optionsFor || {}" :is-owner="isOwner(optionsFor)" @delete="deletePost" @edit="(v) => composeRef?.openEdit(v)" @hidden="removePost" />
    <VibeTagModal ref="tagRef" />
    <SendVibeModal ref="sendVibeRef" :recipient-user-id="Number(sendVibeTo || 0)" />
    <ReelsViewer ref="reelsRef" @deleted="onReelDeleted" />

    <ComposeVibeModal ref="composeRef" :publishers="props.vibePublishers" :affiliate-items="props.affiliateItems" @post-created="(p) => posts.unshift(p)" @post-updated="onPostUpdated" />
    <AffiliateHubModal ref="affiliateRef" :items="props.affiliateItems" :stats="props.earningsStats" />
    <NewReelModal ref="newReelRef" @reel-created="onReelCreated" />
    <VibesMessagesModal ref="messagesRef" @select-user="(u) => chatRef?.open(u)" />
    <VibesChatModal ref="chatRef" @back="openMessages" />
    <SayThankYouModal ref="thanksRef" :gifts-received="props.gifts_received" />
    <KeepVibesCleanModal ref="cleanRef" />
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import MainLayout from '../../../layouts/User/MainLayout.vue';
import { provideVibeActions, toast } from '../../../components/User/vibes/useVibeActions';
import VibesHeader from '../../../components/User/vibes/VibesHeader.vue';
import ReelStoriesBar from '../../../components/User/vibes/stories/ReelStoriesBar.vue';
import VibeComposerBar from '../../../components/User/vibes/compose/VibeComposerBar.vue';
import VibeFeed from '../../../components/User/vibes/feed/VibeFeed.vue';
import VibeEarningsCard from '../../../components/User/vibes/sidebar/VibeEarningsCard.vue';
import VibeTrendingTags from '../../../components/User/vibes/sidebar/VibeTrendingTags.vue';
import VibeSuggestedCreators from '../../../components/User/vibes/sidebar/VibeSuggestedCreators.vue';
import CommentsModal from '../../../components/User/vibes/comments/CommentsModal.vue';
import ReelsViewer from '../../../components/User/vibes/reels/ReelsViewer.vue';
import VibeBigUpModal from '../../../components/User/modals/VibeBigUpModal.vue';
import VibeOptionsModal from '../../../components/User/modals/VibeOptionsModal.vue';
import VibeTagModal from '../../../components/User/modals/VibeTagModal.vue';
import SendVibeModal from '../../../components/User/modals/SendVibeModal.vue';
import ComposeVibeModal from '../../../components/User/modals/ComposeVibeModal.vue';
import AffiliateHubModal from '../../../components/User/modals/AffiliateHubModal.vue';
import NewReelModal from '../../../components/User/modals/NewReelModal.vue';
import VibesMessagesModal from '../../../components/User/modals/VibesMessagesModal.vue';
import VibesChatModal from '../../../components/User/modals/VibesChatModal.vue';
import SayThankYouModal from '../../../components/User/modals/SayThankYouModal.vue';
import KeepVibesCleanModal from '../../../components/User/modals/KeepVibesCleanModal.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
  vibes: { type: Array, default: () => [] },
  stories: { type: Array, default: () => [] },
  myReelsCount: { type: Number, default: 0 },
  allReels: { type: Array, default: () => [] },
  activeTag: { type: String, default: null },
  trendingTags: { type: Array, default: () => [] },
  suggestedCreators: { type: Array, default: () => [] },
  vibePublishers: { type: Object, default: () => ({ organizations: [], groups: [], custom: [] }) },
  affiliateItems: { type: Array, default: () => [] },
  earningsStats: { type: Object, default: () => ({}) },
  gifts_received: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const posts = ref([...props.vibes]);
const reels = ref([...props.allReels]);
const creators = ref([...props.suggestedCreators]);
const myReelsCount = ref(props.myReelsCount);

watch(() => props.vibes, (v) => (posts.value = [...(v || [])]));
watch(() => props.allReels, (v) => (reels.value = [...(v || [])]));

const commentsRef = ref(null);
const bigUpRef = ref(null);
const optionsRef = ref(null);
const tagRef = ref(null);
const sendVibeRef = ref(null);
const reelsRef = ref(null);
const composeRef = ref(null);
const affiliateRef = ref(null);
const newReelRef = ref(null);
const messagesRef = ref(null);
const chatRef = ref(null);
const thanksRef = ref(null);
const cleanRef = ref(null);

const bigUpFor = ref(null);
let bigUpDone = null;
const optionsFor = ref(null);
const sendVibeTo = ref(null);

const isOwner = (p) => !!p && String(p.created_by) === String(user.value.id);

provideVibeActions({
  openComments: (post, onCount) => commentsRef.value?.open(post, onCount),
  openBigUp: (post, done) => { bigUpFor.value = post; bigUpDone = done; nextTick(() => bigUpRef.value?.open()); },
  openOptions: (post) => { optionsFor.value = post; nextTick(() => optionsRef.value?.open()); },
  openTag: (post) => post?.tag && tagRef.value?.open(post.tag),
  sendVibe: (userId) => { sendVibeTo.value = userId; nextTick(() => sendVibeRef.value?.open()); },
  openReels: (list, index) => reelsRef.value?.open(list, index),
  openCreatorReels,
  filterTag: (tag) => router.get(route('user.vibes'), tag ? { tag: String(tag).replace(/^#/, '') } : {}, { preserveScroll: true, preserveState: true, only: ['vibes', 'activeTag'] }),
  openProfile: (userId) => userId && router.visit(route('user.vibes.user-profile', { user: userId })),
});

function openCreatorReels(userId) { reelsRef.value?.openCreator(userId); }
function openAllReels() { reelsRef.value?.open(reels.value, 0); }
function openCompose() { composeRef.value?.open(); }
function openMessages() { messagesRef.value?.open(creators.value.filter((c) => c.is_following)); }

async function openThanks() {
  let gifts = props.gifts_received;
  try { gifts = (await axios.get(route('user.vibes.gifts-received'))).data?.gifts_received || gifts; } catch { /* use what the page has */ }
  thanksRef.value?.open(gifts);
}

function onBigUpSent(count) {
  if (bigUpDone) bigUpDone(count);
  else if (bigUpFor.value) bigUpFor.value.bigups_count = count;
}

function removePost(id) { posts.value = posts.value.filter((p) => p.id !== id); }

async function deletePost(id) {
  try {
    await axios.delete(route('user.vibes.destroy', { vibe: id }));
    removePost(id);
    toast('🗑️ Post deleted');
  } catch (e) {
    toast(e.response?.data?.message || 'Couldn’t delete this post');
  }
}

function onPostUpdated(updated) {
  const i = posts.value.findIndex((p) => String(p.id) === String(updated.id));
  if (i !== -1) posts.value[i] = { ...posts.value[i], ...updated };
}

function onReelCreated(reel) {
  myReelsCount.value += 1;
  reels.value.unshift({ ...reel, is_mine: true, handle: 'Your Reel' });
}

function onReelDeleted(id) {
  reels.value = reels.value.filter((r) => r.id !== id);
  myReelsCount.value = Math.max(0, myReelsCount.value - 1);
}

onMounted(() => {
  cleanRef.value?.openIfDue();
  // Shared links: /user/vibes?reel=12 opens that reel.
  const reelId = Number(new URLSearchParams(window.location.search).get('reel'));
  if (reelId) {
    const i = reels.value.findIndex((r) => r.id === reelId);
    if (i !== -1) reelsRef.value?.open(reels.value, i);
  }
  nextTick(() => window.lucide?.createIcons());
});
</script>
