<template>
  <div v-if="isOpen"
      class="fixed inset-0 z-[1050] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-300"
      @click.self="close">
    <div class="bg-white rounded-[2.5rem] max-w-lg w-full shadow-2xl overflow-hidden flex flex-col border border-slate-100">
      <!-- Header -->
      <div class="p-6 shrink-0" style="background:linear-gradient(120deg,#dbeafe,#fae8ff,#fef9c3)">
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="h-12 w-12 rounded-2xl bg-blue-100 grid place-items-center text-2xl shadow-sm">🌹</div>
            <div>
              <h3 class="text-xl font-black text-slate-900">Send a Vibe</h3>
              <p class="text-slate-500 text-xs font-bold mt-0.5">Send @{{ recipientHandle }} a gift — they earn the coins 💰</p>
            </div>
          </div>
          <button @click="close" class="h-9 w-9 rounded-full bg-white hover:bg-slate-100 grid place-items-center shrink-0 shadow-sm text-slate-500 transition">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>
      </div>

      <!-- Subheader / Balance -->
      <div class="px-6 py-3 flex items-center justify-between border-b border-slate-100 shrink-0 bg-slate-50">
        <span class="rounded-full bg-blue-600 text-white px-4 py-1.5 font-black text-xs inline-flex items-center gap-1 shadow-sm">
          <i data-lucide="coins" class="w-3.5 h-3.5"></i> {{ num(userCoins) }} Coins
        </span>
        <span class="font-black text-slate-800 text-xs">
          {{ selectedGift ? selectedGift[0] : 'Pick a gift' }}
        </span>
      </div>

      <!-- Gifts Grid -->
      <div class="p-4 grid grid-cols-3 gap-2.5 overflow-y-auto max-h-[50vh] hide-scroll">
        <button v-for="(g, idx) in VIBES_GIFTS" :key="idx"
          @click="selectGift(g)"
          :disabled="g[2] > userCoins"
          :class="[
            'text-left rounded-2xl p-3 bg-white border transition active:scale-95 group',
            selectedGift === g ? 'border-2 border-indigo-500 bg-indigo-50/50 shadow-sm' : 'border-slate-100 hover:border-slate-300',
            g[2] > userCoins ? 'opacity-40 grayscale cursor-not-allowed' : 'cursor-pointer'
          ]">
          <div class="h-10 w-10 rounded-xl bg-fuchsia-50 grid place-items-center text-xl mb-1.5 group-hover:scale-110 transition">{{ g[1] }}</div>
          <p class="font-black text-xs text-slate-900 leading-tight">{{ g[0] }}</p>
          <p class="text-[10px] font-bold text-slate-400 min-h-[24px] leading-tight mt-1">{{ g[3] }}</p>
          <span :class="['inline-block mt-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-black', g[2] === 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-600 text-white']">
            {{ g[2] === 0 ? 'Free' : `${g[2]} 🪙` }}
          </span>
        </button>
      </div>

      <!-- Footer Action -->
      <div class="border-t border-slate-100 p-4 shrink-0 bg-white space-y-2">
        <button @click="sendGift"
          :disabled="!selectedGift || selectedGift[2] > userCoins || sending"
          class="w-full rounded-2xl py-3.5 font-black text-white shadow-lg transition active:scale-95 disabled:opacity-40 disabled:scale-100"
          :class="(!selectedGift || selectedGift[2] > userCoins) ? 'bg-slate-300' : 'bg-slate-950 shadow-slate-950/20'">
          <span v-if="sending" class="flex items-center justify-center gap-2">
            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Sending Vibe...
          </span>
          <span v-else>
            {{ !selectedGift ? 'Select a gift' : (selectedGift[2] > userCoins ? 'Not enough Coins' : ('Send ' + selectedGift[0] + (selectedGift[2] === 0 ? ' · Free' : ' · ' + selectedGift[2] + ' 🪙'))) }}
          </span>
        </button>

        <div class="flex justify-between text-[11px] font-bold text-slate-400 px-1">
          <span>Selected: <b class="text-slate-800">{{ selectedGift ? selectedGift[0] : '—' }}</b></span>
          <span>Cost: <b class="text-slate-800">{{ selectedGift ? (selectedGift[2] === 0 ? 'Free' : selectedGift[2] + ' 🪙') : '—' }}</b></span>
          <span>After: <b class="text-slate-800">{{ remainingCoins !== null ? num(remainingCoins) : '—' }}</b></span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  recipientUserId: { type: Number, required: true },
  recipientHandle: { type: String, default: 'creator' }
});

const emit = defineEmits(['sent']);

const isOpen = ref(false);
const sending = ref(false);
const selectedGift = ref(null);

const page = usePage();
const userCoins = computed(() => page.props.auth?.user?.coins || 0);

const VIBES_GIFTS = [
  ['Wha Gwaan', '👋', 0, 'Just saying hi 👋'],
  ['Likkle Smile', '😊', 10, 'A sweet island smile'],
  ['Soca Vibe', '🎶', 35, 'Feel the riddim with me'],
  ['Wine & Dance', '💃', 50, 'Pull up and wine 💃'],
  ['Sunset Lime', '🌅', 75, 'Catch a sunset together'],
  ['Coconut Cheers', '🥥', 90, 'Cheers from the islands'],
  ['Island Rose', '🌹', 120, 'A rose, respectfully 🌹'],
  ['Sweet Kiss', '😘', 150, 'Flirty but classy'],
  ['Candlelit Date', '🕯️', 220, 'Dinner by candlelight'],
  ['Beach Linkup', '🏝️', 250, 'Link up by the sea'],
  ['Sweetheart', '💖', 300, "I really like you 💖"],
  ['Island Royalty', '👑', 400, 'You’re my King/Queen 👑']
];

const remainingCoins = computed(() => {
  if (!selectedGift.value) return null;
  return userCoins.value - selectedGift.value[2];
});

const open = () => {
  isOpen.value = true;
  selectedGift.value = null;
  sending.value = false;
  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
  });
};

const close = () => {
  if (sending.value) return;
  isOpen.value = false;
};

const selectGift = (g) => {
  selectedGift.value = g;
};

const sendGift = async () => {
  if (!selectedGift.value) return;
  const cost = selectedGift.value[2];
  if (userCoins.value < cost) {
    if (window.toast) window.toast('Not enough Coins');
    return;
  }

  sending.value = true;
  try {
    const response = await axios.post(route('new_frontend.vibes.send-vibe'), {
      gift_name: selectedGift.value[0],
      emoji: selectedGift.value[1],
      coins: cost,
      recipient_id: props.recipientUserId
    });

    if (page.props.auth?.user) {
      page.props.auth.user.coins = response.data.user_coins;
    }

    router.reload({ only: ['auth'] });

    if (window.toast) {
      window.toast(`${selectedGift.value[1]} ${selectedGift.value[0]} sent! 🪙`);
    }

    emit('sent', cost);
    close();
  } catch (e) {
    console.error('Failed to send vibe gift', e);
    if (window.toast) window.toast(e.response?.data?.message || 'Failed to send gift');
  } finally {
    sending.value = false;
  }
};

const num = (n) => Number(n || 0).toLocaleString();

defineExpose({ open, close });
</script>

<style scoped>
.hide-scroll::-webkit-scrollbar { display: none; }
.hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
</style>
