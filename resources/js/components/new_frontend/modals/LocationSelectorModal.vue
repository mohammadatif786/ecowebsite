<template>
  <Modal ref="modalRef" maxWidth="max-w-md">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-black text-slate-900">Browse Events In</h3>
        <button @click="close" class="p-1 hover:bg-slate-100 rounded-full transition">
          <i data-lucide="x" class="w-6 h-6 text-slate-400"></i>
        </button>
      </div>

      <!-- Current Location Button -->
      <button
        @click="useCurrentLocation"
        :disabled="isLocating"
        class="w-full flex items-center justify-center gap-3 p-4 mb-6 rounded-2xl border-2 border-indigo-50 bg-indigo-50/30 text-indigo-700 font-black hover:bg-indigo-50 transition-all disabled:opacity-50"
      >
        <i v-if="!isLocating" data-lucide="locate" class="w-5 h-5"></i>
        <div v-else class="w-5 h-5 border-2 border-indigo-700 border-t-transparent rounded-full animate-spin"></div>
        {{ isLocating ? 'Finding you...' : 'Use My Current Location' }}
      </button>

      <!-- Predefined Cities -->
      <div class="grid grid-cols-2 gap-3">
        <button
          v-for="city in cities"
          :key="city.id"
          @click="selectCity(city)"
          class="flex items-center gap-2 p-3 rounded-2xl border border-slate-100 hover:border-lkblue2 hover:bg-lkblue2/5 transition-all group"
        >
          <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-50 text-[10px] font-black text-slate-400 group-hover:bg-lkblue2/10 group-hover:text-lkblue2 uppercase">
            {{ city.countryCode }}
          </span>
          <span class="text-sm font-black text-slate-700 truncate">{{ city.name }}</span>
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import Modal from '../ui/Modal.vue';

const emit = defineEmits(['select']);
const modalRef = ref(null);
const isLocating = ref(false);

const cities = [
  { id: 'the-valley', name: 'The Valley, Anguilla', countryCode: 'AI' },
  { id: 'kingston', name: 'Kingston, Jamaica', countryCode: 'JM' },
  { id: 'port-of-spain', name: 'Port of Spain, T&T', countryCode: 'TT' },
  { id: 'bridgetown', name: 'Bridgetown, Barbados', countryCode: 'BB' },
  { id: 'nassau', name: 'Nassau, Bahamas', countryCode: 'BS' },
  { id: 'santo-domingo', name: 'Santo Domingo, DR', countryCode: 'DO' },
];

const open = () => {
  if (modalRef.value) {
    modalRef.value.open();
    nextTick(() => {
      if (window.lucide) window.lucide.createIcons();
    });
  }
};

const close = () => {
  if (modalRef.value) modalRef.value.close();
};

const useCurrentLocation = () => {
  if (!navigator.geolocation) {
    if (window.toast) window.toast('Geolocation is not supported by your browser');
    return;
  }

  isLocating.value = true;
  navigator.geolocation.getCurrentPosition(
    (position) => {
      const { latitude, longitude } = position.coords;
      emit('select', {
        type: 'coordinates',
        lat: latitude,
        lng: longitude,
        name: 'Current Location'
      });
      isLocating.value = false;
      close();
    },
    (error) => {
      isLocating.value = false;
      let msg = 'Failed to get location';
      if (error.code === 1) msg = 'Location access denied';
      if (window.toast) window.toast(msg);
    },
    { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
  );
};

const selectCity = (city) => {
  emit('select', { type: 'city', ...city });
  close();
};

defineExpose({ open, close });
</script>
