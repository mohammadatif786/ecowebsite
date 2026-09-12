<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans">
    <!-- ===================== SIDEBAR ===================== -->
    <aside id="sidebar" :class="['fixed top-0 left-0 h-full w-[260px] bg-white border-r border-slate-200 z-40 flex flex-col transition-transform', { 'open': isSidebarOpen }]">
      <BrandedLogo custom-class="px-5 h-[68px] border-b border-slate-100" />

      <nav id="sideNav" class="flex-1 overflow-y-auto hide-scroll p-3 space-y-1">
        <Link v-for="item in NAV" :key="item.id"
           :href="route('new_frontend.' + item.id)"
           :class="['navitem flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 hover:text-lkink transition', { 'active': currentRoute === item.id }]">
          <span class="ni-ico w-8 h-8 rounded-lg grid place-items-center shrink-0 transition" :style="getIconStyle(item.id)">
            <i :data-lucide="item.icon" class="w-4 h-4"></i>
          </span>
          <span class="ni-label text-[15px]">{{ item.label }}</span>
        </Link>
      </nav>

      <div class="side-full p-4 border-t border-slate-100">
        <div class="rounded-2xl brandgrad text-white p-4">
          <p class="text-xs font-bold opacity-90">Business on LinkUp</p>
          <p class="text-sm font-black leading-tight mt-1">Sell, host &amp; go live</p>
          <button @click="openCreateModal" class="btn mt-3 bg-white/95  text-[var(--lk-blue2)] text-xs px-3 py-2 w-full">Create store / product →</button>
        </div>
      </div>
    </aside>

    <!-- ===================== MAIN ===================== -->
    <div id="main" class="ml-[260px] min-h-screen flex flex-col transition-all">
      <header class="glass sticky top-0 z-30 border-b border-slate-200/70 h-[68px] flex items-center gap-3 px-4 md:px-6">
        <button id="menuBtn" @click="toggleSidebar" class="hidden w-10 h-10 rounded-xl btn-ghost items-center justify-center">
          <i data-lucide="menu" class="w-5 h-5"></i>
        </button>

        <button class="flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-lkink shrink-0">
          <i data-lucide="map-pin" class="w-4 h-4 text-lkblue"></i>
          <span id="topLoc">{{ user.city }}, {{ user.country }}</span>
          <span>{{ user.flag }}</span>
        </button>

        <div class="flex-1 max-w-xl relative">
          <i data-lucide="search" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
          <input id="globalSearch" placeholder="Search people, events, food, products…" class="w-full bg-slate-100/80 focus:bg-white border border-transparent focus:border-lkblue/40 rounded-full pl-11 pr-4 py-2.5 text-sm font-medium outline-none transition" @keydown.enter="doSearch"/>
        </div>

        <div class="flex items-center gap-2 ml-auto shrink-0">
          <button class="hidden sm:flex items-center gap-1.5 bg-amber-50 text-amber-700 rounded-full px-3 py-2 text-sm font-black border border-amber-200">
            <i data-lucide="gem" class="w-4 h-4"></i>
            <span id="topCoins">{{ num(wallet.coins) }}</span>
          </button>
          <Link :href="route('new_frontend.notifications')" class="relative w-10 h-10 rounded-xl btn-ghost flex items-center justify-center">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span v-if="unreadNotifCount > 0" id="topNotifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-black rounded-full w-4 h-4 grid place-items-center">
              {{ unreadNotifCount > 9 ? '9+' : unreadNotifCount }}
            </span>
          </Link>
          <button class="w-10 h-10 rounded-full bg-slate-200 overflow-hidden ring-2 ring-lkblue/30">
            <img id="topAvatar" :src="user.avatar" @error="$event.target.src = '/images/default-avatar.png'" alt="me" class="w-full h-full object-cover"/>
          </button>
        </div>
      </header>

      <main id="view" class="flex-1 p-4 md:p-6 max-w-[1500px] w-full mx-auto">
        <slot />
      </main>

      <footer class="text-center text-xs text-slate-400 font-semibold py-6">
        LinkUp Vibes · Web — Caribbean &amp; LatAm Super-App · shares the same <code class="text-slate-500">lk_*</code> data model as the app
      </footer>
    </div>

    <!-- Global Full-Screen Overlays -->
    <LiveOverlay ref="liveOverlayRef" :user="user" />
    <SearchOverlay ref="searchOverlayRef" />
    <LiveEndModal ref="liveEndModalRef" />

    <!-- Reusable Globals -->
    <Toast ref="toastRef" />
    <Modal ref="modalRef">
      <!-- Modal Content goes here -->
      <div v-if="modalContent" v-html="modalContent"></div>
    </Modal>

    <GlobalCreateModal
        :is-open="isCreateModalOpen"
        @close="isCreateModalOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUpdated, nextTick, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { NAV } from '../../components/new_frontend/MockDataStore';
import Toast from '../../components/new_frontend/ui/Toast.vue';
import Modal from '../../components/new_frontend/ui/Modal.vue';
import BrandedLogo from '../../components/BrandedLogo.vue';
import LiveOverlay from '../../components/new_frontend/live/LiveOverlay.vue';
import SearchOverlay from '../../components/new_frontend/SearchOverlay.vue';
import LiveEndModal from '../../components/new_frontend/modals/LiveEndModal.vue';
import GlobalCreateModal from '../../components/new_frontend/modals/GlobalCreateModal.vue';
import './new_front.css';

const page = usePage();
const currentRoute = computed(() => {
  // Use URL path to find active tab, default to home
  const url = page.url;
  const parts = url.split('?')[0].split('/').filter(Boolean);
  return parts[parts.length - 1] || 'home';
});

const NAV_COLORS = {
  home: '#2563eb', vibes: '#a855f7', uvibe: '#7c3aed', eats: '#22c55e',
  live: '#e11d48', dating: '#ef4444', events: '#f97316', nightlife: '#4f46e5',
  news: '#0ea5e9', marketplace: '#8b5cf6', wallet: '#2f9bef', profile: '#64748b'
};

const getIconStyle = (id) => {
  const c = NAV_COLORS[id] || '#2563eb';
  return { background: `${c}1a`, color: c };
};

const authUser = computed(() => page.props.auth?.user ?? null);
const user = computed(() => ({
  name: authUser.value?.name ?? 'Guest',
  avatar: authUser.value?.avatar || '/images/default-avatar.png',
  city: authUser.value?.new_city ?? authUser.value?.city ?? '',
  country: authUser.value?.new_country ?? authUser.value?.country ?? '',
  flag: page.props.userCountryFlag ?? '',
}));
const wallet = computed(() => ({
  balance: page.props.walletBalance ?? 0,
  coins: authUser.value?.coins ?? 0,
}));
const unreadNotifCount = computed(() => page.props.notifications?.unreadCount ?? 0);
const isSidebarOpen = ref(false);

const toastRef = ref(null);
const modalRef = ref(null);
const liveOverlayRef = ref(null);
const searchOverlayRef = ref(null);
const liveEndModalRef = ref(null);
const modalContent = ref(null);
const isCreateModalOpen = ref(false);

const num = (n) => Number(n).toLocaleString();

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

const doSearch = (e) => {
  const val = e.target.value;
  if(toastRef.value && val) {
    toastRef.value.showToast(`Searching for: ${val}`);
  }
};

const openCreateModal = () => {
    // Reload the data for the modal using Inertia's lazy loading
    router.reload({
        only: ['createModalData'],
        onSuccess: () => {
            isCreateModalOpen.value = true;
        }
    });
};

const initIcons = () => {
  if (window.lucide) {
    window.lucide.createIcons();
  } else {
    let script = document.createElement('script');
    script.src = 'https://unpkg.com/lucide@latest';
    script.onload = () => { window.lucide.createIcons(); };
    document.head.appendChild(script);
  }
};

onMounted(() => {
  initIcons();
  window.toast = (msg) => {
    if (toastRef.value) toastRef.value.showToast(msg);
  };
  window.openLiveOverlay = (session) => {
    if (liveOverlayRef.value) liveOverlayRef.value.open(session);
  };
  window.showLiveSummary = (stats) => {
    if (liveEndModalRef.value) liveEndModalRef.value.open(stats);
  };
  window.openSearch = () => {
    if (searchOverlayRef.value) searchOverlayRef.value.open();
  };
});

onUpdated(() => {
  nextTick(() => {
    initIcons();
  });
});
</script>
