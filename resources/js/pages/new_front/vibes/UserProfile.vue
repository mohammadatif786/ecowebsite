<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import MainLayout from '@/layouts/new_front_layout/MainLayout.vue';
import ReelViewModal from '@/components/new_frontend/modals/ReelViewModal.vue';
import VibesChatModal from '@/components/new_frontend/modals/VibesChatModal.vue';
import SendVibeModal from '@/components/new_frontend/modals/SendVibeModal.vue';
import AffiliateHubModal from '@/components/new_frontend/modals/AffiliateHubModal.vue';
import VibeTagModal from '@/components/new_frontend/modals/VibeTagModal.vue';
import { ArrowLeft, MapPin, MessageCircle, UserPlus, UserCheck, Check, Heart, TrendingUp, Gift } from 'lucide-vue-next';

defineOptions({ layout: MainLayout });

const props = defineProps({
  profileUser: { type: Object, default: () => ({}) },
  reels: { type: Array, default: () => [] },
  vibes: { type: Array, default: () => [] },
  shopItems: { type: Array, default: () => [] },
  topSenders: { type: Array, default: () => [] },
  followers: { type: Array, default: () => [] },
  earningsStats: { type: Object, default: () => ({ total_commission: 0 }) }
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user || {});
const user = ref({ ...props.profileUser });
const isFollowing = ref(Boolean(user.value.is_following));
const isOwner = computed(() => String(currentUser.value.id) === String(user.value.id));

const reelViewModalRef = ref(null);
const vibesChatModalRef = ref(null);
const sendVibeModalRef = ref(null);
const affiliateHubModalRef = ref(null);
const vibeTagModalRef = ref(null);
const currentReel = ref(null);

const openEarnings = () => {
  if (affiliateHubModalRef.value) {
    affiliateHubModalRef.value.open();
  }
};

const defaultTopSenders = [
  { name: 'Nadia', handle: '@nadia', coins: 1634, avatar: 'https://i.pravatar.cc/150?img=25' },
  { name: 'Aaliyah', handle: '@aaliyah', coins: 1451, avatar: 'https://i.pravatar.cc/150?img=35' },
  { name: 'Renee', handle: '@renee', coins: 1268, avatar: 'https://i.pravatar.cc/150?img=45' },
];

const goBack = () => {
  router.visit(route('new_frontend.vibes'));
};

const toggleFollow = async () => {
  try {
    const response = await axios.post(route('new_frontend.creators.toggle-follow', { user: user.value.id }));
    isFollowing.value = response.data.is_following;
    user.value.is_following = response.data.is_following;
    if (window.toast) {
      window.toast(isFollowing.value ? `Following @${user.value.linkup_id || user.value.name}` : `Unfollowed @${user.value.linkup_id || user.value.name}`);
    }
  } catch (e) {
    console.error('Failed to toggle follow', e);
  }
};

const openMessage = () => {
  if (vibesChatModalRef.value) {
    vibesChatModalRef.value.open({
      user_id: user.value.id,
      handle: user.value.linkup_id || user.value.name,
      avatar: user.value.avatar,
      name: user.value.name
    });
  }
};

const sendVibeModal = () => {
  if (sendVibeModalRef.value) {
    sendVibeModalRef.value.open();
  }
};

const thankSender = (senderName) => {
  if (window.toast) window.toast(`🙏 Thanked ${senderName} for the gift!`);
};

const shopProduct = (product) => {
  if (vibeTagModalRef.value) {
    vibeTagModalRef.value.open({
      kind: product.kind || 'product',
      id: product.id,
      title: product.name,
      price: product.price,
      image: product.image,
      seller: user.value.name,
      vibe_id: product.vibe_id || 1
    });
  }
};

const openReel = (reel) => {
  currentReel.value = reel;
  if (reelViewModalRef.value) {
    reelViewModalRef.value.open();
  }
};

const num = (n) => Number(n || 0).toLocaleString();
const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
</script>

<template>
  <Head :title="user.name + ' - Vibes Profile'" />
  <div class="fade max-w-3xl mx-auto p-4 sm:p-6 space-y-6">
    <!-- Back Button -->
    <button @click="goBack" class="flex items-center gap-1.5 text-slate-500 hover:text-slate-700 font-bold text-sm transition">
      <ArrowLeft class="w-4 h-4" /> Back
    </button>

    <!-- My Earnings Banner -->
    <div v-if="isOwner" @click="openEarnings"
        class="w-full rounded-2xl p-4 text-white text-left flex items-center justify-between cursor-pointer shadow-lg shadow-emerald-600/20 hover:scale-[1.01] transition"
        style="background:linear-gradient(135deg,#059669,#10b981)">
      <div>
        <p class="font-black text-sm flex items-center gap-1.5">
          <TrendingUp class="w-4 h-4" /> My Earnings
        </p>
        <p class="text-[11px] text-white/80 mt-0.5">Commission from tagged sales</p>
      </div>
      <p class="text-xl font-black">{{ money(props.earningsStats?.total_commission || 0) }}</p>
    </div>

    <!-- Profile Card Header -->
    <div class="card p-6 md:p-8 bg-white border border-slate-200 shadow-sm rounded-3xl">
      <div class="flex flex-col sm:flex-row gap-6 sm:gap-10 items-center sm:items-start">
        <img :src="user.avatar || ('https://i.pravatar.cc/150?u=' + user.id)"
             @error="$event.target.src = 'https://i.pravatar.cc/150?u=' + user.id"
             class="h-32 w-32 sm:h-36 sm:w-36 rounded-full object-cover shrink-0 shadow-lg bg-slate-100" />

        <div class="flex-1 min-w-0 w-full text-center sm:text-left">
          <div class="flex items-center justify-center sm:justify-start gap-2 flex-wrap">
            <h1 class="text-2xl font-black text-slate-900">{{ user.handle }}</h1>
            <Check v-if="user.verified" class="w-5 h-5 text-sky-500" />
          </div>
          <p class="text-slate-700 font-semibold mt-0.5">{{ user.name }}</p>

          <div class="flex items-center justify-center sm:justify-start gap-6 mt-4 flex-wrap text-sm font-semibold text-slate-700">
            <p><b class="text-base text-slate-900">{{ num(user.posts_count || (props.reels.length + props.vibes.length)) }}</b> posts</p>
            <p><b class="text-base text-slate-900">{{ num(user.followers_count || 581) }}</b> followers</p>
            <p><b class="text-base text-slate-900">{{ num(user.following_count || 825) }}</b> following</p>
          </div>

          <p class="text-sm text-slate-500 font-semibold mt-3 flex items-center justify-center sm:justify-start gap-1">
            <span>Traveler</span>
            <span class="text-slate-400">·</span>
            <MapPin class="w-3.5 h-3.5 text-slate-400" />
            <span>{{ [user.city || 'The Valley', user.country || 'Anguilla'].filter(Boolean).join(', ') }}</span>
          </p>

          <!-- Followed by database followers -->
          <div v-if="props.followers && props.followers.length > 0" class="flex items-center justify-center sm:justify-start gap-2 mt-2.5 text-xs text-slate-500 font-medium">
            <div class="flex -space-x-1.5 overflow-hidden">
              <img v-for="f in props.followers.slice(0, 3)" :key="f.id" class="inline-block h-5 w-5 rounded-full ring-2 ring-white object-cover bg-slate-200" :src="f.avatar || ('https://i.pravatar.cc/100?u=' + f.id)" @error="$event.target.src = 'https://i.pravatar.cc/100?u=' + f.id" alt="" />
            </div>
            <span>
              Followed by
              <template v-for="(f, idx) in props.followers.slice(0, 2)" :key="f.id">
                <b>{{ f.name || f.handle }}</b><span v-if="idx === 0 && props.followers.length > 1">, </span>
              </template>
              <span v-if="props.followers.length > 2"> and {{ user.followers_count ? user.followers_count - 2 : props.followers.length - 2 }} others</span>
            </span>
          </div>

          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mt-5">
            <button v-if="!isOwner" @click="toggleFollow"
                :class="['px-6 py-2.5 rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-sm', isFollowing ? 'bg-slate-100 text-slate-800 hover:bg-slate-200 border border-slate-200' : 'btn-primary']">
              <UserCheck v-if="isFollowing" class="w-4 h-4" />
              <UserPlus v-else class="w-4 h-4" />
              {{ isFollowing ? 'Following' : 'Follow' }}
            </button>
            <button v-if="!isOwner" @click="openMessage" class="rounded-xl px-5 py-2.5 text-xs font-black border-2 border-slate-200 hover:bg-slate-50 text-slate-800 flex items-center justify-center gap-1.5 transition">
              <MessageCircle class="w-4 h-4" /> Message
            </button>
          </div>
        </div>
      </div>

      <!-- Send a Vibe Banner Button -->
      <button v-if="!isOwner" @click="sendVibeModal" class="w-full mt-6 rounded-2xl py-3.5 font-black text-white flex items-center justify-center gap-2 shadow-lg shadow-pink-500/20 hover:opacity-95 transition" style="background:linear-gradient(135deg,#f59e0b,#ec4899)">
        🌹 Send a Vibe
      </button>
    </div>

    <!-- Shop Section -->
    <div v-if="props.shopItems.length > 0" class="card p-5 bg-white border border-slate-200 shadow-sm rounded-3xl space-y-3">
      <div class="flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-wider">
        <i data-lucide="shopping-bag" class="w-4 h-4 text-lkblue"></i> SHOP {{ user.handle.toUpperCase() }}
      </div>
      <div class="space-y-2">
        <div v-for="product in props.shopItems" :key="product.id"
            class="flex items-center justify-between p-3 rounded-2xl border border-slate-100 hover:border-slate-200 transition bg-slate-50/50">
          <div class="flex items-center gap-3 min-w-0">
            <img :src="product.image || 'https://picsum.photos/200'" class="w-14 h-14 rounded-xl object-cover border border-slate-200 shrink-0" />
            <div class="min-w-0">
              <p class="font-black text-sm text-slate-900 truncate">{{ product.name }}</p>
              <p class="text-xs font-bold text-slate-500 mt-0.5">{{ money(product.price) }}</p>
            </div>
          </div>
          <button @click="shopProduct(product)" class="btn btn-primary px-4 py-2 text-xs font-black shrink-0">
            Shop
          </button>
        </div>
      </div>
    </div>

    <!-- Top Vibes Senders -->
    <div class="card p-5 bg-white border border-slate-200 shadow-sm rounded-3xl space-y-3">
      <div class="flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-wider">
        👑 TOP VIBES SENDERS
      </div>
      <div class="space-y-2">
        <div v-for="(sender, index) in (props.topSenders.length ? props.topSenders : defaultTopSenders)" :key="index"
            class="flex items-center justify-between p-3 rounded-2xl border border-slate-100 bg-slate-50/50">
          <div class="flex items-center gap-3">
            <span class="text-lg font-black w-6 text-center">
              {{ index === 0 ? '🥇' : (index === 1 ? '🥈' : '🥉') }}
            </span>
            <img :src="sender.avatar || ('https://i.pravatar.cc/150?u=' + (index + 50))" class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-xs" />
            <div>
              <p class="font-black text-sm text-slate-900">{{ sender.name }}</p>
              <p class="text-[11px] font-bold text-slate-400">{{ sender.handle }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-xs font-extrabold text-amber-600 bg-amber-50 px-3 py-1.5 rounded-full border border-amber-200 flex items-center gap-1">
              🪙 {{ num(sender.coins) }}
            </span>
            <button @click="thankSender(sender.name)" class="btn btn-ghost text-xs px-3 py-1.5 flex items-center gap-1 font-black">
              🙏 Thank
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- User's Reels & Posts Grid -->
    <div class="space-y-4">
      <h3 class="text-lg font-black text-slate-900">Reels & Posts</h3>

      <div v-if="props.reels.length === 0 && props.vibes.length === 0" class="card py-16 text-center text-slate-400 font-bold text-sm">
        No posts or reels published yet
      </div>

      <div v-else class="grid grid-cols-3 gap-3">
        <div v-for="reel in props.reels" :key="reel.id"
            @click="openReel(reel)"
            class="relative aspect-[9/16] rounded-2xl overflow-hidden cursor-pointer group bg-black border border-slate-200 shadow-sm">
          <video v-if="reel.type === 'video'" :src="reel.file_path" class="w-full h-full object-cover" muted />
          <img v-else :src="reel.file_path" class="w-full h-full object-cover" />
          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-end p-3">
            <div class="flex items-center gap-2 text-white text-xs font-bold">
              <Heart class="w-3.5 h-3.5 fill-rose-500 text-rose-500" /> {{ reel.likes_count || 0 }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <ReelViewModal ref="reelViewModalRef" :reel="currentReel" :reels="props.reels" />
    <VibesChatModal ref="vibesChatModalRef" />
    <SendVibeModal ref="sendVibeModalRef" :recipientUserId="user.id" :recipientHandle="user.handle || user.name" />
    <AffiliateHubModal ref="affiliateHubModalRef" :items="props.shopItems" :stats="props.earningsStats" />
    <VibeTagModal ref="vibeTagModalRef" />
  </div>
</template>
