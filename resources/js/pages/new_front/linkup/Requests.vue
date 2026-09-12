<template>
  <LkPageShell active-page="requests">
    <h2 class="text-2xl font-black mb-1">Friend Requests</h2>
    <p class="text-slate-500 mb-4">People who want to connect with you</p>

    <div v-if="reqList.length === 0" class="card p-12 text-center">
      <i data-lucide="user-plus" class="w-10 h-10 mx-auto text-slate-200"></i>
      <p class="font-black mt-3">No pending requests</p>
      <p class="text-slate-400 text-sm mt-1">Link Up with people and they'll appear here.</p>
    </div>

    <div v-for="(r, k) in reqList" :key="r.id" class="card p-3 flex items-center gap-3 mb-2">
      <div class="relative shrink-0">
        <img :src="r.img" class="h-14 w-14 rounded-full object-cover" />
      </div>
      <div class="flex-1 min-w-0">
        <p class="font-black text-slate-900">{{ r.name }}, {{ r.age }}</p>
        <p class="text-xs text-slate-500">{{ r.flag }} {{ r.country }}</p>
      </div>
      <div class="flex gap-1.5 shrink-0">
        <span v-if="r.st === 'accepted'" class="rounded-full bg-green-50 text-green-700 px-3 py-1.5 text-xs font-black">Accepted</span>
        <template v-else>
          <button @click="act(k, true, r)" class="btn btn-primary px-3 py-2 text-xs">Accept</button>
          <button @click="act(k, false, r)" class="btn btn-ghost px-3 py-2 text-xs">Decline</button>
        </template>
      </div>
    </div>
  </LkPageShell>
</template>

<script setup>
import { ref } from 'vue';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import LkPageShell from '../../../components/new_frontend/LkPageShell.vue';
import axios from 'axios';

defineOptions({ layout: MainLayout });

const props = defineProps({
  currentUser: { type: Object, default: () => ({}) },
  serverRequests: { type: [Array, Object], default: () => [] },
});

const mapUser = (u) => ({
  id: u.id, name: u.name, age: u.age || 25,
  country: u.country, flag: u.country_flag || '🌍',
  img: u.avatar ? (u.avatar.startsWith('http') ? u.avatar : `/storage/${u.avatar}`) : `https://i.pravatar.cc/500?img=${u.id % 70}`,
  st: null,
});

const reqList = ref((Array.isArray(props.serverRequests) ? props.serverRequests : Object.values(props.serverRequests || {})).map(mapUser));

const act = async (k, accept, r) => {
  try {
    const action = accept ? '1' : '0';
    await axios.post(route('new_frontend.linkup.request'), { sender_id: r.id, action });
    if (accept) {
      reqList.value[k].st = 'accepted';
    } else {
      reqList.value.splice(k, 1);
    }
  } catch (e) { console.error(e); }
};
</script>
