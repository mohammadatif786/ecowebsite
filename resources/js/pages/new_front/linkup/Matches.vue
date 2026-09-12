<template>
  <LkPageShell active-page="matches">
    <template #header-actions>
      <button
        v-if="selectedProfile"
        type="button"
        class="btn btn-ghost px-4 py-2.5 flex items-center gap-2"
        @click="selectedProfile = null"
      >
        <i data-lucide="list" class="w-4 h-4"></i>
        All matches
      </button>
    </template>

    <div v-if="people.length === 0" class="card p-12 text-center">
      <i data-lucide="heart" class="w-10 h-10 mx-auto text-slate-200"></i>
      <p class="font-black mt-3">No matches yet</p>
      <p class="text-slate-400 text-sm mt-1">Keep swiping - your matches will appear here.</p>
      <a href="/new_frontend/dating" class="btn btn-primary px-5 py-3 mt-4 inline-block">Go to Swipe</a>
    </div>

    <LkProfileDetail
      v-else-if="selectedProfile"
      :profile="selectedProfile"
      :balance="props.balance"
      back-label="Matches"
      @close="selectedProfile = null"
      @remove="removeProfile"
    />

    <section v-else>
      <h2 class="text-2xl font-black mb-1">Your Matches</h2>
      <p class="text-slate-500 mb-4">You both liked each other.</p>

      <button
        v-for="p in people"
        :key="p.id"
        type="button"
        class="card p-3 flex items-center gap-3 mb-2 text-left w-full hover:shadow-md transition"
        @click="selectedProfile = p"
      >
        <div class="relative shrink-0">
          <img :src="p.img" :alt="p.name" class="h-14 w-14 rounded-full object-cover" />
          <span v-if="p.online" class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full bg-green-400 border-2 border-white"></span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-black text-slate-900 leading-tight">{{ p.name }}, {{ p.age }}</p>
          <p class="text-xs text-slate-500">
            <img v-if="isFlagImage(p.flag)" :src="p.flag" :alt="`${p.country} flag`" class="inline h-3.5 w-5 rounded-sm object-cover mr-1" />
            <span v-else>{{ p.flag }}</span>
            {{ p.country }}
          </p>
          <p v-if="p.goal" class="text-xs text-slate-400 mt-0.5 truncate">{{ p.goal }}</p>
        </div>
        <span class="btn btn-primary px-4 py-2 text-xs shrink-0">View Profile</span>
      </button>
    </section>
  </LkPageShell>
</template>

<script setup>
import { ref } from 'vue';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import LkPageShell from '../../../components/new_frontend/LkPageShell.vue';
import LkProfileDetail from '../../../components/new_frontend/LkProfileDetail.vue';
import { isFlagImage, mapLinkupUser } from '../../../components/new_frontend/linkupProfile';

defineOptions({ layout: MainLayout });

const props = defineProps({
  currentUser: { type: Object, default: () => ({}) },
  balance: { type: Number, default: 0 },
  serverMatches: { type: [Array, Object], default: () => [] },
});

const sourceMatches = Array.isArray(props.serverMatches)
  ? props.serverMatches
  : Object.values(props.serverMatches || {});

const people = ref(sourceMatches.map(mapLinkupUser));
const selectedProfile = ref(null);

const removeProfile = (profileId) => {
  people.value = people.value.filter((person) => person.id !== profileId);
  selectedProfile.value = null;
};
</script>
