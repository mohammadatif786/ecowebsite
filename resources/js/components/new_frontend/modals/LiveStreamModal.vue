<template>
  <Modal ref="modalRef">
    <div v-if="session" class="-m-1">
      <!-- Upcoming session -->
      <template v-if="session.status !== 'live'">
        <div class="relative h-56">
          <img :src="session.thumb" class="w-full h-full object-cover rounded-t-[20px]" />
          <button @click="close" class="absolute top-3 right-3 w-9 h-9 rounded-full bg-black/50 text-white grid place-items-center">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
          <span class="absolute top-3 left-3 bg-slate-800 text-white text-xs font-black px-2.5 py-1 rounded-full">Upcoming</span>
        </div>
        <div class="p-5">
          <h3 class="text-xl font-black">{{ session.title }}</h3>
          <p class="text-slate-500 font-semibold">{{ session.host }} · {{ session.category }}</p>
          <p class="mt-3 font-bold text-slate-600">Starts {{ session.when }}</p>
          <button @click="remindMe" class="btn btn-primary w-full mt-3 py-3">Remind me</button>
        </div>
      </template>

      <!-- Live session -->
      <template v-else>
        <div class="relative h-56">
          <img :src="session.thumb" class="w-full h-full object-cover rounded-t-[20px]" />
          <button @click="close" class="absolute top-3 right-3 w-9 h-9 rounded-full bg-black/50 text-white grid place-items-center">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
          <span class="absolute top-3 left-3 bg-red-600 text-white text-xs font-black px-2.5 py-1 rounded-full flex items-center gap-1">
            <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>LIVE
          </span>
          <div class="absolute bottom-3 left-3 right-3 text-white">
            <p class="font-black text-lg">{{ session.title }}</p>
            <div class="flex items-center gap-3 mt-1 text-xs font-bold">
              <span class="flex items-center gap-1"><i data-lucide="users" class="w-3 h-3"></i>{{ num(session.viewers) }}</span>
              <span class="flex items-center gap-1"><i data-lucide="heart" class="w-3 h-3"></i>{{ session.likes }}</span>
              <span class="flex items-center gap-1"><i data-lucide="gift" class="w-3 h-3"></i>{{ session.gifts }}</span>
            </div>
          </div>
        </div>
        <div class="p-5">
          <p class="text-slate-500 font-semibold mb-3">{{ session.host }} · {{ session.category }}</p>

          <!-- Shoppable products -->
          <div v-if="session.products && session.products.length" class="mb-4">
            <p class="font-black text-sm mb-2 flex items-center gap-1.5"><i data-lucide="shopping-bag" class="w-4 h-4"></i>Shop this live</p>
            <div class="space-y-2">
              <div v-for="p in session.products" :key="p.id" class="flex items-center gap-3 rounded-2xl border border-slate-100 p-2">
                <img :src="p.image" class="h-12 w-12 rounded-xl object-cover shrink-0" />
                <div class="flex-1 min-w-0">
                  <p class="font-black text-sm truncate">{{ p.title }}</p>
                  <p class="text-xs text-slate-500">{{ money(p.price) }}</p>
                </div>
                <button @click="showToast('🛒 Added to cart')" class="btn btn-primary px-3 py-1.5 text-xs shrink-0">
                  {{ p.kind === 'event' ? 'Get Tickets' : 'Shop' }}
                </button>
              </div>
            </div>
          </div>

          <div class="flex gap-2">
            <button @click="sendGift" class="btn btn-ghost flex-1 py-3 flex items-center justify-center gap-2">
              <i data-lucide="gift" class="w-4 h-4"></i>Send Gift
            </button>
            <button @click="showToast('❤️ Liked!')" class="btn btn-ghost flex-1 py-3 flex items-center justify-center gap-2">
              <i data-lucide="heart" class="w-4 h-4"></i>Like
            </button>
          </div>
        </div>
      </template>
    </div>
  </Modal>
</template>

<script setup>
import { ref } from 'vue';
import Modal from '../ui/Modal.vue';

const modalRef = ref(null);
const session = ref(null);

const num = (n) => Number(n).toLocaleString();
const money = (n) => '$' + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const showToast = (msg) => { if (window.toast) window.toast(msg); };

const open = (s) => {
  session.value = s;
  if (modalRef.value) {
    modalRef.value.open();
    if (window.lucide) setTimeout(() => lucide.createIcons(), 50);
  }
};

const close = () => { if (modalRef.value) modalRef.value.close(); };

const remindMe = () => {
  showToast('🔔 Reminder set');
  close();
};

const sendGift = () => {
  showToast('🎁 Gift sent!');
};

defineExpose({ open, close });
</script>
