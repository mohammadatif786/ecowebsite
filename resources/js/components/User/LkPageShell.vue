<template>
  <div class="fade">
    <!-- Page Header -->
    <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
      <div>
        <h1 class="text-2xl md:text-3xl font-black text-lkink">LinkUp</h1>
        <p class="text-slate-500 font-semibold mt-1">Meet people across the Caribbean &amp; LatAm</p>
      </div>
      <slot name="header-actions" />
    </div>

    <!-- Tab Navigation -->
    <div class="flex gap-2 overflow-x-auto hide-scroll pb-1 mb-5">
      <Link
        v-for="tab in TABS"
        :key="tab.key"
        :href="tab.url"
        :class="['chip cursor-pointer no-underline', isActive(tab.key) ? 'on' : '']"
      >
        {{ tab.label }}
      </Link>
    </div>

    <!-- Page Content -->
    <slot />
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  activePage: { type: String, required: true }, // 'swipe' | 'find' | 'matches' | 'friends' | 'requests' | 'likes' | 'chats'
});

const TABS = [
  { key: 'swipe',    label: 'Swipe',         url: '/user/dating' },
  { key: 'find',     label: 'Find Matches',   url: '/user/dating/find' },
  { key: 'matches',  label: 'Matches',        url: '/user/dating/matches' },
  { key: 'friends',  label: 'Friends',        url: '/user/dating/friends' },
  { key: 'requests', label: 'Requests',       url: '/user/dating/requests' },
  { key: 'likes',    label: 'Likes',          url: '/user/dating/likes' },
  { key: 'chats',    label: 'Chats',          url: '/user/dating/chats' },
];

const isActive = (key) => props.activePage === key;
</script>
