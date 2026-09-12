<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import CreateEventModal from '@/pages/organizer/event/Create.vue';

const page = usePage();

const authUser = computed(() => {
  const props = page.props as any;
  return props.auth?.user || props.user || {};
});

const createEventModalRef = ref(null);

const showToast = (msg: string) => {
    if ((window as any).toast) (window as any).toast(msg);
};

const openCreateEventModal = () => {
    // Check for organizer KYC / type
    if (authUser.value.type === 'organizer' || authUser.value.type === 'admin') {
        if (createEventModalRef.value) {
            createEventModalRef.value.open();
        }
    } else {
        showToast('Please complete your Organizer KYC to create events.');
    }
};

const userName = computed(() => authUser.value.name || authUser.value.username || 'Organizer');
const userHandle = computed(() => authUser.value.username || (authUser.value.email?.split('@')[0]) || 'organizer');
const userAvatar = computed(() => authUser.value.avatar || authUser.value.profile_image || `https://i.pravatar.cc/150?u=${userHandle.value}`);

// Dynamic page title based on current URL
const currentPageTitle = computed(() => {
  const path = page.url;

  if (path.startsWith('/organizer/cookout')) return 'Cookouts Dashboard';
  if (path.startsWith('/organizer/wallness-spa')) return 'Wellness Dashboard';
  if (path === '/organizer' || path.startsWith('/organizer/dashboard')) return 'Organizer Dashboard';
  if (path.includes('/ticket')) return 'My Tickets';
  if (path.includes('/sponsors')) return 'Sponsors Management';
  if (path.includes('/coupons')) return 'Coupons Management';
  if (path.includes('/scanner/app-setting')) return 'Scanner App Setting';
  if (path.includes('/scanner')) return 'My Scanners';
  if (path.includes('/pos')) return 'Point of Sale';
  if (path.includes('/review')) return 'Events Review';
  if (path.includes('/payout/request')) return 'My Payout Request';
  if (path.includes('/payout/method')) return 'Payout Method';
  if (path.includes('/statistics') || path.includes('/report')) return 'My Reports';
  if (path.includes('/profile')) return 'Organizer Profile';
  if (path.includes('/event/create')) return 'Create a New Event';
  if (path.includes('/event')) return 'My Events';

  return 'Organizer Portal';
});
</script>

<template>
  <header class="h-[64px] shrink-0 bg-white border-b border-slate-200 flex items-center gap-3 px-6 justify-between">
    <div class="flex items-center gap-3">
      <Link :href="route('new_frontend.events')" class="rounded-xl btn btn-ghost hover:bg-slate-100 transition-colors px-3 py-2 flex items-center gap-2 text-sm font-bold text-slate-600">
        <ArrowLeft class="w-4 h-4" />
        Back to LinkUp
      </Link>
      <h2 class="font-black text-lg ml-1 bg-clip-text text-transparent" style="background-image:linear-gradient(120deg,#2f9bef,#6d5efc)">
        {{ currentPageTitle }}
      </h2>
    </div>

    <!-- Create Event Modal (absolute/fixed, moved outside the flex container to be safe) -->
    <CreateEventModal
        ref="createEventModalRef"
        :categories="($page.props.categories as any) || []"
        :caribbeans="($page.props.caribbeans as any) || []"
        :scanners="($page.props.scanners as any) || []"
        :all-events="($page.props.allEvents as any) || []"
        :appURL="($page.props.appURL as any) || ''"
        :as-modal="true"
    />

    <div class="flex items-center gap-2">
      <span class="btn btn-ghost rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700">Credit $0.00</span>
      <span class="btn btn-ghost rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700 flex items-center gap-1">🪙 0</span>
      <button @click="openCreateEventModal" class="rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-colors px-4 py-2 text-sm font-bold flex items-center gap-2">
        <Plus class="w-4 h-4" />
        Create Event
      </button>
      <!-- User Avatar -->
      <img :src="userAvatar" class="h-9 w-9 rounded-full object-cover" :alt="userName" />
    </div>
  </header>
</template>

<style>
  .btn{border-radius:14px;font-weight:800;transition:.15s;cursor:pointer}
  .btn-ghost{background:#fff;border:1px solid #e2e8f0;color:#0f172a}
</style>
