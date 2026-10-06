<template>
  <div v-if="isOpen"
      class="fixed inset-0 z-[1050] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-300"
      @click.self="close">
    <div class="bg-white rounded-[2.5rem] max-w-lg w-full shadow-2xl overflow-hidden flex flex-col border border-slate-100">
      <!-- Header -->
      <div class="p-5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/50">
        <div class="flex items-center gap-2.5">
          <span class="text-xl">🙏</span>
          <h3 class="text-lg font-black text-slate-900">Say Thank You</h3>
        </div>
        <button @click="close" class="h-9 w-9 rounded-full bg-slate-100 hover:bg-slate-200 grid place-items-center text-slate-500 transition">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <!-- View 1: List of Senders -->
      <div v-if="view === 'list'" class="p-4 overflow-y-auto max-h-[60vh] space-y-2.5 hide-scroll">
        <div v-if="senders.length === 0" class="py-16 text-center text-slate-400 font-bold text-sm">
          No unthanked gift senders yet
        </div>
        <div v-for="sender in senders" :key="sender.id"
            class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-100 bg-white hover:border-slate-200 transition">
          <div class="flex items-center gap-3 min-w-0">
            <img :src="sender.avatar || ('https://i.pravatar.cc/150?u=' + sender.user_id)" class="w-11 h-11 rounded-full object-cover border border-slate-200 shrink-0 bg-slate-100" />
            <div class="min-w-0">
              <p class="font-black text-sm text-slate-900 truncate">{{ sender.name }}</p>
              <p class="text-[11px] font-bold text-slate-500 truncate">Sent you {{ sender.gift_name || 'Gift' }} · 🪙 {{ sender.coins }}</p>
            </div>
          </div>
          <button @click="openComposer(sender)" class="btn btn-ghost text-xs px-3.5 py-2 font-black text-blue-600 hover:bg-blue-50 shrink-0 flex items-center gap-1">
            Thank →
          </button>
        </div>
      </div>

      <!-- View 2: Message Composer -->
      <div v-else-if="view === 'composer'" class="p-5 space-y-4">
        <p class="text-xs font-bold text-slate-600">
          Sending a thank-you message to <b class="text-slate-900">{{ activeSender?.name }}</b> for their support.
        </p>

        <div>
          <textarea v-model="thankMessage" rows="5"
            class="w-full bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-500 rounded-2xl p-4 text-xs font-semibold text-slate-800 outline-none transition resize-none leading-relaxed"
            placeholder="Write your thank you message..."></textarea>
          <p class="text-[11px] text-slate-400 font-bold mt-1.5 px-1">
            Feel free to edit this into your own words before sending.
          </p>
        </div>

        <div class="flex gap-2 pt-2">
          <button @click="view = 'list'" class="btn btn-ghost px-4 py-3 text-xs font-bold">
            Back
          </button>
          <button @click="sendThankYou" :disabled="sending || !thankMessage.trim()"
            class="flex-1 btn btn-primary py-3 text-xs font-black shadow-lg shadow-blue-500/20 disabled:opacity-50">
            <span v-if="sending" class="flex items-center justify-center gap-2">
              <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Sending...
            </span>
            <span v-else class="flex items-center justify-center gap-1.5">
              <i data-lucide="send" class="w-4 h-4"></i> Send Thank You
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  giftsReceived: { type: Array, default: () => [] }
});

const isOpen = ref(false);
const view = ref('list'); // 'list' or 'composer'
const activeSender = ref(null);
const sending = ref(false);
const thankMessage = ref('');

const defaultSenders = [
  { id: 101, user_id: 101, name: 'Aaliyah', gift_name: 'Caribbean Crown', coins: 500, avatar: 'https://i.pravatar.cc/150?img=35' },
  { id: 102, user_id: 102, name: 'Renee', gift_name: 'Caribbean Crown', coins: 500, avatar: 'https://i.pravatar.cc/150?img=45' },
  { id: 103, user_id: 103, name: 'Marcus', gift_name: 'Caribbean Crown', coins: 500, avatar: 'https://i.pravatar.cc/150?img=52' },
];

const senders = ref([]);

const open = (customSenders = null, targetSender = null) => {
  isOpen.value = true;
  view.value = 'list';
  activeSender.value = null;
  sending.value = false;

  const raw = customSenders || props.giftsReceived || [];
  if (raw.length > 0) {
    senders.value = raw.map(g => {
      const isTopSender = g.rank !== undefined || g.total_coins !== undefined || g.handle !== undefined || (g.user_id && !g.recieved_id && !g.sender_id);
      return {
        id: isTopSender ? null : g.id,
        user_id: isTopSender ? (g.user_id || g.id) : (g.sender_id || g.sender?.id || g.user_id),
        name: g.name || g.sender?.name || 'Supporter',
        gift_name: g.gift_name || g.name || 'Gift',
        coins: g.coins || g.total_coins || 100,
        avatar: g.avatar || g.sender?.avatar,
        is_top_sender: isTopSender
      };
    });
  } else {
    senders.value = defaultSenders;
  }

  if (targetSender) {
    const isTopSender = targetSender.rank !== undefined || targetSender.total_coins !== undefined || targetSender.handle !== undefined || (targetSender.user_id && !targetSender.recieved_id && !targetSender.sender_id);
    const formattedSender = {
      id: isTopSender ? null : (targetSender.id || targetSender.gift_coins_id),
      user_id: isTopSender ? (targetSender.user_id || targetSender.id) : (targetSender.sender_id || targetSender.sender?.id || targetSender.user_id),
      name: targetSender.name || targetSender.handle || 'Supporter',
      gift_name: targetSender.gift_name || targetSender.name || 'Gift',
      coins: targetSender.coins || targetSender.total_coins || 100,
      avatar: targetSender.avatar || targetSender.sender?.avatar,
      is_top_sender: isTopSender
    };
    openComposer(formattedSender);
  } else {
    nextTick(() => {
      if (window.lucide) window.lucide.createIcons();
    });
  }
};

const close = () => {
  isOpen.value = false;
};

const openComposer = (sender) => {
  activeSender.value = sender;
  thankMessage.value = `Hey! I just wanted to say thank you so much for the Vibes and coins you've sent my way — it really means a lot and I truly appreciate the support! 🙏💛`;
  view.value = 'composer';
  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
  });
};

const sendThankYou = async () => {
  const userId = activeSender.value?.user_id;

  if (!activeSender.value || !userId || !thankMessage.value.trim()) {
    if (window.toast) window.toast('Please select a valid user to send thank you');
    return;
  }

  sending.value = true;
  try {
    const payload = {
      user_id: userId,
      message: thankMessage.value
    };

    if (!activeSender.value.is_top_sender && activeSender.value.id) {
      payload.gift_coins_id = activeSender.value.id;
    }

    await axios.post(route('new_frontend.vibes.thank-sender'), payload);

    if (window.toast) {
      window.toast(`🙏 Thank you message sent to ${activeSender.value.name}!`);
    }

    sending.value = false;
    close();
  } catch (e) {
    console.error('Failed to send thank you', e);
    console.error('Validation error response data:', e.response?.data);
    const errors = e.response?.data?.errors;
    const firstError = errors ? Object.values(errors)[0]?.[0] : null;
    const errorMsg = firstError || e.response?.data?.message || 'Failed to send thank you message';
    if (window.toast) window.toast(errorMsg);
    sending.value = false;
  }
};

defineExpose({ open, close, openComposer });
</script>

<style scoped>
.hide-scroll::-webkit-scrollbar { display: none; }
.hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
</style>
