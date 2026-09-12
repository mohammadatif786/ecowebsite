<template>
  <LkPageShell active-page="find">
    <template #header-actions>
      <button @click="openFiltersModal" class="btn btn-ghost px-4 py-2.5 flex items-center gap-2">
        <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>Filters
        <span v-if="isFilterApplied" class="h-2 w-2 rounded-full inline-block" style="background:var(--lk-blue)"></span>
      </button>
    </template>

    <LkProfileDetail
      v-if="selectedProfile"
      :profile="selectedProfile"
      :balance="props.balance"
      back-label="Find Matches"
      @close="selectedProfile = null"
      @remove="removeProfile"
    />

    <section v-else>
      <p class="text-slate-500 font-semibold mb-4">
        <span class="font-black text-slate-800">{{ filteredProfiles.length }}</span> people match your filters
      </p>

      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        <div
          v-for="p in filteredProfiles"
          :key="p.id"
          class="relative rounded-3xl overflow-hidden shadow-sm cursor-pointer"
          style="aspect-ratio:3/4.1;background:#2a8fe0"
          @click="selectedProfile = p"
        >
          <div class="absolute inset-0 w-full h-full">
            <div
              v-for="(img, idx) in p.photos"
              :key="idx"
              class="absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out"
              :class="p.activePhotoIdx === idx ? 'opacity-100' : 'opacity-0'"
            >
              <img :src="img" class="absolute inset-0 w-full h-full object-cover" />
            </div>
          </div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/75 to-transparent pointer-events-none"></div>

          <div v-if="p.photos.length > 1" class="absolute top-2 left-1/2 -translate-x-1/2 z-20 flex gap-1">
            <span
              v-for="(_, k) in p.photos"
              :key="k"
              :class="['h-0.5 rounded-full transition-all duration-300', k === p.activePhotoIdx ? 'bg-white w-3' : 'bg-white/40 w-1']"
            ></span>
          </div>

          <img
            v-if="isFlagImage(p.flag)"
            :src="p.flag"
            :alt="`${p.country} flag`"
            class="absolute top-2 right-2 h-5 w-7 rounded-sm object-cover shadow z-20"
          />
          <span v-else class="absolute top-2 right-2 text-xl drop-shadow z-20">{{ p.flag }}</span>
          <span v-if="p.online" class="absolute top-2 left-2 z-20 flex items-center gap-1 bg-black/40 rounded-full px-1.5 py-0.5">
            <span class="h-2 w-2 rounded-full bg-green-400"></span>
            <span class="text-white text-[9px] font-bold">Online</span>
          </span>

          <div class="absolute inset-x-0 bottom-0 p-2.5 text-white z-20">
            <p class="font-black text-sm leading-tight drop-shadow">{{ p.name }}, {{ p.age }}</p>
            <p class="text-white/90 text-[11px] flex items-center gap-1 mt-0.5">
              <i data-lucide="map-pin" class="w-3 h-3"></i>{{ p.km ?? 'Unknown' }} km
            </p>
            <div class="flex items-center justify-start gap-2 mt-2">
              <button disabled title="Undo is not available yet" aria-label="Undo" class="h-6 w-6 rounded-full bg-lkblue2 grid place-items-center opacity-50 cursor-not-allowed" @click.stop>
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-white"></i>
              </button>
              <button @click.stop="apiSwipe(p.id, 'pass')" title="Pass" aria-label="Pass" class="h-6 w-6 rounded-full bg-white/20 grid place-items-center">
                <i data-lucide="thumbs-down" class="w-3.5 h-3.5" style="color:var(--lk-yellow)"></i>
              </button>
              <button @click.stop="openGift(p)" title="Send Vibe" aria-label="Send Vibe" class="h-6 w-6 rounded-full bg-pink-500/80 grid place-items-center">
                <i data-lucide="gift" class="w-3.5 h-3.5 text-white"></i>
              </button>
              <button disabled title="Magic Vibe is not available yet" aria-label="Magic Vibe" class="h-6 w-6 rounded-full bg-white/20 grid place-items-center opacity-50 cursor-not-allowed">
                <i data-lucide="wand-2" class="w-3.5 h-3.5" style="color:#f9a8d4"></i>
              </button>
              <button @click.stop="apiSwipe(p.id, 'like')" title="Top Shelf" aria-label="Top Shelf" class="h-6 w-6 rounded-full bg-white/20 grid place-items-center">
                <i data-lucide="star" class="w-3.5 h-3.5" style="color:var(--lk-yellow)"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="filteredProfiles.length === 0" class="card p-12 text-center">
        <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-300"></i>
        <p class="font-black mt-3">No one found</p>
        <p v-if="!isFilterApplied" class="text-slate-400 text-sm mt-1">No discoverable users right now.</p>
        <p v-else class="text-slate-400 text-sm mt-1">Try widening your filters.</p>
        <button v-if="isFilterApplied" @click="openFiltersModal" class="btn btn-primary px-5 py-3 mt-4">Edit Filters</button>
      </div>
    </section>

    <LkFiltersModal ref="filtersModalRef" :caribbeanCountries="props.caribbeanCountries" @apply="applyFilter" />
    <GiftDialog ref="giftDialogRef" :user="selectedUser" :balance="props.balance" />
  </LkPageShell>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import LkPageShell from '../../../components/new_frontend/LkPageShell.vue';
import LkProfileDetail from '../../../components/new_frontend/LkProfileDetail.vue';
import LkFiltersModal from '../../../components/new_frontend/modals/LkFiltersModal.vue';
import GiftDialog from '../../../components/new_frontend/modals/GiftDialog.vue';
import { isFlagImage, mapLinkupUser } from '../../../components/new_frontend/linkupProfile';
import axios from 'axios';

defineOptions({ layout: MainLayout });

const props = defineProps({
  currentUser: { type: Object, default: () => ({}) },
  balance: { type: Number, default: 0 },
  serverDiscoverUsers: { type: [Array, Object], default: () => [] },
  caribbeanCountries: { type: Array, default: () => [] },
});

const discoverList = ref((Array.isArray(props.serverDiscoverUsers) ? props.serverDiscoverUsers : Object.values(props.serverDiscoverUsers || {})).map(mapLinkupUser));
const selectedProfile = ref(null);
const selectedUser = ref(null);
const filtersModalRef = ref(null);
const giftDialogRef = ref(null);

let findTimer = null;
onMounted(() => {
  findTimer = setInterval(() => {
    discoverList.value.forEach((p) => {
      if (p.photos && p.photos.length > 1) {
        p.swipeDelay -= 500;
        if (p.swipeDelay <= 0) {
          p.activePhotoIdx = (p.activePhotoIdx + 1) % p.photos.length;
          p.swipeDelay = 5000;
        }
      }
    });
  }, 500);
});

onUnmounted(() => {
  if (findTimer) clearInterval(findTimer);
});

const isFilterApplied = ref(false);
const lkFilter = ref({ ageMin: 15, ageMax: 60, distMin: 0, distMax: 20000, gender: 'both', countryMode: 'all', country: '', interests: [], languages: [], religions: [], goals: [] });

const filteredProfiles = computed(() => {
  const base = discoverList.value;
  if (!isFilterApplied.value) return base;
  const f = lkFilter.value;
  return base.filter((p) => {
    const ageOk = p.age >= f.ageMin && p.age <= f.ageMax;
    const distOk = f.distMax >= 20000 || (p.km >= f.distMin && p.km <= f.distMax);
    let genderOk = true;
    if (f.gender !== 'both') {
      const g1 = (p.gender || '').toLowerCase();
      genderOk = g1 === f.gender || g1.startsWith(f.gender);
    }
    const ctrOk = f.countryMode !== 'other' || !f.country || p.caribbeanCountry === f.country || p.country === f.country;
    const intOk = !f.interests.length || f.interests.some((i) => p.interests.includes(i));
    const langOk = !f.languages.length || f.languages.some((l) => p.languages.includes(l));
    const religionOk = !f.religions.length || f.religions.includes(p.raw?.religion);
    const goalOk = !f.goals.length || f.goals.some((g) => (p.goal || '').toLowerCase().includes(g.toLowerCase()));
    return ageOk && distOk && genderOk && ctrOk && intOk && langOk && religionOk && goalOk;
  });
});

const openFiltersModal = () => { if (filtersModalRef.value) filtersModalRef.value.open(lkFilter.value); };
const applyFilter = (f) => {
  lkFilter.value = { ...f };
  isFilterApplied.value = true;

  const gender = { m: 'male', f: 'female', both: 'both' }[f.gender] || 'both';
  router.get(route('new_frontend.dating.find'), {
    age_min: f.ageMin,
    age_max: f.ageMax,
    distance_min: f.distMin,
    distance_max: f.distMax,
    gender,
    country: f.countryMode === 'other' ? f.country : undefined,
    interests: f.interests,
    languages: f.languages,
    religions: f.religions,
    goals: f.goals,
  }, { preserveScroll: true, preserveState: true, replace: true });
};
const openGift = (p) => { selectedUser.value = p; giftDialogRef.value?.open(p); };
const removeProfile = (profileId) => {
  discoverList.value = discoverList.value.filter((u) => u.id !== profileId);
  selectedProfile.value = null;
};
const apiSwipe = async (id, action) => {
  try {
    await axios.post(route('new_frontend.linkup.swipe'), { target_id: id, action });
    removeProfile(id);
  } catch (error) {
    if (window.toast) window.toast(error.response?.data?.message || 'That action could not be completed.');
  }
};
</script>
