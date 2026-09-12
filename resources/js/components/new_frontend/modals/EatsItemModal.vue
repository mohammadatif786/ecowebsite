<template>
  <Modal ref="modalRef" maxWidth="max-w-lg">
    <div v-if="item" class="-m-1">
      <div class="h-48 relative">
        <img :src="item.img" class="w-full h-full object-cover rounded-t-[20px]" />
        <button @click="close" class="absolute top-3 right-3 w-9 h-9 rounded-full bg-black/50 text-white grid place-items-center">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <div class="p-5">
        <div class="flex items-start justify-between gap-2">
          <h3 class="text-xl font-black">{{ item.n }}</h3>
          <p class="text-xl font-black text-lkblue2">{{ money(item.p) }}</p>
        </div>
        <p class="text-slate-500 mt-1">{{ item.d || '' }}</p>
        
        <label class="text-xs font-black mt-4 block">Special instructions</label>
        <textarea v-model="notes" rows="2" placeholder="e.g. extra pepper, no onion" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-lkblue"></textarea>
        
        <div class="flex items-center justify-between mt-4">
          <div class="flex items-center gap-3">
            <button @click="qty = Math.max(1, qty - 1)" class="h-10 w-10 rounded-full btn-ghost grid place-items-center">
              <i data-lucide="minus" class="w-4 h-4"></i>
            </button>
            <span class="font-black text-lg w-6 text-center">{{ qty }}</span>
            <button @click="qty++" class="h-10 w-10 rounded-full btn-ghost grid place-items-center">
              <i data-lucide="plus" class="w-4 h-4"></i>
            </button>
          </div>
          <button @click="addItem" class="btn btn-primary px-6 py-3">
            Add to Cart · {{ money(item.p * qty) }}
          </button>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref } from 'vue';
import Modal from '../ui/Modal.vue';

const modalRef = ref(null);
const item = ref(null);
const qty = ref(1);
const notes = ref('');

const emit = defineEmits(['add']);

const money = (n) => '$' + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const open = (menuItem, restaurantId) => {
  // We need to attach an image if it doesn't have one (in original mock eatsItemImg generated it, we'll just use PIC)
  const imgUrl = `https://picsum.photos/seed/lk_${encodeURIComponent(menuItem.n)}/700/500`;
  
  item.value = {
    rid: restaurantId,
    n: menuItem.n,
    p: menuItem.p,
    d: menuItem.d,
    img: imgUrl
  };
  qty.value = 1;
  notes.value = '';
  
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

const addItem = () => {
  emit('add', { ...item.value, qty: qty.value, notes: notes.value });
  close();
};

defineExpose({ open, close });
</script>
