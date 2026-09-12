<template>
  <LkPageShell active-page="likes">
    <h2 class="text-2xl font-black mb-1">Likes You</h2>
    <p class="text-slate-500 mb-4">People who swiped right on you 💛</p>

    <div v-if="people.length === 0" class="card p-12 text-center">
      <i data-lucide="heart" class="w-10 h-10 mx-auto text-slate-200"></i>
      <p class="font-black mt-3">No likes yet</p>
      <p class="text-slate-400 text-sm mt-1">Keep your profile active and likes will roll in!</p>
    </div>

    <div v-for="p in people" :key="p.id" class="card p-3 flex items-center gap-3 mb-2">
      <div class="relative shrink-0">
        <img :src="p.img" class="h-14 w-14 rounded-full object-cover" />
        <span v-if="p.online" class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full bg-green-400 border-2 border-white"></span>
      </div>
      <div class="flex-1 min-w-0">
        <p class="font-black text-slate-900 leading-tight">{{ p.name }}, {{ p.age }}</p>
        <p class="text-xs text-slate-500">{{ p.flag }} {{ p.country }}</p>
        <p v-if="p.goal" class="text-xs text-slate-400 mt-0.5 truncate">{{ p.goal }}</p>
      </div>
      <div class="flex gap-1.5 shrink-0">
        <button @click="openGift(p)" class="btn btn-ghost px-3 py-2 text-xs flex items-center gap-1">
          <i data-lucide="gift" class="w-3.5 h-3.5"></i>Vibe
        </button>
        <button @click="likeBack(p)" class="btn px-4 py-2 text-xs text-white" style="background:linear-gradient(135deg,#db2777,#f43f5e)">
          Like Back 💖
        </button>
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
import axios from 'axios';

defineOptions({ layout: MainLayout });

const props = defineProps({
  currentUser: { type: Object, default: () => ({}) },
  balance: { type: Number, default: 0 },
  serverLikes: { type: [Array, Object], default: () => [] },
});

const mapUser = (u) => ({
  id: u.id, name: u.name, age: u.age || 25,
  country: u.country, flag: u.country_flag || '🌍',
  online: !!u.is_live_streaming,
  img: u.avatar ? (u.avatar.startsWith('http') ? u.avatar : `/storage/${u.avatar}`) : `https://i.pravatar.cc/500?img=${u.id % 70}`,
  goal: u.whyare || '',
});

const people = ref((Array.isArray(props.serverLikes) ? props.serverLikes : Object.values(props.serverLikes || {})).map(mapUser));

const giftDialogRef = ref(null);
const selectedUser = ref(null);
const openGift = (p) => { selectedUser.value = p; giftDialogRef.value?.open(p); };

const likeBack = async (p) => {
  try {
    await axios.post(route('new_frontend.linkup.swipe'), { target_id: p.id, action: 'like' });
    people.value = people.value.filter(u => u.id !== p.id);
  } catch (e) { console.error(e); }
};
</script>
