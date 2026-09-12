<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import * as LucideIcons from 'lucide-vue-next';
import AppHeader from './AppHeader.vue';
import AppSidebar from './AppSidebar.vue';

const page = usePage();

// Check if the current page should hide the layout header
const showLayoutHeader = computed(() => {
  return !pageMeta.value.hide;
});

const pageMeta = computed(() => {
  const path = page.url;

  if (path.startsWith('/organizer/cookout')) {
    return { title: 'Cookouts Dashboard', subtitle: 'Manage your food and cookout events', icon: 'Utensils', grad: 'linear-gradient(135deg,#f59e0b,#f97316)' };
  } else if (path.startsWith('/organizer/wallness-spa')) {
    return { title: 'Wellness Dashboard', subtitle: 'Manage your wellness and spa events', icon: 'Flower2', grad: 'linear-gradient(135deg,#10b981,#059669)' };
  } else if (path === '/organizer' || path.startsWith('/organizer/dashboard')) {
    return { title: 'Organizer Dashboard', subtitle: 'Revenue, drink mix, tables, bottles, check-ins, payouts', icon: 'PartyPopper', grad: 'linear-gradient(90deg,#0ea5e9,#2563eb)' };
  } else if (path.includes('/ticket')) {
    return { title: 'My Tickets', subtitle: 'Manage ticket types and sales', icon: 'Ticket', grad: 'linear-gradient(120deg,#2f9bef,#2563eb 60%,#6d5efc)' };
  } else if (path.includes('/sponsors')) {
    return { title: 'Sponsors Management', subtitle: 'Manage the brands and partners backing your events', icon: 'Award', grad: 'linear-gradient(120deg,#f59e0b,#eab308)' };
  } else if (path.includes('/coupons')) {
    return { title: 'Coupons Management', subtitle: 'Create and track discount codes for your events', icon: 'Tag', grad: 'linear-gradient(120deg,#2dd4bf,#14b8a6)' };
  } else if (path.startsWith('/organizer/scanner/app-setting')) {
    return { title: 'Scanner App Setting', subtitle: 'Control what your door staff can see and do', icon: 'Settings', grad: 'linear-gradient(120deg,#0ea5e9,#2563eb)' };
  } else if (path === '/organizer/scanner' || path.startsWith('/organizer/scanner/') && !path.includes('/app-setting')) {
    return { title: 'My Scanners', subtitle: 'Manage who can check attendees in at the door', icon: 'QrCode', grad: 'linear-gradient(120deg,#2563eb,#0ea5e9)' };
  } else if (path.includes('/pos/fees')) {
    return { title: 'POS Fee Summary', subtitle: 'Platform fees earned, outstanding, and deducted from Point of Sale', icon: 'Receipt', hide: true, grad: 'linear-gradient(120deg,#059669,#14b8a6)' };
  } else if (path.includes('/pos')) {
    return { title: 'Point of Sale', subtitle: 'Sell tickets at the door', icon: 'CreditCard', hide: true, grad: 'linear-gradient(120deg,#059669,#14b8a6)' };
  } else if (path.includes('/review')) {
    return { title: 'Events Review', subtitle: 'What people are saying about your events', icon: 'Star', grad: 'linear-gradient(120deg,#f59e0b,#eab308)' };
  } else if (path.includes('/payout/request')) {
    return { title: 'My Payout Request', subtitle: 'Cash out what you have earned from your events', icon: 'Send', grad: 'linear-gradient(120deg,#1e293b,#312e81)' };
  } else if (path.includes('/payout/method')) {
    return { title: 'Payout Method', subtitle: 'Manage your bank and payout accounts', icon: 'Landmark', hide: true, grad: 'linear-gradient(120deg,#2f9bef,#2563eb 60%,#6d5efc)' };
  } else if (path.includes('/statistics') || path.includes('/report')) {
    return { title: 'My Reports', subtitle: 'Detailed analytics and reporting', hide: true, icon: 'BarChart2', grad: 'linear-gradient(120deg,#2f9bef,#2563eb 60%,#6d5efc)' };
  } else if (path.includes('/profile')) {
    return { title: 'Organizer Profile', subtitle: 'Your public identity and payout details', icon: 'User', grad: 'linear-gradient(120deg,#4f46e5,#2563eb)' };
  } else if (path.includes('/event/create')) {
    return { title: 'Create a New Event', subtitle: 'To create your event, start by uploading photos and videos to make it visually appealing. Click the plus sign in the media section to select files. For each section below, click the plus icon to reveal the fields and enter the required information.', icon: 'CalendarPlus', grad: 'linear-gradient(135deg,#2dd4bf,#f59e0b)' };
  } else if (path.includes('/event') && path.includes('/attendees')) {
    return { title: 'My Events', subtitle: 'Manage all your created events', icon: 'Calendar', hide: true, grad: 'linear-gradient(120deg,#2f9bef,#2563eb 60%,#6d5efc)' };
  } else if (path.includes('/event')) {
    return { title: 'My Events', subtitle: 'Manage all your created events', icon: 'Calendar', grad: 'linear-gradient(120deg,#2f9bef,#2563eb 60%,#6d5efc)' };
  }

  return { title: 'Organizer Portal', subtitle: 'Manage your events and settings', icon: 'LayoutDashboard', grad: 'linear-gradient(120deg,#2f9bef,#2563eb 60%,#6d5efc)' };
});
</script>

<template>
  <div class="flex h-screen w-full bg-slate-50">
    <AppSidebar />
    <div class="flex flex-1 flex-col h-full min-w-0">
      <AppHeader />
      <main class="flex-1 overflow-y-auto p-6">
        <div class="mx-auto max-w-[1100px]">

          <!-- Dynamic Page Header (only show if page doesn't have its own header) -->
          <div v-if="showLayoutHeader" class="rounded-3xl overflow-hidden mb-6" :style="{ background: pageMeta.grad }">
            <div class="p-5 flex items-center justify-between flex-wrap gap-3 text-white">
              <div class="flex items-center gap-4">
                <span class="h-12 w-12 rounded-2xl bg-white/20 grid place-items-center font-black shrink-0">
                  <component :is="(LucideIcons as any)[pageMeta.icon]" class="w-6 h-6" />
                </span>
                <div>
                  <p class="text-xl font-black leading-tight">{{ pageMeta.title }}</p>
                  <p class="text-white/80 text-[13px] font-bold mt-0.5">{{ pageMeta.subtitle }}</p>
                </div>
              </div>

              <!-- Slot for page-specific actions (e.g. Export buttons, filters) -->
              <div class="flex items-center gap-2 flex-wrap empty:hidden">
                <slot name="header-actions"></slot>
              </div>
            </div>
          </div>

          <slot />

        </div>
      </main>
    </div>
  </div>
</template>
