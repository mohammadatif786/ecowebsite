<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
  PartyPopper,
  Utensils,
  Flower2,
  Calendar,
  Ticket,
  Award,
  Tag,
  QrCode,
  Settings,
  CreditCard,
  Receipt,
  Star,
  Send,
  Landmark,
  BarChart2,
  User,
  LogOut
} from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const isActive = (link: string) => {
  // Special handling for scanner pages to avoid both being active
  if (link === '/organizer/scanner' && page.url.includes('/scanner/app-setting')) {
    return false;
  }
  // POS Fee Summary is a child route, but it is a separate navigation item.
  if (link === '/organizer/pos' && page.url.startsWith('/organizer/pos/')) {
    return false;
  }
  if (link === '/organizer/scanner/app-setting' && !page.url.includes('/scanner/app-setting')) {
    return false;
  }
  return page.url === link || page.url.startsWith(link + '/');
};

const authUser = computed(() => {
  const props = page.props as any;
  return props.auth?.user || props.user || {};
});

const userName = computed(() => authUser.value.name || authUser.value.username || 'Organizer');
const userHandle = computed(() => authUser.value.username || (authUser.value.email?.split('@')[0]) || 'organizer');
const userAvatar = computed(() => authUser.value.avatar || authUser.value.profile_image || `https://i.pravatar.cc/150?u=${userHandle.value}`);

const navCounts = computed(() => {
  return authUser.value.nav_counts || { events: 0, cookouts: 0, wellness: 0 };
});

const organizerCategories = computed(() => {
  const profile = authUser.value.organizer_profile || authUser.value.organizerProfile;
  return profile?.categories || [];
});

// Define the nav structure directly matching the design
const navGroups = computed(() => {
  const groups = [
    {
      group: 'Home',
      items: [
        {
          id: 'home-events',
          label: 'Events',
          icon: PartyPopper,
          color: '#2563eb',
          grad: 'linear-gradient(135deg,#2f9bef,#2563eb)',
          badge: navCounts.value.events,
          link: '/organizer/dashboard',
          // Show if categories contains any event-type category (default logic: anything not cookout/wellness)
          visible: organizerCategories.value.some(cat => !['Cookouts/Food', 'Food', 'Wellness and Spa'].includes(cat))
        },
        {
          id: 'home-cookouts',
          label: 'Cookouts',
          icon: Utensils,
          color: '#f97316',
          grad: 'linear-gradient(135deg,#f59e0b,#f97316)',
          badge: navCounts.value.cookouts,
          link: '/organizer/cookout/dashboard',
          visible: organizerCategories.value.some(cat => ['Cookouts/Food', 'Food'].includes(cat))
        },
        {
          id: 'home-wellness',
          label: 'Wellness',
          icon: Flower2,
          color: '#059669',
          grad: 'linear-gradient(135deg,#10b981,#059669)',
          badge: navCounts.value.wellness,
          link: '/organizer/wallness-spa/dashboard',
          visible: organizerCategories.value.some(cat => cat === 'Wellness and Spa')
        }
      ]
    },
    {
      group: 'Events',
      items: [
        { id: 'my-events', label: 'My Events', icon: Calendar, color: '#2563eb', grad: 'linear-gradient(135deg,#2f9bef,#2563eb)', link: '/organizer/event/index' },
        { id: 'event-tickets', label: 'My Tickets', icon: Ticket, color: '#7c3aed', grad: 'linear-gradient(135deg,#2f9bef,#2563eb)', link: '/organizer/ticket' },
        { id: 'sponsor', label: 'Sponsor', icon: Award, color: '#f59e0b', grad: 'linear-gradient(135deg,#2f9bef,#2563eb)', link: '/organizer/event/sponsors/index' },
        { id: 'coupons', label: 'Coupons', icon: Tag, color: '#14b8a6', grad: 'linear-gradient(135deg,#2f9bef,#2563eb)', link: '/organizer/event/coupons/index' }
      ]
    },
    {
      group: 'Scanner App',
      items: [
        { id: 'scanner', label: 'My Scanners', icon: QrCode, color: '#0ea5e9', grad: 'linear-gradient(135deg,#2f9bef,#2563eb)', link: '/organizer/scanner' },
        { id: 'scanner-settings', label: 'Scanner App Setting', icon: Settings, color: '#2563eb', grad: 'linear-gradient(135deg,#2f9bef,#2563eb)', link: '/organizer/scanner/app-setting' }
      ]
    },
    {
      group: 'My Point Of Sale',
      items: [
        { id: 'pos', label: 'Point of Sale', icon: CreditCard, color: '#059669', grad: 'linear-gradient(135deg,#2f9bef,#2563eb)', link: '/organizer/pos' },
        { id: 'pos-fees', label: 'POS Fee Summary', icon: Receipt, color: '#059669', grad: 'linear-gradient(135deg,#2f9bef,#2563eb)', link: '/organizer/pos/fees' }
      ]
    },
    {
      group: 'Review',
      items: [
        { id: 'reviews', label: 'Events Review', icon: Star, color: '#eab308', grad: 'linear-gradient(135deg,#2f9bef,#2563eb)', link: '/organizer/review' }
      ]
    },
    {
      group: 'Payout',
      items: [
        { id: 'payout-request', label: 'My Payout Request', icon: Send, color: '#4f46e5', grad: 'linear-gradient(135deg,#2f9bef,#2563eb)', link: '/organizer/payout/request' },
        { id: 'payout-method', label: 'Payout Method', icon: Landmark, color: '#0891b2', grad: 'linear-gradient(135deg,#2f9bef,#2563eb)', link: '/organizer/payout/method' }
      ]
    },
    {
      group: 'Reports',
      items: [
        { id: 'reports', label: 'My Reports', icon: BarChart2, color: '#2563eb', grad: 'linear-gradient(135deg,#2f9bef,#2563eb)', link: '/organizer/statistics' }
      ]
    },
    {
      group: 'Account',
      items: [
        { id: 'profile', label: 'Organizer Profile', icon: User, color: '#4f46e5', grad: 'linear-gradient(135deg,#2f9bef,#2563eb)', link: '/organizer/profile/index' }
      ]
    }
  ];

  // Filter groups and items
  return groups.map(group => {
    return {
      ...group,
      items: group.items.filter(item => item.visible !== false)
    };
  }).filter(group => group.items.length > 0);
});
</script>

<template>
  <aside class="w-[250px] h-full bg-white border-r border-slate-200 flex flex-col shrink-0">
    <div class="p-5 text-center text-white" style="background:linear-gradient(120deg,#2f9bef,#2563eb 60%,#6d5efc)">
      <img :src="userAvatar" class="h-16 w-16 rounded-full object-cover mx-auto ring-4 ring-white/40" :alt="userName" />
      <p class="font-black mt-2">{{ userName }}</p>
      <p class="text-xs text-white/75">@{{ userHandle }} · Organizer</p>
    </div>

    <nav class="flex-1 overflow-y-auto hide-scroll p-3 space-y-3">
      <div v-for="group in navGroups" :key="group.group">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-wide px-4 mb-1">
          {{ group.group }}
        </p>
        <div class="space-y-0.5">
          <Link
            v-for="item in group.items"
            :key="item.id"
            :href="item.link"
            :class="[
              'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold text-sm transition',
              isActive(item.link) ? 'text-white shadow' : 'text-slate-600 hover:bg-slate-100'
            ]"
            :style="isActive(item.link) ? { background: item.grad } : {}"
          >
            <span
              class="h-7 w-7 rounded-lg grid place-items-center shrink-0"
              :style="{
                backgroundColor: isActive(item.link) ? 'rgba(255,255,255,.25)' : item.color + '1a',
                color: isActive(item.link) ? '#fff' : item.color
              }"
            >
              <component :is="item.icon" class="w-4 h-4" />
            </span>
            <span class="truncate flex-1 text-left">{{ item.label }}</span>
            <span
              v-if="'badge' in item && item.badge"
              class="text-[10px] font-black rounded-full px-2 py-0.5 shrink-0"
              :style="{
                backgroundColor: isActive(item.link) ? 'rgba(255,255,255,.25)' : item.color + '1a',
                color: isActive(item.link) ? '#fff' : item.color
              }"
            >
              {{ item.badge }}
            </span>
          </Link>
        </div>
      </div>
    </nav>

    <div class="p-3 border-t border-slate-100 shrink-0">
      <Link href="/logout" method="post" as="button" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl font-bold text-sm text-rose-600 hover:bg-rose-50 transition-colors">
        <LogOut class="w-4 h-4" />
        Logout / Back to LinkUp
      </Link>
    </div>
  </aside>
</template>
