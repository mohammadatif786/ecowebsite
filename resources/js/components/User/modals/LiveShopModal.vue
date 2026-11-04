<template>
  <Modal ref="modalRef" maxWidth="max-w-xl">
    <div class="p-6 text-slate-900 bg-white rounded-3xl overflow-hidden shadow-2xl">
      <header class="flex items-start justify-between mb-4">
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-2xl font-black flex items-center gap-2">
              <span class="text-2xl">🛍️</span> Live Shop
            </h2>
          </div>
          <p class="text-[11px] font-bold text-slate-400 mt-1 flex items-center gap-1.5">
            <span class="w-3.5 h-3.5 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
              <i data-lucide="check" class="w-2.5 h-2.5"></i>
            </span>
            Escrow-protected · paid to seller after delivery · you earn commission
          </p>
        </div>
        <button @click="close" class="p-1 text-slate-400 hover:text-slate-600 transition-colors">
          <X class="w-6 h-6" />
        </button>
      </header>

      <div class="flex items-center justify-between mb-4">
        <p class="text-xs font-black text-slate-500 uppercase tracking-tight">{{ products.length }} items featured</p>
        <button v-if="canManage" @click="$emit('add')" class="rounded-full bg-blue-600 px-3 py-1.5 text-xs font-black text-white transition hover:bg-blue-500">
          + Add product
        </button>
        <p v-else class="text-xs font-black text-emerald-600 flex items-center gap-1">
          {{ totalSold }} sold this live 🔥
        </p>
      </div>

      <div v-if="products.length === 0" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-10 text-center">
        <p class="text-sm font-black text-slate-700">No products tagged yet</p>
        <button v-if="canManage" @click="$emit('add')" class="mt-3 rounded-xl bg-blue-600 px-4 py-2 text-xs font-black text-white">Tag a product</button>
      </div>

      <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1 hide-scroll">
        <div
          v-for="(p, idx) in products" :key="idx"
          :class="['p-3 rounded-2xl border transition-all duration-300',
                   featuredId === p.id ? 'bg-blue-50/50 border-blue-200' : 'bg-white border-slate-100']"
        >
          <div class="flex gap-4">
            <div class="relative w-20 h-20 shrink-0">
               <img :src="p.image || p.cover_image" class="w-full h-full object-cover rounded-xl shadow-sm" />
               <div v-if="p.kind === 'event'" class="absolute -top-1.5 -left-1.5 w-5 h-5 bg-blue-600 rounded-lg flex items-center justify-center border-2 border-white shadow-sm">
                 <span class="text-[8px] text-white font-black">🎟️</span>
               </div>
            </div>

            <div class="min-w-0 flex-1">
              <p class="font-black text-sm truncate text-slate-900">{{ p.title }}</p>
              <p class="text-[11px] font-bold text-slate-400 mt-0.5">{{ p.seller || 'Individual Seller' }}</p>
              <p class="text-base font-black text-slate-900 mt-1">${{ Number(p.price).toFixed(2) }}</p>
              <div class="flex items-center gap-2 mt-1">
                <span v-if="p.left" class="text-[10px] font-black text-rose-500">{{ p.left }} left</span>
                <span v-if="p.sold" class="text-[10px] font-black text-emerald-600">{{ p.sold }} sold</span>
                <span class="text-[10px] font-bold text-slate-400">· you earn {{ p.commission || '5.00/sale' }}{{ typeof p.commission === 'number' ? '%' : '' }}</span>
              </div>
            </div>

            <div v-if="canManage" class="flex flex-col gap-2 shrink-0">
              <button
                @click="feature(p)"
                :class="['px-4 py-2 rounded-xl text-xs font-black transition-all shadow-sm',
                         featuredId === p.id ? 'bg-blue-600 text-white shadow-blue-200' : 'bg-white border border-slate-200 text-slate-900 hover:bg-slate-50']"
              >
                {{ featuredId === p.id ? 'Featured' : 'Feature' }}
              </button>
              <button @click="remove(idx)" class="px-4 py-2 rounded-xl text-xs font-black text-rose-500 bg-white border border-rose-100 hover:bg-rose-50 transition-all shadow-sm">
                Remove
              </button>
            </div>
            <div v-else class="flex shrink-0 flex-col gap-2">
              <button
                @click="buyNow(p)"
                class="rounded-xl bg-blue-500 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-blue-600"
              >
                Buy now
              </button>
              <button
                @click="addToCart(p)"
                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-900 transition hover:bg-slate-50"
              >
                {{ cartItemIds.includes(itemKey(p)) ? 'Added' : 'Add' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { X } from 'lucide-vue-next';
import Modal from '../ui/Modal.vue';

const props = defineProps({
  canManage: { type: Boolean, default: false },
});

const emit = defineEmits(['feature', 'remove', 'buy', 'add']);

const modalRef = ref(null);
const products = ref([]);
const featuredId = ref(null);
const cartItemIds = ref([]);

const itemKey = (product) => `${product.kind || 'product'}-${product.id}`;

const totalSold = computed(() => {
  return products.value.reduce((sum, p) => sum + (Number(p.sold) || 0), 0);
});

const open = (list, currentFeaturedId) => {
  products.value = [...list];
  featuredId.value = currentFeaturedId;
  if (modalRef.value) {
    modalRef.value.open();
    nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
  }
};

const buyNow = (product) => {
  emit('buy', product);
};

const addToCart = (product) => {
  const key = itemKey(product);
  if (!cartItemIds.value.includes(key)) {
    cartItemIds.value.push(key);
    emit('add', product);
  }
};

const close = () => {
  if (modalRef.value) modalRef.value.close();
};

const feature = (p) => {
  featuredId.value = p.id;
  emit('feature', p);
};

const remove = (idx) => {
  const p = products.value[idx];
  products.value.splice(idx, 1);
  emit('remove', p.id);
  if (featuredId.value === p.id) {
    featuredId.value = products.value[0]?.id || null;
    emit('feature', products.value[0] || null);
  }
};

defineExpose({ open, close });
</script>
