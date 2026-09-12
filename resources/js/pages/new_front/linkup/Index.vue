<template>
  <LkPageShell active-page="swipe">
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
      back-label="Swipe"
      @close="closeProfileDetail"
      @remove="removeProfile"
    />

    <div v-else class="max-w-md mx-auto">
      <div class="flex items-center gap-2 mb-3">
        <button @click="openFiltersModal" class="chip flex items-center gap-1.5">
          Apply Filters <i data-lucide="sliders-horizontal" class="w-4 h-4 text-lkblue"></i>
        </button>
        <button disabled title="Profile hiding is not available yet" class="chip ml-auto flex items-center gap-1.5 opacity-50 cursor-not-allowed">
          <i data-lucide="eye-off" class="w-4 h-4 text-lkblue"></i>Hide Profile
        </button>
      </div>

      <div v-if="filteredProfiles.length">
        <div class="relative overflow-visible" style="height:clamp(340px,54vh,560px)">
          <div
            v-for="(profile, index) in upcomingProfiles"
            :key="profile.id"
            class="absolute inset-y-0 overflow-hidden rounded-3xl bg-slate-200 shadow-xl"
            :style="upcomingCardStyle(index)"
          >
            <img :src="profile.photos?.[0] || profile.img" :alt="profile.name" class="h-full w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-black/10"></div>
            <div class="absolute inset-x-0 bottom-0 p-5 text-white">
              <p class="text-2xl font-black leading-tight drop-shadow">{{ profile.name }}, {{ profile.age }}</p>
              <p class="mt-1 flex items-center gap-1 text-sm text-white/90">
                <i data-lucide="map-pin" class="h-4 w-4"></i>{{ profile.km ?? 'Unknown' }} km away
              </p>
            </div>
          </div>

          <div
            ref="swipeCardRef"
            :key="currentProfile.id"
            class="absolute inset-0 z-30 overflow-hidden rounded-3xl shadow-xl"
          :class="isDragging ? 'cursor-grabbing' : 'cursor-grab'"
          :style="swipeCardStyle"
          @pointerdown="startCardSwipe"
          @pointermove="moveCardSwipe"
          @pointerup="endCardSwipe"
          @pointercancel="cancelCardSwipe"
        >
          <div class="absolute inset-0 w-full h-full">
            <div
              v-for="(img, idx) in currentPhotos"
              :key="idx"
              class="absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out"
              :class="currentPhoto === idx ? 'opacity-100' : 'opacity-0'"
            >
              <img :src="img" class="w-full h-full object-cover" />
            </div>
          </div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-black/10 pointer-events-none"></div>
          <div
            v-if="dragX > 20"
            class="pointer-events-none absolute left-6 top-8 z-30 rounded-xl border-4 border-lime-300 px-3 py-1 text-3xl font-black tracking-wider text-lime-200"
            :style="{ opacity: Math.min(dragX / 110, 1), transform: 'rotate(-14deg)' }"
          >LIKE</div>
          <div
            v-else-if="dragX < -20"
            class="pointer-events-none absolute right-6 top-8 z-30 rounded-xl border-4 border-rose-400 px-3 py-1 text-3xl font-black tracking-wider text-rose-300"
            :style="{ opacity: Math.min(Math.abs(dragX) / 110, 1), transform: 'rotate(14deg)' }"
          >NOPE</div>

          <button data-swipe-photo-control @click="prevPhoto" class="absolute left-0 top-0 h-3/5 w-1/2 z-10"></button>
          <button data-swipe-photo-control @click="nextPhoto" class="absolute right-0 top-0 h-3/5 w-1/2 z-10"></button>

          <div class="absolute top-3 left-1/2 -translate-x-1/2 flex gap-1">
            <span
              v-for="(_, k) in currentPhotos"
              :key="k"
              :class="['h-1 rounded-full', k === currentPhoto ? 'bg-white w-6' : 'bg-white/50 w-2']"
            ></span>
          </div>

          <img
            v-if="isFlagImage(currentProfile.flag)"
            :src="currentProfile.flag"
            :alt="`${currentProfile.country} flag`"
            class="absolute top-3 right-3 h-7 w-9 rounded object-cover shadow z-20"
          />
          <span v-else class="absolute top-3 right-3 text-3xl drop-shadow z-20">{{ currentProfile.flag }}</span>
          <span v-if="currentProfile.online" class="absolute top-12 right-3 z-20 flex items-center gap-1 bg-black/40 rounded-full px-2 py-0.5">
            <span class="h-2 w-2 rounded-full bg-green-400"></span>
            <span class="text-white text-[10px] font-bold">Online</span>
          </span>

          <div class="absolute inset-x-0 bottom-0 p-5 text-white z-20">
            <div class="flex items-end justify-between gap-3">
              <div class="min-w-0">
                <p
                  data-swipe-interactive
                  class="font-black text-3xl leading-tight drop-shadow cursor-pointer hover:opacity-80 transition"
                  @pointerdown.stop
                  @click.stop="selectedProfile = currentProfile"
                >
                    {{ currentProfile.name }}, {{ currentProfile.age }}
                </p>
                <p class="text-white/90 flex items-center gap-1 mt-1 truncate">
                  <i data-lucide="map-pin" class="w-4 h-4"></i>{{ currentProfile.km ?? 'Unknown' }} km away -
                  <img
                    v-if="isFlagImage(currentProfile.flag)"
                    :src="currentProfile.flag"
                    :alt="`${currentProfile.country} flag`"
                    class="h-4 w-5 rounded-sm object-cover"
                  />
                  <span v-else>{{ currentProfile.flag }}</span>
                  {{ currentProfile.country }}
                </p>
                <p class="text-white/90 mt-0.5 truncate">{{ currentProfile.goal }}</p>
                <p class="text-white/80 text-sm mt-1 line-clamp-1">{{ currentProfile.bio }}</p>
              </div>

              <button
                data-swipe-interactive
                @pointerdown.stop
                @click.stop="selectedProfile = currentProfile"
                class="h-12 w-12 rounded-full border-2 border-white/40 bg-white/10 backdrop-blur-md grid place-items-center hover:bg-white/20 transition shrink-0 shadow-lg mb-1"
              >
                <i data-lucide="eye" class="w-5 h-5 text-white"></i>
              </button>
            </div>
          </div>
        </div>
        </div>

        <div class="grid grid-cols-6 gap-2 mt-4 items-end">
          <div class="flex flex-col items-center gap-1.5">
            <button @click="swBack" class="rounded-full shadow bg-white border border-slate-200 grid place-items-center" style="height:52px;width:52px">
              <i data-lucide="rotate-ccw" class="w-5 h-5" style="color:#2f9bef"></i>
            </button>
            <span class="text-[10px] font-black text-slate-400">Backup</span>
          </div>
          <div class="flex flex-col items-center gap-1.5">
            <button @click="swAct('nah')" class="rounded-full shadow bg-white border border-rose-100 grid place-items-center" style="height:52px;width:52px">
              <i data-lucide="thumbs-down" class="w-5 h-5" style="color:#f43f5e"></i>
            </button>
            <span class="text-[10px] font-black text-rose-500">NAH</span>
          </div>
          <div class="flex flex-col items-center gap-1.5">
            <button @click="swAct('yes')" class="rounded-full shadow-lg grid place-items-center text-white" style="height:64px;width:64px;background:linear-gradient(135deg,#35c3e8,#9EDB2F)">
              <i data-lucide="thumbs-up" class="w-7 h-7"></i>
            </button>
            <span class="text-[10px] font-black" style="color:#2487e0">Yes Sir!</span>
          </div>
          <div class="flex flex-col items-center gap-1.5">
            <button @click="swAct('vibe')" class="rounded-full shadow grid place-items-center text-white" style="height:52px;width:52px;background:linear-gradient(135deg,#f59e0b,#ec4899)">
              <i data-lucide="gift" class="w-5 h-5 text-white"></i>
            </button>
            <span class="text-[10px] font-black" style="color:#ec4899">Send Vibe</span>
          </div>
          <div class="flex flex-col items-center gap-1.5">
            <button @click="swAct('linkup')" class="rounded-full bg-white shadow grid place-items-center border border-slate-200" style="height:52px;width:52px">
              <svg viewBox="0 0 120 120" width="26" height="26">
                <ellipse cx="42" cy="20" rx="13" ry="16" fill="#9EDB2F" />
                <ellipse cx="80" cy="20" rx="13" ry="16" fill="#35c3e8" />
                <path d="M34 44 v34 a16 16 0 0 0 32 0 v-34" fill="none" stroke="#35c3e8" stroke-width="18" stroke-linecap="round" />
                <path d="M86 44 v50" fill="none" stroke="#9EDB2F" stroke-width="18" stroke-linecap="round" />
              </svg>
            </button>
            <span class="text-[10px] font-black" style="color:#7ec81e">Link Up</span>
          </div>
          <div class="flex flex-col items-center gap-1.5">
            <button @click="swAct('top')" title="Top Shelf" class="rounded-full shadow bg-white border border-amber-100 grid place-items-center hover:bg-amber-50 transition" style="height:52px;width:52px">
              <i data-lucide="star" class="w-5 h-5" style="color:#eab308"></i>
            </button>
            <span class="text-[10px] font-black" style="color:#eab308">Top Shelf</span>
          </div>
        </div>

        <p :class="['text-center text-[11px] font-black mt-3', quotaLeft <= 3 ? 'text-rose-500' : 'text-slate-400']">
          {{ quotaLeft }} of 10 free Link Ups left today
        </p>
      </div>

      <div v-else class="card p-10 text-center">
        <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-300"></i>
        <p class="font-black mt-3">No one found</p>
        <p v-if="!isFilterApplied" class="text-slate-400 text-sm mt-1">No users available right now. Check back soon.</p>
        <p v-else class="text-slate-400 text-sm mt-1">Try widening your Age/Distance range or clearing a few filters.</p>
        <button v-if="isFilterApplied" @click="openFiltersModal" class="btn btn-primary px-5 py-3 mt-4">Edit Filters</button>
      </div>
    </div>

    <LkFiltersModal ref="filtersModalRef" :caribbeanCountries="props.caribbeanCountries" @apply="applyFilter" />
    <GiftDialog ref="giftDialogRef" :user="selectedUserForGift" :balance="props.balance" />
  </LkPageShell>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import LkPageShell from '../../../components/new_frontend/LkPageShell.vue';
import LkProfileDetail from '../../../components/new_frontend/LkProfileDetail.vue';
import LkFiltersModal from '../../../components/new_frontend/modals/LkFiltersModal.vue';
import GiftDialog from '../../../components/new_frontend/modals/GiftDialog.vue';
import { DB } from '../../../components/new_frontend/MockDataStore';
import { isFlagImage, mapLinkupUser } from '../../../components/new_frontend/linkupProfile';

defineOptions({ layout: MainLayout });

const props = defineProps({
  currentUser: { type: Object, default: () => ({}) },
  balance: { type: Number, default: 0 },
  serverDiscoverUsers: { type: [Array, Object], default: () => [] },
  serverMatches: { type: [Array, Object], default: () => [] },
  serverFriends: { type: [Array, Object], default: () => [] },
  serverRequests: { type: [Array, Object], default: () => [] },
  serverLikes: { type: [Array, Object], default: () => [] },
  caribbeanCountries: { type: Array, default: () => [] },
});

const discoverList = ref((Array.isArray(props.serverDiscoverUsers) ? props.serverDiscoverUsers : Object.values(props.serverDiscoverUsers || {})).map(mapLinkupUser));
const swipeIdx = ref(0);
const currentPhoto = ref(0);
const filtersModalRef = ref(null);
const giftDialogRef = ref(null);
const selectedUserForGift = ref(null);
const selectedProfile = ref(null);
const swipeCardRef = ref(null);
const dragStartX = ref(0);
const dragX = ref(0);
const isDragging = ref(false);
const isAdvancing = ref(false);
const isResettingCard = ref(false);
const swipeProcessing = ref(false);

const swipeCardStyle = computed(() => ({
  height: '100%',
  background: '#2a8fe0',
  transform: `translateX(${dragX.value}px) rotate(${-3 + dragX.value / 24}deg)`,
  transition: isDragging.value || isResettingCard.value ? 'none' : 'transform 220ms ease-out',
}));

watch(() => props.serverDiscoverUsers, (users) => {
  const profiles = Array.isArray(users) ? users : Object.values(users || {});
  discoverList.value = profiles.map(mapLinkupUser);
  swipeIdx.value = 0;
  currentPhoto.value = 0;
});

let swipeTimer = null;
const startSwipeTimer = () => {
  stopSwipeTimer();
  if (currentPhotos.value.length > 1) {
    swipeTimer = setInterval(() => {
      nextPhoto();
    }, 4000);
  }
};
const stopSwipeTimer = () => {
  if (swipeTimer) clearInterval(swipeTimer);
};

onMounted(() => {
  startSwipeTimer();
});
onUnmounted(() => {
  stopSwipeTimer();
});

watch(swipeIdx, () => {
  currentPhoto.value = 0;
  startSwipeTimer();
});

const refreshLucideIcons = async () => {
  await nextTick();
  window.lucide?.createIcons();
};

watch(selectedProfile, refreshLucideIcons);

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

const currentProfile = computed(() => filteredProfiles.value[swipeIdx.value % Math.max(filteredProfiles.value.length, 1)]);
const upcomingProfiles = computed(() => {
  const profiles = filteredProfiles.value;
  if (profiles.length < 2) return [];

  return Array.from({ length: Math.min(3, profiles.length - 1) }, (_, index) => (
    profiles[(swipeIdx.value + index + 1) % profiles.length]
  ));
});
const currentPhotos = computed(() => {
  const p = currentProfile.value;
  if (!p) return ['https://i.pravatar.cc/500?img=1'];
  return p.photos?.length ? p.photos : [p.img];
});

const quotaLeft = computed(() => {
  const today = new Date().toISOString().slice(0, 10);
  const s = DB.get('lk_swipe_quota', {});
  return s.date === today ? Math.max(0, 10 - (s.rights || 0)) : 10;
});

const showToast = (msg) => { if (window.toast) window.toast(msg); };
const upcomingCardStyle = (index) => ({
  left: '0px',
  right: '0px',
  zIndex: 20 - index,
  transform: isAdvancing.value && index === 0
    ? 'rotate(0deg) scale(1)'
    : `translateY(${index * 7}px) rotate(${2 + (index * 1.5)}deg) scale(${0.94 - (index * 0.035)})`,
  transition: 'transform 220ms ease-out',
});
const startCardSwipe = (event) => {
  if (swipeProcessing.value || event.button !== undefined && event.button !== 0) return;
  if (event.target.closest('[data-swipe-interactive]')) return;
  if (event.target.closest('button') && !event.target.closest('[data-swipe-photo-control]')) return;

  dragStartX.value = event.clientX;
  dragX.value = 0;
  isDragging.value = true;
  swipeCardRef.value?.setPointerCapture?.(event.pointerId);
};
const moveCardSwipe = (event) => {
  if (!isDragging.value) return;
  dragX.value = event.clientX - dragStartX.value;
};
const cancelCardSwipe = () => {
  if (isAdvancing.value) return;
  isDragging.value = false;
  dragX.value = 0;
};
const endCardSwipe = async (event) => {
  if (!isDragging.value) return;

  const distance = dragX.value;
  isDragging.value = false;
  if (Math.abs(distance) < 110) {
    dragX.value = 0;
    return;
  }

  isAdvancing.value = true;
  dragX.value = distance > 0 ? window.innerWidth : -window.innerWidth;
  await new Promise((resolve) => setTimeout(resolve, 220));
  await swAct(distance > 0 ? 'yes' : 'nah');
  isResettingCard.value = true;
  dragX.value = 0;
  await nextTick();
  await new Promise((resolve) => requestAnimationFrame(resolve));
  isAdvancing.value = false;
  isResettingCard.value = false;
};
const prevPhoto = () => { stopSwipeTimer(); currentPhoto.value = (currentPhoto.value - 1 + currentPhotos.value.length) % currentPhotos.value.length; startSwipeTimer(); };
const nextPhoto = () => { currentPhoto.value = (currentPhoto.value + 1) % currentPhotos.value.length; };
const swBack = () => { if (swipeIdx.value > 0) { swipeIdx.value--; currentPhoto.value = 0; showToast('Backup'); } };

const apiSwipe = async (targetId, action) => {
  try {
    const res = await axios.post(route('new_frontend.linkup.swipe'), { target_id: targetId, action });
    if (res.data.match) {
      showToast("It's a mutual match!");
      router.reload({ only: ['serverMatches'] });
    }
    return true;
  } catch (e) {
    console.error(e);
    showToast(e.response?.data?.message || 'That action could not be completed.');
    return false;
  }
};

const removeProfile = (profileId) => {
  discoverList.value = discoverList.value.filter((person) => person.id !== profileId);
  selectedProfile.value = null;
  if (swipeIdx.value >= discoverList.value.length) swipeIdx.value = 0;
};

const swAct = async (kind) => {
  if (!currentProfile.value || swipeProcessing.value) return;
  if (kind === 'vibe') { openGift(currentProfile.value); return; }
  swipeProcessing.value = true;
  const labels = { yes: 'Yes Sir!', nah: 'NAH', linkup: 'Link Up sent!', top: 'Top Shelf' };
  const today = new Date().toISOString().slice(0, 10);
  let s = DB.get('lk_swipe_quota', {});
  if (s.date !== today) s = { date: today, rights: 0 };
  let succeeded = true;
  if (kind === 'yes' || kind === 'linkup') {
    succeeded = await apiSwipe(currentProfile.value.id, 'like');
  } else if (kind === 'top') {
    succeeded = await apiSwipe(currentProfile.value.id, 'loveit');
  } else if (kind === 'nah') {
    succeeded = await apiSwipe(currentProfile.value.id, 'pass');
  }
  if (!succeeded) {
    swipeProcessing.value = false;
    return;
  }
  if (kind === 'yes' || kind === 'linkup') {
    s.rights = (s.rights || 0) + 1;
    DB.set('lk_swipe_quota', s);
  }
  showToast(labels[kind] || kind);
  swipeIdx.value++;
  currentPhoto.value = 0;
  swipeProcessing.value = false;
};

const openGift = (p) => { selectedUserForGift.value = p; giftDialogRef.value?.open(p); };
const closeProfileDetail = () => { selectedProfile.value = null; };
const openFiltersModal = () => { if (filtersModalRef.value) filtersModalRef.value.open(lkFilter.value); };
const applyFilter = (f) => {
  lkFilter.value = { ...f };
  isFilterApplied.value = true;
  swipeIdx.value = 0;

  const gender = { m: 'male', f: 'female', both: 'both' }[f.gender] || 'both';
  router.get(route('new_frontend.dating'), {
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

</script>
