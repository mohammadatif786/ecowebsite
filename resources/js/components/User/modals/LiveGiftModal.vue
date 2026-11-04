<template>
  <Modal ref="modalRef" maxWidth="max-w-[500px]">
    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl">
      <!-- Header -->
      <header class="p-6 bg-gradient-to-r from-[#e11d48] via-[#f97316] to-[#f59e0b] text-white">
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
              <i data-lucide="gift" class="w-6 h-6"></i>
            </div>
            <div>
              <h2 class="text-xl font-black">Send a Live Gift</h2>
              <p class="text-sm font-bold opacity-90 flex items-center gap-1.5 mt-0.5">
                Balance: <i data-lucide="gem" class="w-3.5 h-3.5"></i> {{ num(balance) }}
              </p>
            </div>
          </div>
          <button @click="close" class="p-1 text-white/70 hover:text-white transition-colors">
            <X class="w-6 h-6" />
          </button>
        </div>
      </header>

      <!-- Gift Grid -->
      <div class="p-5">
        <div class="grid grid-cols-3 gap-3">
          <button
            v-for="gift in GIFTS"
            :key="gift.id"
            @click="send(gift)"
            class="group p-4 rounded-2xl bg-white border border-slate-100 hover:border-amber-400 hover:bg-amber-50/30 transition-all duration-300 text-center flex flex-col items-center gap-2 shadow-sm hover:shadow-md"
          >
            <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform duration-300">
               {{ gift.emoji }}
            </span>
            <div>
              <p class="text-[12px] font-black text-slate-900">{{ gift.name }}</p>
              <p class="text-[11px] font-bold text-amber-600 flex items-center justify-center gap-1">
                <i data-lucide="gem" class="w-3 h-3"></i> {{ num(gift.price) }}
              </p>
            </div>
          </button>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { X } from 'lucide-vue-next';
import Modal from '../ui/Modal.vue';

const emit = defineEmits(['send']);

const props = defineProps({
  balance: { type: Number, default: 0 }
});

const modalRef = ref(null);

const GIFTS = [
  { id: 'applause', name: 'Applause', price: 5, emoji: '👏' },
  { id: 'shoutout', name: 'Shoutout', price: 25, emoji: '📢' },
  { id: 'micdrop', name: 'Mic Drop', price: 50, emoji: '🎤' },
  { id: 'spotlight', name: 'Spotlight', price: 100, emoji: '💡' },
  { id: 'riddim', name: 'Riddim Section', price: 200, emoji: '🥁' },
  { id: 'soca', name: 'Soca Star', price: 350, emoji: '⭐' },
  { id: 'confetti', name: 'Confetti Drop', price: 500, emoji: '🎉' },
  { id: 'fyah', name: 'Fyah Pon Stage', price: 1000, emoji: '🔥' },
  { id: 'crown', name: 'Carnival Crown', price: 2000, emoji: '👑' },
];

const num = (n) => Number(n || 0).toLocaleString();

const open = () => {
  modalRef.value?.open();
  nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
};

const close = () => {
  if (modalRef.value) modalRef.value.close();
};

const send = (gift) => {
  if (props.balance < gift.price) {
    if (window.toast) window.toast('❌ Insufficient coin balance');
    return;
  }
  emit('send', gift);
  close();
};

defineExpose({ open, close });
</script>
