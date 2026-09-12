<template>
  <Modal ref="modalRef" maxWidth="max-w-md">
    <div v-if="tag" class="-m-1">
      <div class="h-44 relative">
        <img :src="tag.image" class="w-full h-full object-cover rounded-t-[20px]" />
        <button @click="close" class="absolute top-3 right-3 w-9 h-9 rounded-full bg-black/50 text-white grid place-items-center">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
        <span class="absolute top-3 left-3 text-white text-[11px] font-black px-2.5 py-1 rounded-full" :style="{ background: isEvent ? '#2563eb' : '#8b5cf6' }">
          {{ isEvent ? '🎟️ Tagged event' : '🛍️ Tagged in this vibe' }}
        </span>
      </div>
      
      <div class="p-5">
        <h3 class="text-xl font-black">{{ tag.title }}</h3>
        <p class="text-slate-500 font-semibold mt-0.5">
          {{ isEvent ? `${tag.date} · ${tag.location || 'Location TBA'}` : (tag.seller || 'Marketplace') }}
        </p>
        <p class="text-2xl font-black text-lkink mt-2">{{ tag.price ? money(tag.price) : 'Free' }}</p>
        
        <div v-if="isEvent" class="rounded-2xl bg-blue-50 p-3 mt-3 text-[12px] font-bold text-blue-700 flex items-center gap-2">
          <i data-lucide="ticket" class="w-4 h-4"></i>Pays from your Wallet · ticket saved to My Tickets
        </div>
        <div v-else class="rounded-2xl bg-emerald-50 p-3 mt-3 text-[12px] font-bold text-emerald-700 flex items-center gap-2">
          <i data-lucide="shield-check" class="w-4 h-4"></i>Escrow-protected · seller paid after delivery
        </div>
        
        <div class="flex gap-2 mt-4">
          <button @click="buyTag" class="btn btn-primary flex-1 py-3">
            {{ isEvent ? 'Get Tickets' : 'Buy now' }} · {{ tag.price ? money(tag.price) : 'Free' }}
          </button>
          <button v-if="isEvent" @click="viewEvent" class="btn btn-ghost px-4 py-3">
            View event
          </button>
          <button v-else @click="addToCart" class="btn btn-ghost px-4 py-3">
            Add to Cart
          </button>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from '../ui/Modal.vue';
import { getWallet } from '../MockDataStore';

const modalRef = ref(null);
const tag = ref(null);
const wallet = getWallet();

const isEvent = computed(() => tag.value && tag.value.kind === 'event');
const money = (n) => '$' + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const open = (t) => {
  tag.value = t;
  if (modalRef.value) {
    modalRef.value.open();
    if (window.lucide) {
      setTimeout(() => lucide.createIcons(), 50);
    }
  }
};

const close = () => {
  if (modalRef.value) modalRef.value.close();
};

const buyTag = () => {
  if (wallet.balance < tag.value.price) {
    alert('Insufficient wallet balance — top up first');
    return;
  }
  wallet.balance -= tag.value.price;
  alert(isEvent.value ? `🎟️ Ticket secured · ${tag.value.title}` : `✅ Bought ${tag.value.title} · 🔒 escrow-protected`);
  close();
};

const viewEvent = () => {
  close();
  router.visit('/new_frontend/events');
};

const addToCart = () => {
  showToast('🛒 Added to cart');
  close();
};
const showToast = (msg) => {
  if (window.toast) window.toast(msg);
};
defineExpose({ open, close });
</script>
