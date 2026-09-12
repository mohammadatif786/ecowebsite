<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/layouts/new_front_layout/MainLayout.vue';
import { DB } from '@/components/new_frontend/MockDataStore.js';
import SettingsModal from './SettingsModal.vue';
import GiftsTab from '@/components/ProfileTabComponents/GiftsTab.vue';

const props = defineProps({
  profileUser: { type: Object, default: () => ({}) },
  profileStats: { type: Object, default: () => ({}) },
  wallet: { type: Object, default: () => ({ balance: 0, coins: 0 }) },
  tickets: { type: Array, default: () => [] },
  marketplaceOrders: { type: Array, default: () => [] },
  matchesData: { type: Object, default: () => ({}) },
  gifts: { type: Object, default: () => ({ sent: [], received: [] }) },
  newsItems: { type: Array, default: () => [] },
  subscription: { type: Object, default: () => ({ active: null, history_count: 0 }) },
});

// ---- MAIN DATA & STATE ----
const USER = ref({
  name: 'Guest',
  handle: '@guest',
  avatar: '',
  city: '',
  state: '',
  country: '',
  flag: '',
  interests: [],
  ...props.profileUser,
});
const WALLET = ref({
  balance: Number(props.wallet?.balance ?? 0),
  coins: Number(props.wallet?.coins ?? 0),
});
const PROF_TABS = ['About', 'Tickets & Events', 'Marketplace', 'Eats', 'Vibes', 'News', 'Matches', 'Gifts', 'LinkUp Live', 'Subscription', 'LinkUp Coins', 'Bill Payment', 'Event Organizer', 'Wallet'];
const PROF_TAB_ICONS = {
  'About': ['contact', '#2563eb'], ['Tickets & Events']: ['party-popper', '#f97316'], Marketplace: ['shopping-bag', '#8b5cf6'],
  Eats: ['utensils', '#22c55e'], Vibes: ['sparkles', '#a855f7'], News: ['newspaper', '#0ea5e9'], Matches: ['heart', '#ef4444'],
  Gifts: ['gift', '#ec4899'], ['LinkUp Live']: ['radio', '#e11d48'], Subscription: ['crown', '#eab308'],
  ['LinkUp Coins']: ['coins', '#f59e0b'], ['Bill Payment']: ['receipt', '#059669'], ['Event Organizer']: ['briefcase', '#4f46e5'], Wallet: ['wallet', '#2f9bef']
};

const profTab = ref('About');
const profSub = ref('About');
const tabRail = ref(null);

const scrollProfileTabs = (direction) => {
  tabRail.value?.scrollBy({ left: direction * 280, behavior: 'smooth' });
};

const selectProfileTab = (tab) => {
  profTab.value = tab;
};

// Settings State
const showSettings = ref(false);

// Global Utilities
const num = (n) => typeof n === 'number' ? (n > 9999 ? (n / 1000).toFixed(1) + 'k' : n.toLocaleString()) : n;
const money = (n) => typeof n === 'number' ? '$' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : n;
const fallbackInterests = ['Caribbean Culture', 'Live Music', 'Carnival', 'Community Events'];
const valueOrFallback = (value, fallback = 'Not added') => value || fallback;
const profileLocation = () => [USER.value.city, USER.value.state, USER.value.country].filter(Boolean).join(', ') || 'Not added';
const toastMsg = ref('');
const showToast = ref(false);
const toast = (m) => { toastMsg.value = m; showToast.value = true; setTimeout(() => showToast.value = false, 2500); };

// Profile Edit Modal (Separate from Settings now if needed, but SettingsModal handles it too)
const showEditProfile = ref(false);

const updateLocalUser = (updatedData) => {
  USER.value = { ...USER.value, ...updatedData };
};

import { watch } from 'vue';
watch(() => props.profileUser, (next) => {
  USER.value = { ...USER.value, ...next };
}, { deep: true });
</script>

<template>
  <Head title="Profile" />
  <MainLayout>
    <div class="fade p-4 lg:p-6 max-w-[1400px] mx-auto">

      <!-- HEADER CARD -->
      <div class="card overflow-hidden">
        <div class="h-40 brandgrad relative">
          <button @click="showSettings = true" class="absolute top-3 right-3 h-10 w-10 rounded-full bg-white/20 text-white grid place-items-center">
            <i data-lucide="settings" class="w-5 h-5"></i>
          </button>
        </div>
        <div class="p-6">
          <img :src="USER.avatar" class="w-28 h-28 rounded-3xl ring-4 ring-white object-cover -mt-16 relative"/>
          <div class="flex flex-wrap items-end justify-between gap-4 mt-3">
            <div class="min-w-0 pb-1">
              <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-2xl font-black">{{ USER.name }}</h2>
                <i v-if="USER.verified" data-lucide="badge-check" class="w-6 h-6 text-lkblue"></i>
                <span class="text-slate-500 font-bold">{{ USER.handle }}</span>
              </div>
              <p class="text-slate-500 font-semibold mt-1 flex items-center gap-1">
                <i data-lucide="map-pin" class="w-4 h-4"></i>{{ [USER.city, USER.state, USER.country].filter(Boolean).join(', ') }} {{ USER.flag }}
              </p>
            </div>
            <div class="flex gap-2 pb-1">
              <button @click="showSettings = true" class="btn btn-primary px-5 py-2.5">Edit profile</button>
              <button @click="showSettings = true" class="btn btn-ghost px-5 py-2.5">Settings</button>
            </div>
          </div>
          <div class="flex gap-6 mt-4">
            <div><span class="text-xl font-black">{{ num(props.profileStats.vibes || 0) }}</span> <span class="text-slate-500 text-sm font-bold">Vibes</span></div>
            <div><span class="text-xl font-black">{{ num(props.profileStats.followers || 0) }}</span> <span class="text-slate-500 text-sm font-bold">Followers</span></div>
            <div><span class="text-xl font-black">{{ num(props.profileStats.following || 0) }}</span> <span class="text-slate-500 text-sm font-bold">Following</span></div>
          </div>
        </div>
      </div>

      <!-- HIGHLIGHT STATS -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
        <div class="card p-4">
          <p class="text-xs font-bold text-slate-400">Tickets</p>
          <p class="text-2xl font-black">{{ num(props.profileStats.tickets || 0) }}</p>
          <p class="text-[11px] text-slate-400">{{ num(props.profileStats.cancelled_tickets || 0) }} cancelled</p>
        </div>
        <div class="card p-4">
          <p class="text-xs font-bold text-slate-400">Marketplace</p>
          <p class="text-2xl font-black">{{ num(props.profileStats.marketplace || 0) }}</p>
          <p class="text-[11px] text-slate-400">items purchased</p>
        </div>
        <div class="card p-4">
          <p class="text-xs font-bold text-slate-400">Matches</p>
          <p class="text-2xl font-black">{{ num(props.profileStats.matches || 0) }}</p>
          <p class="text-[11px] text-slate-400">{{ num(props.profileStats.likes || 0) }} likes</p>
        </div>
        <div class="card p-4">
          <p class="text-xs font-bold text-slate-400">Gifts</p>
          <p class="text-2xl font-black text-amber-500">{{ num(props.profileStats.net_gift_coins || 0) }}</p>
          <p class="text-[11px] text-slate-400">net coins</p>
        </div>
      </div>

      <!-- TABS -->
      <div class="relative mt-2">
        <button type="button" @click="scrollProfileTabs(-1)" class="absolute left-0 top-1/2 z-10 h-9 w-9 -translate-y-1/2 rounded-full border border-slate-200 bg-white shadow-sm grid place-items-center text-slate-600">
          <i data-lucide="chevron-left" class="w-4 h-4"></i>
        </button>
        <div ref="tabRail" class="profile-tabs-scroll flex max-w-full gap-2 overflow-x-auto scroll-smooth py-4 px-11">
          <button v-for="t in PROF_TABS" :key="t" @click="selectProfileTab(t)" :class="['chip flex shrink-0 items-center gap-1.5 whitespace-nowrap', profTab === t ? 'on' : '']">
            <i :data-lucide="PROF_TAB_ICONS[t][0]" class="w-3.5 h-3.5" :style="{color: profTab === t ? '#fff' : PROF_TAB_ICONS[t][1]}"></i>{{ t }}
          </button>
        </div>
        <button type="button" @click="scrollProfileTabs(1)" class="absolute right-0 top-1/2 z-10 h-9 w-9 -translate-y-1/2 rounded-full border border-slate-200 bg-white shadow-sm grid place-items-center text-slate-600">
          <i data-lucide="chevron-right" class="w-4 h-4"></i>
        </button>
      </div>

      <!-- TAB CONTENT -->
      <div id="profBody" class="mt-2 min-h-[300px]">

        <div v-if="profTab === 'About'" class="fade">
          <div class="flex gap-5 border-b border-slate-100 mb-4">
            <button @click="profSub='About'" :class="['pb-2 px-1 text-sm font-black flex items-center gap-1', profSub === 'About' ? 'text-slate-900 border-b-2 border-lkblue' : 'text-slate-400']">
              <i data-lucide="contact" class="w-4 h-4 text-blue-600"></i>About
            </button>
            <button @click="profSub='Contact'" :class="['pb-2 px-1 text-sm font-black flex items-center gap-1', profSub === 'Contact' ? 'text-slate-900 border-b-2 border-lkblue' : 'text-slate-400']">
              <i data-lucide="phone" class="w-4 h-4 text-emerald-600"></i>Contact
            </button>
            <button @click="profSub='Interests'" :class="['pb-2 px-1 text-sm font-black flex items-center gap-1', profSub === 'Interests' ? 'text-slate-900 border-b-2 border-lkblue' : 'text-slate-400']">
              <i data-lucide="heart" class="w-4 h-4 text-pink-500"></i>Interests
            </button>
          </div>

          <div v-if="profSub === 'About'" class="card p-4 fade">
            <p class="font-black flex items-center gap-2 mb-1"><i data-lucide="contact" class="w-4 h-4"></i>Profile Details</p>
            <p class="text-[11px] text-slate-400 mb-3">Personal information and background</p>
            <div class="grid sm:grid-cols-2 gap-2">
              <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3"><span class="h-9 w-9 rounded-lg grid place-items-center shrink-0 bg-blue-50 text-blue-600"><i data-lucide="user" class="w-4 h-4"></i></span><div><p class="text-[11px] font-black text-slate-400">Full Name</p><p class="font-bold text-sm">{{ USER.name }}</p></div></div>
              <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3"><span class="h-9 w-9 rounded-lg grid place-items-center shrink-0 bg-sky-50 text-sky-600"><i data-lucide="mail" class="w-4 h-4"></i></span><div><p class="text-[11px] font-black text-slate-400">Email</p><p class="font-bold text-sm">{{ valueOrFallback(USER.email) }}</p></div></div>
              <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3"><span class="h-9 w-9 rounded-lg grid place-items-center shrink-0 bg-pink-50 text-pink-600"><i data-lucide="cake" class="w-4 h-4"></i></span><div><p class="text-[11px] font-black text-slate-400">Birthday</p><p class="font-bold text-sm">{{ valueOrFallback(USER.birthday) }}</p></div></div>
              <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3"><span class="h-9 w-9 rounded-lg grid place-items-center shrink-0 bg-amber-50 text-amber-500"><i data-lucide="briefcase" class="w-4 h-4"></i></span><div><p class="text-[11px] font-black text-slate-400">Job</p><p class="font-bold text-sm">{{ valueOrFallback(USER.job) }}</p></div></div>
              <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3"><span class="h-9 w-9 rounded-lg grid place-items-center shrink-0 bg-emerald-50 text-emerald-600"><i data-lucide="badge-check" class="w-4 h-4"></i></span><div><p class="text-[11px] font-black text-slate-400">Status</p><p class="font-bold text-sm">{{ USER.verified ? 'Verified' : 'Active' }}</p></div></div>
              <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3"><span class="h-9 w-9 rounded-lg grid place-items-center shrink-0 bg-violet-50 text-violet-600"><i data-lucide="users" class="w-4 h-4"></i></span><div><p class="text-[11px] font-black text-slate-400">Gender</p><p class="font-bold text-sm">{{ valueOrFallback(USER.gender) }}</p></div></div>
            </div>
          </div>

          <div v-else-if="profSub === 'Contact'" class="grid sm:grid-cols-2 gap-2 fade">
             <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3"><span class="h-9 w-9 rounded-lg grid place-items-center shrink-0 bg-sky-50 text-sky-600"><i data-lucide="mail" class="w-4 h-4"></i></span><div><p class="text-[11px] font-black text-slate-400">Email</p><p class="font-bold text-sm">{{ valueOrFallback(USER.email) }}</p></div></div>
             <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3"><span class="h-9 w-9 rounded-lg grid place-items-center shrink-0 bg-green-50 text-green-600"><i data-lucide="phone" class="w-4 h-4"></i></span><div><p class="text-[11px] font-black text-slate-400">Phone</p><p class="font-bold text-sm">{{ valueOrFallback(USER.phone) }}</p></div></div>
             <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3"><span class="h-9 w-9 rounded-lg grid place-items-center shrink-0 bg-rose-50 text-rose-600"><i data-lucide="map-pin" class="w-4 h-4"></i></span><div><p class="text-[11px] font-black text-slate-400">Location</p><p class="font-bold text-sm">{{ profileLocation() }}</p></div></div>
             <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3"><span class="h-9 w-9 rounded-lg grid place-items-center shrink-0 bg-blue-50 text-blue-500"><i data-lucide="globe" class="w-4 h-4"></i></span><div><p class="text-[11px] font-black text-slate-400">Website</p><p class="font-bold text-sm">{{ valueOrFallback(USER.website) }}</p></div></div>
          </div>

          <div v-else-if="profSub === 'Interests'" class="card p-4 fade">
            <p class="font-black mb-2">Interests</p>
            <div class="flex flex-wrap gap-2">
              <span v-for="x in (USER.interests?.length ? USER.interests : fallbackInterests)" :key="x" class="rounded-full bg-blue-50 text-blue-700 px-3 py-1.5 text-sm font-bold">{{ x }}</span>
            </div>
          </div>
        </div>

        <div v-else-if="profTab === 'Tickets & Events'" class="fade">
          <div class="flex items-center justify-between mb-3">
            <div>
              <p class="font-black">Ticket Records</p>
              <p class="text-[11px] text-slate-400">Confirmed purchases, grouped by event</p>
            </div>
            <span class="rounded-full bg-blue-50 text-blue-600 px-2 py-0.5 text-xs font-black">{{ props.tickets.length }}</span>
          </div>
          <div v-if="props.tickets.length" class="grid gap-3">
            <div v-for="ticket in props.tickets" :key="ticket.id" class="card p-4 flex flex-wrap items-center justify-between gap-3">
              <div class="min-w-0">
                <p class="font-black truncate">{{ ticket.event }}</p>
                <p class="text-xs text-slate-400 font-bold">{{ ticket.ticket_name }} · {{ ticket.date }}</p>
              </div>
              <div class="text-right">
                <p class="font-black">{{ money(ticket.amount) }}</p>
                <span class="rounded-full bg-emerald-50 text-emerald-600 px-2 py-0.5 text-[11px] font-black">{{ ticket.status }}</span>
              </div>
            </div>
          </div>
          <div v-else class="card p-10 text-center text-slate-400 font-bold">No tickets found.</div>
        </div>

        <div v-else-if="profTab === 'Marketplace'" class="fade">
          <div class="flex items-center justify-between mb-3">
            <div>
              <p class="font-black">Marketplace Orders</p>
              <p class="text-[11px] text-slate-400">Recent purchases from the marketplace</p>
            </div>
            <span class="rounded-full bg-violet-50 text-violet-600 px-2 py-0.5 text-xs font-black">{{ props.marketplaceOrders.length }}</span>
          </div>
          <div v-if="props.marketplaceOrders.length" class="grid gap-3">
            <div v-for="order in props.marketplaceOrders" :key="order.id" class="card p-4 flex flex-wrap items-center justify-between gap-3">
              <div>
                <p class="font-black">{{ order.number }}</p>
                <p class="text-xs text-slate-400 font-bold">{{ order.items_count }} items · {{ order.date }}</p>
              </div>
              <div class="text-right">
                <p class="font-black">{{ money(order.total) }}</p>
                <span class="rounded-full bg-slate-100 text-slate-600 px-2 py-0.5 text-[11px] font-black">{{ order.status }}</span>
              </div>
            </div>
          </div>
          <div v-else class="card p-10 text-center text-slate-400 font-bold">No marketplace orders yet.</div>
        </div>

        <div v-else-if="profTab === 'News'" class="fade">
          <div class="flex items-center justify-between mb-3">
            <div>
              <p class="font-black">News Activity</p>
              <p class="text-[11px] text-slate-400">Latest news records from your profile feed</p>
            </div>
            <span class="rounded-full bg-sky-50 text-sky-600 px-2 py-0.5 text-xs font-black">{{ props.newsItems.length }}</span>
          </div>
          <div v-if="props.newsItems.length" class="grid md:grid-cols-2 gap-3">
            <div v-for="item in props.newsItems" :key="item.id" class="card p-4">
              <p class="font-black line-clamp-2">{{ item.title }}</p>
              <p class="text-xs text-slate-400 font-bold mt-1">{{ item.source }} · {{ item.date }}</p>
            </div>
          </div>
          <div v-else class="card p-10 text-center text-slate-400 font-bold">No news activity yet.</div>
        </div>

        <div v-else-if="profTab === 'Matches'" class="grid sm:grid-cols-3 gap-3 fade">
          <div class="card p-4"><p class="text-xs font-black text-slate-400">Likes Sent</p><p class="text-2xl font-black text-rose-500">{{ num(props.matchesData.likes_sent || 0) }}</p></div>
          <div class="card p-4"><p class="text-xs font-black text-slate-400">Likes Received</p><p class="text-2xl font-black text-pink-500">{{ num(props.matchesData.likes_received || 0) }}</p></div>
          <div class="card p-4"><p class="text-xs font-black text-slate-400">Mutual Matches</p><p class="text-2xl font-black text-emerald-500">{{ num(props.matchesData.mutual || 0) }}</p></div>
        </div>

        <div v-else-if="profTab === 'Gifts'" class="fade">
          <GiftsTab
            :gifts_sent="props.gifts.sent"
            :gifts_received="props.gifts.received"
            :all_gifts="[...props.gifts.sent, ...props.gifts.received]"
            :user_wallet_balance="WALLET.balance"
          />
        </div>

        <div v-else-if="profTab === 'Wallet'" class="fade">
          <div class="rounded-3xl bg-slate-900 text-white p-6 max-w-lg">
            <p class="text-white/60 text-xs font-black">LINK UP DIGITAL WALLET</p>
            <p class="text-4xl font-black mt-2">{{ money(WALLET.balance) }}</p>
            <Link href="/new_frontend/wallet" class="btn btn-primary mt-4 px-5 py-2.5 inline-block">Open Wallet →</Link>
          </div>
        </div>

        <div v-else-if="profTab === 'LinkUp Coins'" class="fade">
          <div class="rounded-3xl bg-gradient-to-r from-amber-400 to-amber-500 text-white p-6 max-w-lg">
            <p class="text-white/80 text-xs font-black">LINK UP COINS</p>
            <p class="text-4xl font-black mt-2">{{ num(WALLET.coins) }}</p>
            <Link href="/new_frontend/wallet" class="btn bg-white/90 text-amber-700 mt-4 px-5 py-2.5 inline-block">Buy More</Link>
          </div>
        </div>

        <div v-else-if="profTab === 'Subscription'" class="fade">
          <div v-if="props.subscription.active" class="card p-5 max-w-xl ring-2 ring-lkblue">
            <p class="text-xs font-black text-blue-600">CURRENT PLAN</p>
            <p class="font-black text-2xl mt-1">{{ props.subscription.active.name }}</p>
            <p class="text-sm text-slate-500 mt-1">Status: {{ props.subscription.active.status }} - Ends {{ props.subscription.active.ends_at || 'Not set' }}</p>
            <div class="grid grid-cols-2 gap-3 mt-4">
              <div class="rounded-xl bg-blue-50 p-3">
                <p class="text-[11px] font-black text-blue-500">Amount</p>
                <p class="font-black">{{ money(props.subscription.active.amount || 0) }}</p>
              </div>
              <div class="rounded-xl bg-emerald-50 p-3">
                <p class="text-[11px] font-black text-emerald-500">Days Left</p>
                <p class="font-black">{{ num(props.subscription.active.days_left || 0) }}</p>
              </div>
            </div>
          </div>
          <div v-else class="card p-10 text-center text-slate-400 font-bold">No active subscription.</div>
          <p class="text-xs text-slate-400 font-bold mt-3">Subscription history: {{ num(props.subscription.history_count || 0) }} records</p>
        </div>
        <div v-else-if="profTab === 'Bill Payment'" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 fade">
           <button v-for="b in ['Electricity','Water','Cable / Internet','Mobile Top-Up','School Fees','Insurance']" :key="b" @click="toast('🧾 ' + b)" class="card p-4 text-left font-black flex items-center gap-2"><i data-lucide="receipt" class="w-5 h-5 text-lkblue"></i>{{ b }}</button>
        </div>

        <!-- Fallback for other tabs -->
        <div v-else class="card p-10 text-center text-slate-400 font-bold fade">
          <p class="text-xl mb-2">{{ profTab }} Dashboard</p>
          <p class="text-sm">Summary data for {{ profTab }} will appear here.</p>
        </div>

      </div>
    </div>
  </MainLayout>

  <!-- ==================== EXTERNAL SETTINGS MODAL ==================== -->
  <SettingsModal
    v-model="showSettings"
    :profileUser="USER"
    @toast="toast"
    @profile-updated="updateLocalUser"
  />

  <!-- Global Toast -->
  <div v-if="showToast" class="fixed top-20 left-1/2 -translate-x-1/2 z-[100] fade-up">
    <div class="bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-2xl font-bold flex items-center gap-3">
      {{ toastMsg }}
    </div>
  </div>

</template>

<style scoped>
.profile-tabs-scroll {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 transparent;
  overscroll-behavior-x: contain;
  -webkit-overflow-scrolling: touch;
}

.profile-tabs-scroll::-webkit-scrollbar {
  height: 6px;
}

.profile-tabs-scroll::-webkit-scrollbar-track {
  background: transparent;
}

.profile-tabs-scroll::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 999px;
}
</style>
