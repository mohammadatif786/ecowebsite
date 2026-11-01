<template>
  <div v-if="isOpen"
       class="fixed inset-0 z-[1050] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
       @click.self="closeOrBack">
    <div class="bg-white rounded-[2rem] max-w-lg w-full shadow-2xl relative border border-slate-100 flex flex-col max-h-[88vh] overflow-hidden">
      <!-- Header + search -->
      <div class="p-4 border-b border-slate-100 shrink-0">
        <div class="flex items-center justify-between gap-3">
          <h3 class="text-lg font-black text-slate-900">🧭 {{ mode === 'add' ? 'Add a university' : 'Campus Explorer' }}</h3>
          <button @click="closeOrBack" aria-label="Close" class="h-8 w-8 rounded-full bg-slate-100 hover:bg-slate-200 grid place-items-center text-slate-500 font-black">✕</button>
        </div>
        <input v-model="q" type="search" autocomplete="off"
               placeholder="Search by university, nickname or country…"
               class="mt-2.5 w-full border-[1.5px] border-slate-200 focus:border-violet-500 rounded-2xl px-3.5 py-2.5 text-sm font-bold text-slate-900 outline-none" />
      </div>

      <div class="px-4 pt-3 pb-5 overflow-y-auto flex-1 min-h-0">
        <p v-if="loading" class="text-center text-slate-400 font-bold text-sm py-10">Loading campuses…</p>
        <p v-else-if="loadError" class="text-center text-rose-600 font-bold text-sm py-10">{{ loadError }}</p>

        <template v-else>
          <!-- Country pills (from the real campus list) -->
          <div class="flex gap-1.5 overflow-x-auto hide-scroll pb-1.5">
            <button @click="country = ''"
                    :class="pillClass(!country)">🌎 All · {{ total }}</button>
            <button v-for="c in countries" :key="c.country" @click="country = c.country"
                    :class="pillClass(country === c.country)">{{ c.flag }} {{ c.country }} · {{ c.count }}</button>
          </div>

          <!-- Shelves (no search, no country filter) -->
          <template v-if="!q.trim() && !country">
            <section v-if="yours.length">
              <p class="shelf">Your campuses</p>
              <CampusRow v-for="u in yours" :key="'y' + u.id" :u="u" :busy="busyId === u.id" @pick="pick" @toggle-follow="toggleFollow" />
            </section>
            <button v-if="mode === 'browse'" @click="manage" class="mt-2 text-xs font-black text-violet-700 underline">Manage my universities</button>

            <section v-if="trending.length">
              <p class="shelf">🔥 Trending now</p>
              <CampusRow v-for="u in trending" :key="'t' + u.id" :u="u" :busy="busyId === u.id" @pick="pick" @toggle-follow="toggleFollow" />
            </section>
            <section v-if="near && near.list.length">
              <p class="shelf">📍 Also in {{ near.country }}</p>
              <CampusRow v-for="u in near.list" :key="'n' + u.id" :u="u" :busy="busyId === u.id" @pick="pick" @toggle-follow="toggleFollow" />
            </section>
            <section v-if="fresh.length">
              <p class="shelf">🆕 New on U Vibe</p>
              <CampusRow v-for="u in fresh" :key="'f' + u.id" :u="u" :busy="busyId === u.id" @pick="pick" @toggle-follow="toggleFollow" />
            </section>
            <section>
              <p class="shelf">All campuses · A–Z</p>
              <CampusRow v-for="u in all" :key="'a' + u.id" :u="u" :busy="busyId === u.id" @pick="pick" @toggle-follow="toggleFollow" />
            </section>
          </template>

          <!-- Search / country results -->
          <template v-else>
            <template v-if="results.length">
              <p class="shelf">{{ results.length }} campus{{ results.length > 1 ? 'es' : '' }}</p>
              <CampusRow v-for="u in results" :key="'r' + u.id" :u="u" :busy="busyId === u.id" @pick="pick" @toggle-follow="toggleFollow" />
            </template>
            <p v-else class="mt-6 text-center font-extrabold text-slate-600 text-sm">No campus matches that yet.</p>
          </template>

          <!-- Not listed: add it as a new campus (adds it to My universities) -->
          <button v-if="canAddCustom && mode === 'add'" @click="addCustom" :disabled="busyId === 'new'"
                  class="mt-4 w-full rounded-2xl border-[1.5px] border-dashed border-violet-400 bg-violet-50/60 text-violet-800 font-black text-sm py-3 disabled:opacity-50">
            ＋ Add “{{ q.trim() }}” as a new campus
          </button>
          <p v-else-if="canAddCustom" class="mt-4 text-center text-xs font-bold text-slate-500">
            Not listed? Add it from <button @click="manage" class="underline text-violet-700 font-black">My universities</button>.
          </p>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, h } from 'vue';
import axios from 'axios';

const props = defineProps({
  state: { type: Object, default: () => ({ my_universities: [], following: [], max: 5, total_campuses: 0 }) },
});
const emit = defineEmits(['changed', 'visit', 'manageUniversities']);

/* One campus row: flag, full name, country · posts; right side = Main / Yours badge or Follow. */
const CampusRow = {
  props: { u: Object, busy: Boolean },
  emits: ['pick', 'toggle-follow'],
  setup(p, { emit: e }) {
    return () => {
      const u = p.u;
      const meta = (u.is_following && !u.is_mine ? '★ Following · ' : '') + (u.country || 'New campus') + ' · ' +
        (u.posts_count ? `${u.posts_count} post${u.posts_count > 1 ? 's' : ''}` : 'No posts yet');
      return h('div', { class: 'flex items-center gap-2.5 py-2 border-b border-slate-100' }, [
        h('button', { type: 'button', class: 'flex items-center gap-2.5 flex-1 min-w-0 text-left', onClick: () => e('pick', u) }, [
          h('span', { class: 'h-10 w-10 rounded-xl bg-slate-100 grid place-items-center text-xl shrink-0' }, u.flag),
          h('span', { class: 'min-w-0 flex-1' }, [
            h('span', { class: 'block font-black text-sm text-slate-900 truncate' }, u.full_name),
            h('span', { class: 'block text-[11px] font-bold text-slate-500 truncate' }, meta),
          ]),
        ]),
        u.is_mine
          ? h('span', { class: 'shrink-0 rounded-full px-2.5 py-1 text-[11px] font-black bg-violet-100 text-violet-800' }, u.is_main ? '🏠 Main' : '🎓 Yours')
          : h('button', {
              type: 'button', disabled: p.busy,
              class: 'shrink-0 rounded-full px-2.5 py-1 text-[11px] font-black border-[1.5px] disabled:opacity-50 ' +
                (u.is_following ? 'bg-white text-rose-700 border-rose-200' : 'bg-slate-100 text-slate-700 border-transparent hover:bg-slate-200'),
              onClick: () => e('toggle-follow', u),
            }, u.is_following ? 'Unfollow' : '☆ Follow'),
      ]);
    };
  },
};

const isOpen = ref(false);
const mode = ref('browse'); // 'browse' = visit a campus · 'add' = add to My universities
const q = ref('');
const country = ref('');
const loading = ref(false);
const loadError = ref('');
const busyId = ref(null);

const catalog = ref({ universities: [], countries: [], total: 0, shelves: {} });

const load = async () => {
  loading.value = true;
  loadError.value = '';
  try {
    const res = await axios.get(route('new_frontend.universities.catalog'));
    catalog.value = res.data;
  } catch (e) {
    loadError.value = 'Could not load campuses. Please try again.';
  } finally {
    loading.value = false;
  }
};

const open = (m = 'browse') => {
  mode.value = m === 'add' ? 'add' : 'browse';
  q.value = '';
  country.value = '';
  isOpen.value = true;
  load();
};
const close = () => { isOpen.value = false; };
// In add mode, closing goes back to My universities (as in the design).
const closeOrBack = () => {
  close();
  if (mode.value === 'add') emit('manageUniversities');
};
const manage = () => { close(); emit('manageUniversities'); };

/* ------- lists ------- */
const byId = computed(() => Object.fromEntries(catalog.value.universities.map(u => [u.id, u])));
const pickIds = (ids) => (ids || []).map(id => byId.value[id]).filter(Boolean);
const all = computed(() => catalog.value.universities);
const total = computed(() => catalog.value.total || all.value.length);
const countries = computed(() => catalog.value.countries || []);
const yours = computed(() => pickIds(catalog.value.shelves?.yours));
const trending = computed(() => pickIds(catalog.value.shelves?.trending));
const fresh = computed(() => pickIds(catalog.value.shelves?.new));
const near = computed(() => {
  const n = catalog.value.shelves?.near;
  return n ? { country: n.country, list: pickIds(n.ids) } : null;
});

const norm = (s) => String(s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, ' ').trim();
const results = computed(() => {
  const nq = norm(q.value);
  return all.value.filter(u =>
    (!country.value || (u.country || 'Other') === country.value) &&
    (!nq || norm(`${u.full_name} ${u.name} ${u.country}`).includes(nq)));
});
const canAddCustom = computed(() => {
  const t = norm(q.value);
  return t.length > 2 && !all.value.some(u => norm(u.full_name) === t || norm(u.name) === t);
});

const pillClass = (on) => [
  'shrink-0 rounded-full px-3 py-1.5 text-xs font-black whitespace-nowrap border-[1.5px]',
  on ? 'border-violet-600 bg-violet-50 text-slate-900' : 'border-slate-200 bg-white text-slate-800',
];

/* ------- actions ------- */
const toast = (msg) => { if (window.toast) window.toast(msg); };
const errorText = (e, fallback) =>
  e?.response?.data?.errors ? Object.values(e.response.data.errors)[0][0] : (e?.response?.data?.message || fallback);

const pick = async (u) => {
  if (mode.value === 'browse') {
    close();
    emit('visit', u);
    return;
  }
  if (u.is_mine) { toast(`${u.name} is already on your list`); return; }
  busyId.value = u.id;
  try {
    const res = await axios.post(route('new_frontend.universities.mine.store'), { university_id: u.id });
    emit('changed', res.data.state);
    toast(res.data.message);
    close();
    emit('manageUniversities');
  } catch (e) {
    toast(errorText(e, 'Could not add that university'));
  } finally {
    busyId.value = null;
  }
};

const addCustom = async () => {
  busyId.value = 'new';
  try {
    const res = await axios.post(route('new_frontend.universities.mine.store'), { name: q.value.trim() });
    emit('changed', res.data.state);
    toast(res.data.message);
    close();
    emit('manageUniversities');
  } catch (e) {
    toast(errorText(e, 'Could not add that campus'));
  } finally {
    busyId.value = null;
  }
};

const toggleFollow = async (u) => {
  busyId.value = u.id;
  try {
    const res = u.is_following
      ? await axios.delete(route('new_frontend.universities.unfollow', u.id))
      : await axios.post(route('new_frontend.universities.follow', u.id));
    u.is_following = !u.is_following;
    emit('changed', res.data.state);
    toast(res.data.message);
  } catch (e) {
    toast(errorText(e, 'Could not update'));
  } finally {
    busyId.value = null;
  }
};

defineExpose({
  open,
  close,
  isOpen,
  // Number of campuses (for the "Explore · N" chip).
  campusCount: computed(() => catalog.value.total || props.state?.total_campuses || 0),
});
</script>

<style scoped>
.shelf {
  margin: 14px 0 2px;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: .04em;
  text-transform: uppercase;
  color: #64748b;
}
</style>
