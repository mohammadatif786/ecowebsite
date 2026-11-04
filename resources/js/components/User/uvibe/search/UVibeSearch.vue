<template>
  <!-- The magnifying glass in the U Vibe header and the window it opens: every poster listed up front, narrowing as you type; matching campuses underneath. -->
  <button type="button" @click="open" class="h-11 w-11 rounded-xl bg-white/20 grid place-items-center" title="Search U Vibe" aria-label="Search U Vibe">
    <Search class="w-4 h-4" />
  </button>

  <!-- These windows are opened from the purple header (white text); they must not inherit it.
       `contents` = no box of its own, so it doesn't change the header's layout. -->
  <div class="contents text-slate-900">
  <Modal ref="modalRef" maxWidth="max-w-lg">
    <div class="sticky top-0 z-[2]" style="padding:14px 14px 12px;background:linear-gradient(120deg,#4f46e5,#7c3aed 55%,#db2777)">
      <div class="flex items-center gap-2 text-white">
        <Search class="w-5 h-5" />
        <h3 class="font-black text-base">Search U Vibe</h3>
        <button type="button" @click="close" aria-label="Close" class="ml-auto font-black text-base w-7 h-7">✕</button>
      </div>
      <input ref="inputRef" v-model="q" type="search" autocomplete="off" placeholder="Search by name or university" aria-label="Search by name or university"
             class="mt-2.5 w-full rounded-full bg-white/95 px-4 py-2.5 text-sm font-bold outline-none text-slate-900" />
    </div>

    <div class="p-2">
      <p v-if="loading && !people.length && !campuses.length" class="text-center text-slate-400 font-bold text-sm py-10">Searching…</p>
      <p v-else-if="failed" class="text-center text-rose-600 font-bold text-sm py-10 px-3">Couldn’t search right now — try again.</p>

      <template v-if="people.length">
        <p class="mx-2 mt-2 mb-0.5 text-[11px] font-black tracking-wide uppercase text-slate-500">People · {{ people.length }}</p>
        <button v-for="p in people" :key="p.user_id" type="button" @click="pickPerson(p)"
                class="w-full flex items-center gap-3 p-2 rounded-2xl text-left hover:bg-slate-50">
          <img v-if="p.avatar && !broken[p.user_id]" :src="p.avatar" alt="" @error="broken[p.user_id] = true"
               class="shrink-0 w-11 h-11 rounded-full object-cover bg-slate-100" />
          <span v-else class="shrink-0 w-11 h-11 rounded-full grid place-items-center text-white font-black text-sm"
                style="background:linear-gradient(135deg,#6366f1,#ec4899)">{{ initials(p.name) }}</span>
          <span class="min-w-0 flex-1">
            <span class="block font-black text-sm text-slate-900 truncate">{{ p.name }}</span>
            <span class="block text-xs font-semibold text-slate-500 truncate">{{ subline(p) }}</span>
          </span>
          <span class="shrink-0 text-slate-300 font-black">›</span>
        </button>
      </template>

      <template v-if="campuses.length">
        <p class="mx-2 mt-2 mb-0.5 text-[11px] font-black tracking-wide uppercase text-slate-500">Campuses</p>
        <button v-for="u in campuses" :key="'c' + u.id" type="button" @click="pickCampus(u)"
                class="w-full flex items-center gap-3 p-2 rounded-2xl text-left hover:bg-slate-50">
          <span class="shrink-0 w-11 h-11 rounded-2xl grid place-items-center text-xl bg-slate-100">{{ u.flag }}</span>
          <span class="min-w-0 flex-1">
            <span class="block font-black text-sm text-slate-900 truncate">{{ u.full_name }}</span>
            <span class="block text-xs font-semibold text-slate-500">{{ u.country || 'New campus' }}</span>
          </span>
          <span class="shrink-0 text-slate-300 font-black">›</span>
        </button>
      </template>

      <p v-if="!loading && !failed && !people.length && !campuses.length" class="text-center text-slate-400 font-extrabold text-sm py-10 px-3">
        <template v-if="q.trim()">No one found for “{{ q.trim() }}”.</template>
        <template v-else>No one has posted on U Vibe yet.</template>
      </p>
    </div>
  </Modal>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import Modal from '../../ui/Modal.vue';
import { notify } from '../../../../services/uvibe';
import { searchUVibe } from '../../../../services/uvibeSearch';

// visit: a campus the member picked (same object the Campus Explorer gives) — the page opens its feed.
const emit = defineEmits(['visit']);

const modalRef = ref(null);
const inputRef = ref(null);
const q = ref('');
const people = ref([]);
const campuses = ref([]);
const loading = ref(false);
const failed = ref(false);
const broken = reactive({}); // avatars that didn't load -> show initials

let timer = null;
let latest = 0; // only the newest request may fill the lists

const run = async () => {
  const mine = ++latest;
  loading.value = true;
  failed.value = false;
  try {
    const res = await searchUVibe(q.value.trim());
    if (mine !== latest) return;
    people.value = res.people || [];
    campuses.value = res.campuses || [];
  } catch (e) {
    if (mine !== latest) return;
    people.value = [];
    campuses.value = [];
    failed.value = true;
  } finally {
    if (mine === latest) loading.value = false;
  }
};

// Typing narrows the lists (after a short pause, so it isn't one request per key).
watch(q, () => {
  clearTimeout(timer);
  timer = setTimeout(run, 250);
});

const open = () => {
  clearTimeout(timer);
  q.value = '';
  people.value = [];
  campuses.value = [];
  modalRef.value?.open();
  run();
  setTimeout(() => inputRef.value?.focus(), 40);
};
const close = () => { clearTimeout(timer); latest++; modalRef.value?.close(); };

const initials = (name) =>
  String(name || '').replace(/[^A-Za-z0-9 ]/g, '').trim().split(/\s+/).slice(0, 2).map((w) => w.charAt(0)).join('').toUpperCase() || '?';

// "🇯🇲 UWI Mona · 2 posts · Chess Society"
const subline = (p) => {
  const parts = [];
  if (p.campus) parts.push(`${p.campus.flag} ${p.campus.name}`);
  parts.push(`${p.posts} post${p.posts === 1 ? '' : 's'}`);
  if (p.orgs?.length) parts.push(p.orgs.join(', '));
  return parts.join(' · ');
};

const pickPerson = (p) => {
  close();
  router.visit(route('user.vibes.user-profile', p.user_id));
};
const pickCampus = (u) => {
  close();
  notify(`${u.flag} ${u.full_name}`);
  emit('visit', u);
};

defineExpose({ open, close });
</script>
