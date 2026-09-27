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
          No gift senders yet
        </div>
        <div v-for="sender in senders" :key="sender.id"
            class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-100 bg-white hover:border-slate-200 transition">
          <div class="flex items-center gap-3 min-w-0">
            <img :src="sender.avatar || ('https://i.pravatar.cc/150?u=' + sender.id)" class="w-11 h-11 rounded-full object-cover border border-slate-200 shrink-0 bg-slate-100" />
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
  { id: 101, name: 'Aaliyah', gift_name: 'Caribbean Crown', coins: 500, avatar: 'https://i.pravatar.cc/150?img=35' },
  { id: 102, name: 'Renee', gift_name: 'Caribbean Crown', coins: 500, avatar: 'https://i.pravatar.cc/150?img=45' },
  { id: 103, name: 'Marcus', gift_name: 'Caribbean Crown', coins: 500, avatar: 'https://i.pravatar.cc/150?img=52' },
  { id: 104, name: 'Denise', gift_name: 'Caribbean Crown', coins: 500, avatar: 'https://i.pravatar.cc/150?img=28' },
  { id: 105, name: 'Carlos', gift_name: 'Caribbean Crown', coins: 400, avatar: 'https://i.pravatar.cc/150?img=60' },
];

const senders = ref([]);

const open = (customSenders = null) => {
  isOpen.value = true;
  view.value = 'list';
  activeSender.value = null;
  sending.value = false;

  const raw = customSenders || props.giftsReceived || [];
  if (raw.length > 0) {
    senders.value = raw.map(g => ({
      id: g.sender?.id || g.sender_id || 1,
      name: g.sender?.name || 'Supporter',
      gift_name: g.name || 'Gift',
      coins: g.coins || 100,
      avatar: g.sender?.avatar
    }));
  } else {
    senders.value = defaultSenders;
  }

  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
  });
};

const close = () => {
  if (sending.value) return;
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
  if (!activeSender.value || !thankMessage.value.trim()) return;

  sending.value = true;
  try {
    await axios.post(route('new_frontend.vibes.thank-sender'), {
      user_id: activeSender.value.id,
      message: thankMessage.value
    });

    if (window.toast) {
      window.toast(`🙏 Thank you message sent to ${activeSender.value.name}!`);
    }
    close();
  } catch (e) {
    console.error('Failed to send thank you', e);
    if (window.toast) window.toast(e.response?.data?.message || 'Failed to send thank you message');
  } finally {
    sending.value = false;
  }
};

defineExpose({ open, close });
</script>

<style scoped>
.hide-scroll::-webkit-scrollbar { display: none; }
.hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
</style>
