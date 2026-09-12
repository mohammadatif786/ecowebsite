<template>
  <Modal ref="modalRef" maxWidth="max-w-md">
    <div class="p-6 text-slate-900 bg-white rounded-3xl overflow-hidden shadow-2xl">
      <header class="flex items-start justify-between mb-4">
        <div>
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <i data-lucide="user-plus" class="w-5 h-5"></i>
            </div>
            <h2 class="text-2xl font-black">Invite guests</h2>
          </div>
          <p class="text-sm font-bold text-slate-400 mt-1">
            Bring people onto your live — up to 6 guests.
          </p>
        </div>
        <button @click="close" class="p-1 text-slate-400 hover:text-slate-600 transition-colors">
          <i data-lucide="x" class="w-6 h-6"></i>
        </button>
      </header>

      <div class="space-y-6 max-h-[70vh] overflow-y-auto pr-1 hide-scroll">
        <!-- ON YOUR LIVE -->
        <div v-if="invitedGuests.length">
          <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">On your live</p>
          <div class="space-y-2">
            <div v-for="g in invitedGuests" :key="g.id" class="flex items-center gap-4 p-3 rounded-2xl bg-emerald-50/50 border border-emerald-100 shadow-sm">
              <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-xs font-black text-white shadow-md', g.color]">
                {{ g.name.substring(0,2).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-black text-sm text-slate-900 truncate">@{{ g.name }}</p>
                <p v-if="g.status === 'live'" class="text-[10px] text-rose-500 font-black flex items-center gap-1 uppercase">
                  <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span> Live now
                </p>
                <p v-else class="text-[10px] text-amber-500 font-black flex items-center gap-1 uppercase">
                  Invited — waiting to accept
                </p>
              </div>
              <button @click="remove(g.id)" class="px-4 py-2 bg-white text-rose-500 border border-rose-100 hover:bg-rose-50 rounded-xl text-[11px] font-black transition shadow-sm">
                Remove
              </button>
            </div>
          </div>
        </div>

        <!-- SUGGESTED -->
        <div>
          <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Suggested</p>
          <div class="space-y-2">
            <div v-for="g in suggestedGuests" :key="g.id" class="flex items-center gap-4 p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100 transition shadow-sm">
              <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-xs font-black text-white shadow-md', g.color]">
                 {{ g.name.substring(0,2).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-black text-sm text-slate-900 truncate">@{{ g.name }}</p>
                <p class="text-[10px] text-slate-400 font-bold flex items-center gap-1">
                  <i data-lucide="map-pin" class="w-2.5 h-2.5"></i> {{ g.loc }}
                </p>
              </div>
              <button @click="invite(g)" class="px-5 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-full text-[11px] font-black transition transform active:scale-95 shadow-md">
                Invite
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import Modal from '../ui/Modal.vue';

const emit = defineEmits(['invite', 'remove']);

const modalRef = ref(null);
const suggestedGuests = ref([]);
const invitedGuests = ref([]);

const open = (suggested, current) => {
  suggestedGuests.value = suggested.filter(s => !current.some(c => c.id === s.id));
  invitedGuests.value = current;
  if (modalRef.value) {
    modalRef.value.open();
    nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
  }
};

const close = () => {
  if (modalRef.value) modalRef.value.close();
};

const invite = (guest) => {
  emit('invite', guest);
};

const remove = (id) => {
  emit('remove', id);
};

defineExpose({ open, close });
</script>
