<template>
  <div v-if="isOpen"
       class="fixed inset-0 z-[1050] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
       @click.self="close">
    <div class="bg-white rounded-[2rem] max-w-md w-full shadow-2xl relative border border-slate-100 flex flex-col max-h-[88vh] overflow-hidden">
      <!-- Header -->
      <div class="p-5 pb-3 border-b border-slate-100 shrink-0">
        <div class="flex items-center justify-between gap-3">
          <h3 class="text-lg font-black text-slate-900">🎓 My universities</h3>
          <button @click="close" aria-label="Close" class="h-8 w-8 rounded-full bg-slate-100 hover:bg-slate-200 grid place-items-center text-slate-500 font-black">✕</button>
        </div>
        <p class="mt-1.5 text-xs font-semibold text-slate-500 leading-relaxed">
          The schools you attend or attended. In U Vibe you can post to these — and only these. Your main one is selected first.
        </p>
      </div>

      <!-- List -->
      <div class="p-4 overflow-y-auto flex-1 min-h-0">
        <p v-if="!mine.length" class="text-center font-black text-slate-600 text-sm my-4">
          You haven’t added a university yet.
        </p>

        <div v-for="u in mine" :key="u.id"
             :class="['rounded-2xl p-3 mb-2.5 border-[1.5px]', u.is_main ? 'border-violet-300 bg-violet-50/60' : 'border-slate-200 bg-white']">
          <div class="flex items-center gap-2.5">
            <span class="h-10 w-10 rounded-xl bg-slate-100 grid place-items-center text-xl shrink-0">{{ u.flag }}</span>
            <span class="min-w-0 flex-1">
              <span class="block font-black text-sm text-slate-900 truncate">{{ u.full_name }}</span>
              <span class="block text-[11px] font-bold text-slate-500">{{ u.country || 'New campus' }}</span>
            </span>
            <span v-if="u.is_main" class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-black bg-violet-100 text-violet-800">🏠 Main</span>
            <button @click="remove(u)" :disabled="busy" :aria-label="'Remove ' + u.name" title="Remove"
                    class="shrink-0 h-7 w-7 rounded-full bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-500 font-black text-xs disabled:opacity-50">✕</button>
          </div>
          <div class="flex items-center gap-1.5 mt-2.5 flex-wrap">
            <span class="text-[11px] font-extrabold text-slate-500 mr-0.5">I am a</span>
            <button v-for="opt in statusOptions" :key="opt.value" @click="setStatus(u, opt.value)" :disabled="busy"
                    :class="['rounded-full px-2.5 py-1 text-[11px] font-black border-[1.5px] transition',
                             (u.status || 'student') === opt.value ? 'bg-violet-600 border-violet-600 text-white' : 'bg-white border-slate-200 text-slate-600 hover:border-violet-300']">
              {{ opt.label }}
            </button>
            <button v-if="!u.is_main" @click="makeMain(u)" :disabled="busy"
                    class="ml-auto text-[11px] font-black text-violet-700 underline disabled:opacity-50">Make main</button>
          </div>
        </div>

        <button @click="addUniversity" :disabled="isFull"
                class="w-full rounded-2xl border-[1.5px] border-dashed border-violet-400 bg-violet-50/60 text-violet-800 font-black text-sm py-3 hover:bg-violet-100 transition disabled:opacity-45 disabled:cursor-not-allowed">
          ＋ Add a university{{ isFull ? ` (limit of ${max} reached)` : '' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  // { my_universities: [], following: [], max }
  state: { type: Object, default: () => ({ my_universities: [], following: [], max: 5 }) },
});
const emit = defineEmits(['changed', 'addUniversity']);

const isOpen = ref(false);
const busy = ref(false);
const statusOptions = [
  { value: 'student', label: 'Current student' },
  { value: 'alumni', label: 'Alumni' },
];

const mine = computed(() => props.state?.my_universities || []);
const max = computed(() => props.state?.max || 5);
const isFull = computed(() => mine.value.length >= max.value);

const open = () => { isOpen.value = true; };
const close = () => { isOpen.value = false; };

const toast = (msg) => { if (window.toast) window.toast(msg); };
const errorText = (e, fallback) =>
  e?.response?.data?.errors ? Object.values(e.response.data.errors)[0][0] : (e?.response?.data?.message || fallback);

// Every change returns the updated state from the server.
const run = async (request, fallbackError) => {
  if (busy.value) return;
  busy.value = true;
  try {
    const res = await request();
    emit('changed', res.data.state);
    if (res.data.message) toast(res.data.message);
  } catch (e) {
    toast(errorText(e, fallbackError));
  } finally {
    busy.value = false;
  }
};

const remove = (u) => run(() => axios.delete(route('new_frontend.universities.mine.destroy', u.id)), 'Could not remove that university');
const setStatus = (u, status) => {
  if ((u.status || 'student') === status) return;
  run(() => axios.patch(route('new_frontend.universities.mine.status', u.id), { status }), 'Could not save');
};
const makeMain = (u) => run(() => axios.post(route('new_frontend.universities.mine.main', u.id)), 'Could not change your main university');

const addUniversity = () => {
  if (isFull.value) return;
  close();
  emit('addUniversity');
};

defineExpose({ open, close, isOpen });
</script>
