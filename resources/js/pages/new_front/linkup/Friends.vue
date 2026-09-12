<template>
  <LkPageShell active-page="friends">
    <h2 class="text-2xl font-black mb-1">Friends</h2>
    <p class="text-slate-500 mb-4">People you've connected with</p>

    <div v-if="people.length === 0" class="card p-12 text-center">
      <i data-lucide="users" class="w-10 h-10 mx-auto text-slate-200"></i>
      <p class="font-black mt-3">No friends yet</p>
      <p class="text-slate-400 text-sm mt-1">Accept friend requests to grow your circle.</p>
      <a href="/new_frontend/dating/requests" class="btn btn-primary px-5 py-3 mt-4 inline-block">View Requests</a>
    </div>

    <div v-for="p in people" :key="p.id" class="card p-3 flex items-center gap-3 mb-2">
      <div class="relative shrink-0">
        <img :src="p.img" class="h-14 w-14 rounded-full object-cover" />
        <span v-if="p.online" class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full bg-green-400 border-2 border-white"></span>
      </div>
      <div class="flex-1 min-w-0">
        <p class="font-black text-slate-900 leading-tight">{{ p.name }}, {{ p.age }}</p>
        <p class="text-xs text-slate-500 flex items-center gap-1">
          <img v-if="isFlagImage(p.flag)" :src="p.flag" :alt="`${p.country} flag`" class="h-3.5 w-5 rounded-sm object-cover" />
          <span v-else>{{ p.flag }}</span>
          {{ p.country }}
        </p>
        <p v-if="p.goal" class="text-xs text-slate-400 mt-0.5 truncate">{{ p.goal }}</p>
      </div>
      <div class="flex gap-1.5 shrink-0">
        <button @click="openGift(p)" class="btn btn-ghost px-3 py-2 text-xs flex items-center gap-1">
          <i data-lucide="gift" class="w-3.5 h-3.5"></i>Vibe
        </button>
        <a href="/new_frontend/dating/chats" class="btn btn-primary px-4 py-2 text-xs">Message</a>
      </div>
    </div>

    <GiftDialog ref="giftDialogRef" :user="selectedUser" :balance="props.balance" />
  </LkPageShell>
</template>

<script setup>
import { ref } from 'vue';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import LkPageShell from '../../../components/new_frontend/LkPageShell.vue';
import GiftDialog from '../../../components/new_frontend/modals/GiftDialog.vue';
import { isFlagImage } from '../../../components/new_frontend/linkupProfile';

defineOptions({ layout: MainLayout });

const props = defineProps({
  currentUser: { type: Object, default: () => ({}) },
  balance: { type: Number, default: 0 },
  serverFriends: { type: [Array, Object], default: () => [] },
});

const mapUser = (u) => ({
  id: u.id, name: u.name, age: u.age || 25,
  country: u.country, flag: u.country_flag || '🌍',
  km: u.distanceinMK != null ? Math.floor(parseFloat(u.distanceinMK)) : 9999,
  online: !!u.is_live_streaming,
  img: u.avatar ? (u.avatar.startsWith('http') ? u.avatar : `/storage/${u.avatar}`) : `https://i.pravatar.cc/500?img=${u.id % 70}`,
  goal: u.whyare || '',
});

const people = ref((Array.isArray(props.serverFriends) ? props.serverFriends : Object.values(props.serverFriends || {})).map(mapUser));

const giftDialogRef = ref(null);
const selectedUser = ref(null);
const openGift = (p) => { selectedUser.value = p; giftDialogRef.value?.open(p); };
</script>
