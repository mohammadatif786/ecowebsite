<template>
  <Modal ref="modalRef" maxWidth="max-w-[520px]">
    <div class="bg-white">
      <template v-if="viewMode === 'form'">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5">
          <h3 class="text-2xl font-black text-slate-950">Reserve a Table</h3>
          <button @click="close" class="text-slate-400 transition hover:text-slate-700">
            <i data-lucide="x" class="h-5 w-5"></i>
          </button>
        </div>

        <div class="space-y-4 px-5 py-5">
          <div>
            <label class="mb-2 block text-xs font-black text-slate-500">Restaurant</label>
            <select
              v-model="restaurantId"
              class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base font-semibold text-slate-800 outline-none transition focus:border-blue-400"
            >
              <option v-for="restaurant in restaurants" :key="restaurant.id" :value="restaurant.id">
                {{ restaurantLabel(restaurant) }}
              </option>
            </select>
          </div>

          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
              <label class="mb-2 block text-xs font-black text-slate-500">Date</label>
              <div class="relative">
                <input
                  v-model="resDate"
                  class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-11 text-base font-semibold text-slate-800 outline-none transition focus:border-blue-400"
                />
                <i data-lucide="calendar-days" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-900"></i>
              </div>
            </div>

            <div>
              <label class="mb-2 block text-xs font-black text-slate-500">Time</label>
              <div class="relative">
                <input
                  v-model="resTime"
                  class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-11 text-base font-semibold text-slate-800 outline-none transition focus:border-blue-400"
                />
                <i data-lucide="clock" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-900"></i>
              </div>
            </div>
          </div>

          <div>
            <label class="mb-2 block text-xs font-black text-slate-500">Party size</label>
            <div class="flex items-center gap-5">
              <button
                @click="partySize = Math.max(1, partySize - 1)"
                class="grid h-10 w-10 place-items-center rounded-full border border-slate-200 text-xl text-slate-700 transition hover:bg-slate-50"
              >
                -
              </button>
              <span class="min-w-4 text-center text-xl font-black text-slate-950">{{ partySize }}</span>
              <button
                @click="partySize = Math.min(20, partySize + 1)"
                class="grid h-10 w-10 place-items-center rounded-full border border-slate-200 text-xl text-slate-700 transition hover:bg-slate-50"
              >
                +
              </button>
            </div>
          </div>

          <div>
            <label class="mb-2 block text-xs font-black text-slate-500">Special request (optional)</label>
            <textarea
              v-model="specialRequest"
              rows="3"
              placeholder="e.g. window seat, birthday, allergy"
              class="w-full resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-400"
            ></textarea>
          </div>

          <button
            @click="makeReservation"
            class="w-full rounded-2xl bg-blue-600 py-3.5 text-base font-black text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-700"
          >
            Confirm Reservation
          </button>

          <button
            @click="openReservationsList"
            class="w-full rounded-2xl border border-slate-200 bg-white py-3 text-sm font-black text-slate-950 transition hover:bg-slate-50"
          >
            View my reservations
          </button>
        </div>
      </template>

      <template v-else>
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5">
          <h3 class="text-xl font-black text-slate-950">My Reservations</h3>
          <button @click="close" class="text-slate-400 transition hover:text-slate-700">
            <i data-lucide="x" class="h-5 w-5"></i>
          </button>
        </div>

        <div class="space-y-4 px-5 py-5">
          <div v-if="!savedReservations.length" class="rounded-2xl border border-slate-200 p-4 text-center text-sm font-bold text-slate-400">
            No reservations yet.
          </div>

          <div
            v-for="reservation in savedReservations"
            :key="reservation.id"
            class="rounded-2xl border border-slate-200 p-4"
          >
            <div class="flex items-start justify-between gap-3">
              <div>
                <h4 class="font-black text-slate-950">{{ reservation.place }}</h4>
                <p class="mt-1 text-sm font-semibold text-slate-500">
                  {{ formatReservationDate(reservation.date) }} - {{ formatReservationTime(reservation.time) }} - {{ reservation.party_size }} guests
                </p>
              </div>
              <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-600">Confirmed</span>
            </div>

            <button
              @click="cancelReservation(reservation.id)"
              class="mt-3 w-full rounded-xl border border-slate-200 bg-white py-2 text-sm font-black text-rose-500 transition hover:bg-rose-50"
            >
              Cancel
            </button>
          </div>

          <button
            @click="showForm"
            class="w-full rounded-2xl bg-blue-600 py-3.5 text-base font-black text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-700"
          >
            Reserve another table
          </button>
        </div>
      </template>
    </div>
  </Modal>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue';
import Modal from '../ui/Modal.vue';
import { DB, getRestaurants } from '../MockDataStore';

const modalRef = ref(null);
const restaurants = getRestaurants();

const viewMode = ref('form');
const restaurantId = ref(restaurants[0]?.id || null);
const resDate = ref('');
const resTime = ref('07:00 PM');
const partySize = ref(2);
const specialRequest = ref('');
const savedReservations = ref([]);

const selectedRestaurant = computed(() => restaurants.find((restaurant) => restaurant.id === restaurantId.value) || restaurants[0] || null);

const restaurantLabel = (restaurant) => {
  return [restaurant.name, restaurant.cuisine].filter(Boolean).join(' - ');
};

const defaultDate = () => {
  const date = new Date();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${month}/${day}/${date.getFullYear()}`;
};

const refreshIcons = () => {
  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
  });
};

const loadReservations = () => {
  savedReservations.value = DB.get('lk_eats_reservations', []);
};

const open = (restaurant = null) => {
  const chosen = restaurant || selectedRestaurant.value || restaurants[0] || null;
  restaurantId.value = chosen?.id || restaurants[0]?.id || null;
  resDate.value = defaultDate();
  resTime.value = '07:00 PM';
  partySize.value = 2;
  specialRequest.value = '';
  viewMode.value = 'form';
  loadReservations();

  if (modalRef.value) {
    modalRef.value.open();
    refreshIcons();
  }
};

const close = () => {
  if (modalRef.value) modalRef.value.close();
};

const showForm = () => {
  viewMode.value = 'form';
  refreshIcons();
};

const openReservationsList = () => {
  loadReservations();
  viewMode.value = 'list';
  refreshIcons();
};

const makeReservation = () => {
  const restaurant = selectedRestaurant.value;
  const all = DB.get('lk_eats_reservations', []);
  all.push({
    id: Date.now(),
    restaurant_id: restaurant?.id || null,
    place: restaurant?.name || 'Restaurant',
    cuisine: restaurant?.cuisine || '',
    date: resDate.value || defaultDate(),
    time: resTime.value || '07:00 PM',
    party_size: partySize.value,
    request: specialRequest.value.trim(),
    created_at: new Date().toISOString(),
  });
  DB.set('lk_eats_reservations', all);
  loadReservations();
  viewMode.value = 'list';
  refreshIcons();
  if (window.toast) window.toast('Table reserved');
};

const cancelReservation = (reservationId) => {
  const remaining = DB.get('lk_eats_reservations', []).filter((reservation) => reservation.id !== reservationId);
  DB.set('lk_eats_reservations', remaining);
  loadReservations();
  if (window.toast) window.toast('Reservation cancelled');
};

const formatReservationDate = (value) => {
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value || '';
  return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }).toUpperCase();
};

const formatReservationTime = (value) => {
  if (!value) return '';
  const match = String(value).trim().match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
  if (!match) return value;

  let hour = Number(match[1]);
  const minute = match[2];
  const meridiem = match[3].toUpperCase();

  if (meridiem === 'PM' && hour !== 12) hour += 12;
  if (meridiem === 'AM' && hour === 12) hour = 0;

  return `${String(hour).padStart(2, '0')}:${minute}`;
};

defineExpose({ open, close });
</script>
