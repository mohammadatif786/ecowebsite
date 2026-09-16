<template>
  <div v-if="isOpen" class="fixed inset-0 z-[1000] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-300" @click.self="close">
    <div class="bg-white rounded-[2.5rem] max-w-lg w-full shadow-2xl overflow-hidden flex flex-col border border-white/20">
      <!-- Header -->
      <div class="p-6 border-b border-slate-50 flex items-start justify-between">
        <div>
          <h3 class="text-xl font-black text-slate-900 flex items-center gap-2">
            <i data-lucide="zap" class="w-6 h-6 text-orange-500 fill-orange-500"></i>
            Send a Big Up
          </h3>
          <p class="text-xs font-bold text-slate-400 mt-1">
            Show {{ p.handle }} some love · Balance: <span class="text-amber-600">🪙 {{ num(userCoins) }}</span>
          </p>
        </div>
        <button @click="close" class="w-10 h-10 rounded-full hover:bg-slate-50 flex items-center justify-center text-slate-400 transition-colors">
          <i data-lucide="x" class="w-6 h-6"></i>
        </button>
      </div>

      <!-- Grid of Gifts -->
      <div class="p-6 overflow-y-auto hide-scroll max-h-[60vh]">
        <div class="grid grid-cols-3 gap-4">
          <button
            v-for="g in BIG_UPS"
            :key="g.id"
            @click="sendBigUp(g)"
            :disabled="sending || userCoins < g.cost"
            class="group flex flex-col items-center gap-2 p-4 rounded-3xl border-2 border-slate-50 hover:border-lkblue hover:bg-blue-50/30 transition-all active:scale-95 disabled:opacity-50 disabled:grayscale disabled:scale-100"
          >
            <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition duration-300">{{ g.emoji }}</span>
            <div class="text-center">
              <p class="text-[11px] font-black text-slate-900 leading-tight">{{ g.name }}</p>
              <p class="text-[9px] font-bold text-slate-400 leading-tight mt-0.5">{{ g.desc }}</p>
            </div>
            <div class="mt-1 bg-orange-500 text-white px-3 py-1 rounded-full text-[10px] font-black flex items-center gap-1 shadow-md shadow-orange-500/20">
              <i data-lucide="zap" class="w-2.5 h-2.5 fill-white"></i>
              {{ g.cost }}
            </div>
          </button>
        </div>
      </div>

      <!-- Footer Info -->
      <div v-if="error" class="px-6 pb-4">
        <p class="text-xs font-bold text-rose-500 text-center bg-rose-50 p-2 rounded-xl">{{ error }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  p: Object
});

const emit = defineEmits(['sent']);

const isOpen = ref(false);
const sending = ref(false);
const error = ref('');

const page = usePage();
const userCoins = computed(() => page.props.auth?.user?.coins || 0);

const BIG_UPS = [
  { id: 'likkle', name: 'Likkle Up', emoji: '🤙', desc: 'A little big up', cost: 10 },
  { id: 'bigup', name: 'Big Up', emoji: '🙌', desc: 'Big up yuhself!', cost: 25 },
  { id: 'respect', name: 'Respect', emoji: '👊', desc: 'Respect due', cost: 50 },
  { id: 'bless', name: 'Bless Up', emoji: '🙏', desc: 'Blessings on yuh', cost: 75 },
  { id: 'nuff', name: 'Nuff Respect', emoji: '💯', desc: 'Nuff respect, star', cost: 100 },
  { id: 'blessings', name: 'Blessings', emoji: '🕊️', desc: 'Blessings upon you', cost: 150 },
  { id: 'one_love', name: 'One Love', emoji: '❤️', desc: 'One love, always', cost: 200 },
  { id: 'god_bless', name: 'God Bless You', emoji: '😇', desc: 'God bless you', cost: 250 },
  { id: 'love_respect', name: 'Love & Respect', emoji: '💕', desc: "Love an' respect", cost: 350 },
  { id: 'big_tings', name: 'Big Tings', emoji: '🔥', desc: 'Big tings a gwaan', cost: 500 },
  { id: 'crown', name: 'Caribbean Crown', emoji: '👑', desc: 'King/Queen ting', cost: 1000 },
];

const open = () => {
  isOpen.value = true;
  error.value = '';
  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
  });
};

const close = () => {
  if (sending.value) return;
  isOpen.value = false;
};

const sendBigUp = async (gift) => {
  if (userCoins.value < gift.cost) {
    error.value = "Insufficient coins";
    return;
  }

  sending.value = true;
  error.value = '';

  try {
    const response = await axios.post(route('new_frontend.vibes.bigup', { vibe: props.p.id }), {
      gift_name: gift.name,
      emoji: gift.emoji,
      coins: gift.cost
    });

    // Update global auth user coins via Inertia if possible, or just emit success
    if (page.props.auth?.user) {
      page.props.auth.user.coins = response.data.user_coins;
    }

    emit('sent', response.data.bigups_count);
    if (window.toast) window.toast(`Sent ${gift.name}! 🪙`);
    close();
  } catch (e) {
    error.value = e.response?.data?.message || "Failed to send Big Up";
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
