<template>
  <div class="fade space-y-6">
    <!-- Header -->
    <div class="rounded-3xl brandgrad text-white p-6 md:p-8 flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <h1 class="text-3xl font-black">Notifications</h1>
        <span v-if="unreadCount > 0" class="bg-white/20 text-white text-xs font-black px-3 py-1 rounded-full">{{ unreadCount }} new</span>
      </div>
      <div class="flex items-center gap-2">
        <button @click="markAllRead" class="btn bg-white/15 hover:bg-white/25 text-white px-4 py-2.5 text-sm">Mark all read</button>
        <button @click="openSettings" class="w-10 h-10 rounded-xl bg-white/15 hover:bg-white/25 grid place-items-center transition">
          <i data-lucide="settings" class="w-5 h-5"></i>
        </button>
      </div>
    </div>

    <!-- Filter tabs -->
    <div class="flex gap-2 overflow-x-auto hide-scroll pb-1">
      <button v-for="tab in tabs" :key="tab" @click="activeTab = tab" :class="['chip', tab === activeTab ? 'on' : '']">
        {{ tab }}
      </button>
    </div>

    <!-- List -->
    <div class="space-y-3">
      <button
        v-for="n in filteredNotifications"
        :key="n.id"
        @click="openNotification(n)"
        :class="['card w-full text-left p-4 flex items-center gap-4 transition hover:-translate-y-0.5', n.unread ? 'ring-2 ring-blue-500/40' : '']"
      >
        <img v-if="n.avatar" :src="n.avatar" class="w-11 h-11 rounded-full object-cover shrink-0" />
        <span v-else class="w-11 h-11 rounded-full grid place-items-center text-white font-black text-sm shrink-0" :style="{ background: categoryColor(n.category) }">
          {{ initials(n.message || n.title) }}
        </span>

        <span class="min-w-0 flex-1">
          <span class="flex items-center gap-2">
            <span class="font-black text-sm truncate">{{ n.title }}</span>
            <span v-if="n.unread" class="w-2 h-2 rounded-full bg-blue-600 shrink-0"></span>
          </span>
          <span class="block text-sm text-slate-500 truncate mt-0.5">{{ n.message }}</span>
          <span class="flex items-center gap-2 mt-2">
            <span class="text-[11px] font-black px-2.5 py-1 rounded-full" :style="{ background: categoryColor(n.category) + '1a', color: categoryColor(n.category) }">
              {{ n.category }}
            </span>
            <span class="text-xs text-slate-400 font-semibold">{{ n.timeAgo }}</span>
          </span>
        </span>

        <span class="w-9 h-9 rounded-xl grid place-items-center shrink-0" :style="{ background: categoryColor(n.category) + '1a', color: categoryColor(n.category) }">
          <i :data-lucide="categoryIcon(n.category)" class="w-4 h-4"></i>
        </span>
      </button>

      <div v-if="!filteredNotifications.length" class="card p-10 text-center text-slate-400 font-semibold">
        No notifications here yet.
      </div>
    </div>

    <!-- Notification Settings Modal -->
    <div v-if="showSettings" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4" @click.self="showSettings = false">
      <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden fade">
        <div class="p-6">
          <h3 class="text-xl font-black mb-4">Notification Settings</h3>

          <div class="space-y-5">
            <div>
              <p class="font-black text-sm mb-2">Quiet hours</p>
              <div class="flex items-center gap-3 flex-wrap">
                <label class="text-sm font-bold text-slate-500 flex items-center gap-2">
                  From
                  <input v-model="form.quiet_hours_from" type="time" class="border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-semibold" />
                </label>
                <label class="text-sm font-bold text-slate-500 flex items-center gap-2">
                  To
                  <input v-model="form.quiet_hours_to" type="time" class="border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-semibold" />
                </label>
                <label class="text-sm font-bold inline-flex items-center gap-2">
                  <input v-model="form.allow_priority" type="checkbox" class="w-4 h-4" />
                  Allow priority
                </label>
              </div>
            </div>

            <div>
              <p class="font-black text-sm mb-2">Mute categories</p>
              <div class="flex flex-wrap gap-4 text-sm font-bold">
                <label class="inline-flex items-center gap-2">
                  <input v-model="form.mute_messages" type="checkbox" class="w-4 h-4" />
                  Messages
                </label>
                <label class="inline-flex items-center gap-2">
                  <input v-model="form.mute_matches" type="checkbox" class="w-4 h-4" />
                  Matches
                </label>
                <label class="inline-flex items-center gap-2">
                  <input v-model="form.mute_payments" type="checkbox" class="w-4 h-4" />
                  Payments
                </label>
                <label class="inline-flex items-center gap-2">
                  <input v-model="form.mute_gifts" type="checkbox" class="w-4 h-4" />
                  Gifts
                </label>
                <label class="inline-flex items-center gap-2">
                  <input v-model="form.mute_system" type="checkbox" class="w-4 h-4" />
                  System
                </label>
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-2">
            <button @click="showSettings = false" class="btn btn-ghost px-4 py-2.5">Close</button>
            <button @click="saveSettings" class="btn btn-primary px-4 py-2.5">Save</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
  notifications: { type: Array, default: () => [] },
  settings: {
    type: Object,
    default: () => ({
      quiet_hours_from: '22:00',
      quiet_hours_to: '07:00',
      allow_priority: true,
      mute_messages: false,
      mute_matches: false,
      mute_payments: false,
      mute_gifts: false,
      mute_system: false,
    }),
  },
});

const showSettings = ref(false);
const form = ref({ ...props.settings });

const openSettings = () => {
  form.value = { ...props.settings };
  showSettings.value = true;
};

const saveSettings = () => {
  router.post(route('new_frontend.notifications.settings'), form.value, {
    preserveScroll: true,
    onSuccess: () => {
      showSettings.value = false;
      if (window.toast) window.toast('Notification settings saved');
    },
  });
};

const tabs = ['All', 'Unread', 'Messages', 'Matches', 'Payments', 'Marketplace Orders', 'Eats', 'Events', 'Gifts', 'Requests', 'System', 'Priority'];
const activeTab = ref('All');

const unreadCount = computed(() => props.notifications.filter(n => n.unread).length);

const filteredNotifications = computed(() => {
  if (activeTab.value === 'All') return props.notifications;
  if (activeTab.value === 'Unread') return props.notifications.filter(n => n.unread);
  return props.notifications.filter(n => n.category === activeTab.value);
});

const CATEGORY_COLORS = {
  Messages: '#2f9bef',
  Matches: '#e11d48',
  Payments: '#2563eb',
  'Marketplace Orders': '#16a34a',
  Gifts: '#a855f7',
  Requests: '#f59e0b',
  Events: '#f97316',
  System: '#64748b',
};

const CATEGORY_ICONS = {
  Messages: 'message-circle',
  Matches: 'heart',
  Payments: 'dollar-sign',
  'Marketplace Orders': 'shopping-bag',
  Gifts: 'gift',
  Requests: 'user-plus',
  Events: 'party-popper',
  System: 'bell',
};

const categoryColor = (category) => CATEGORY_COLORS[category] || '#64748b';
const categoryIcon = (category) => CATEGORY_ICONS[category] || 'bell';

const initials = (text) => (text || '').trim().slice(0, 2).toUpperCase() || '·';

const markAllRead = () => {
  router.post(route('new_frontend.notifications.mark_all_read'), {}, { preserveScroll: true });
};

const openNotification = (n) => {
  if (n.unread) {
    router.post(route('new_frontend.notifications.mark_read', n.id), {}, { preserveScroll: true, preserveState: true });
  }
};
</script>
