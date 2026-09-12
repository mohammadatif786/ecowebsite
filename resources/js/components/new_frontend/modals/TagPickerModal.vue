<template>
  <Modal ref="modalRef" maxWidth="max-w-[480px]">
    <div class="p-6 text-slate-900 bg-white rounded-3xl overflow-hidden shadow-2xl">
      <header class="flex items-start justify-between mb-5">
        <div>
          <h2 class="text-2xl font-black">Tag a product or event</h2>
          <p class="text-sm font-bold text-slate-400 mt-1">
            Viewers can buy / get tickets without leaving
          </p>
        </div>
        <button @click="close" class="p-1 text-slate-400 hover:text-slate-600 transition-colors">
          <i data-lucide="x" class="w-6 h-6"></i>
        </button>
      </header>

      <!-- Tabs -->
      <div class="flex p-1.5 bg-slate-100 rounded-[20px] mb-5">
        <button
          @click="activeTab = 'marketplace'"
          :class="['flex-1 flex items-center justify-center gap-2 py-3 rounded-[16px] text-[13px] font-black transition-all',
                    activeTab === 'marketplace' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700']"
        >
          <span>🛍️</span> Marketplace
        </button>
        <button
          @click="activeTab = 'events'"
          :class="['flex-1 flex items-center justify-center gap-2 py-3 rounded-[16px] text-[13px] font-black transition-all',
                    activeTab === 'events' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700']"
        >
          <span>💼</span> LinkUp Events
        </button>
      </div>

      <!-- Search -->
      <div class="relative mb-6">
        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
        <input
          v-model="searchQuery"
          :placeholder="activeTab === 'marketplace' ? 'Search the Marketplace...' : 'Search Events...'"
          class="w-full bg-slate-50 border-slate-100 rounded-2xl pl-11 pr-4 py-3.5 text-[13px] font-bold text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none"
        />
      </div>

      <!-- List -->
      <div class="space-y-3 max-h-[50vh] overflow-y-auto pr-1 hide-scroll">
        <div v-if="isLoading" class="py-12 text-center text-sm font-bold text-slate-400">Loading products and events...</div>
        <div v-else v-for="item in filteredItems" :key="`${item.kind}-${item.id}`" class="p-3 rounded-2xl border border-slate-50 bg-slate-50/30 hover:bg-slate-50 transition-all">
          <div class="flex items-center gap-4">
            <img :src="item.image || item.cover_image" class="w-16 h-16 rounded-xl object-cover shadow-sm shrink-0" />
            <div class="min-w-0 flex-1">
              <p class="font-black text-[13px] text-slate-900 truncate leading-tight">{{ item.title }}</p>
              <p class="text-[11px] font-bold text-slate-400 mt-1">{{ item.seller || item.organizer_name || 'LinkUp Seller' }}</p>
              <p class="text-[11px] font-black mt-1.5">
                <span class="text-blue-600">${{ Number(item.price).toFixed(2) }}</span>
                <span class="text-slate-400 mx-1">·</span>
                <span class="text-slate-700">you earn {{ item.commission || 10 }}%</span>
                <span class="text-slate-400 mx-1">·</span>
                <span class="text-slate-500 font-bold">${{ ((item.price * (item.commission || 10)) / 100).toFixed(2) }} per sale</span>
              </p>
            </div>
            <button @click="pick(item)" class="w-9 h-9 rounded-full border-2 border-blue-500 flex items-center justify-center text-blue-500 hover:bg-blue-500 hover:text-white transition-all shadow-sm shrink-0">
               <i data-lucide="plus" class="w-5 h-5"></i>
            </button>
          </div>
        </div>

        <div v-if="!isLoading && filteredItems.length === 0" class="text-center py-12 opacity-40">
           <i data-lucide="search-x" class="w-12 h-12 mx-auto mb-3"></i>
           <p class="text-sm font-bold">No {{ activeTab === 'marketplace' ? 'products' : 'events' }} found</p>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Modal from '../ui/Modal.vue';
import { SEED } from '../MockDataStore';

const emit = defineEmits(['pick']);

const modalRef = ref(null);
const activeTab = ref('marketplace');
const searchQuery = ref('');
const isLoading = ref(false);
const page = usePage();

// Generate some more robust mock data matching the screenshot
const mockProducts = [
  { id: 'm1', kind: 'product', title: 'Utopia Bedding Bed Pillows for Sleeping Queen Size...', seller: 'Individual Seller', price: 28.00, commission: 12, image: 'https://m.media-amazon.com/images/I/71rIId8eWDL._AC_SL1500_.jpg' },
  { id: 'm2', kind: 'product', title: 'Dolce & Gabbana My Sicily Handbag in Burgundy Le...', seller: 'The Stop & Shop', price: 1245.00, commission: 8, image: 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?q=80&w=500' },
  { id: 'm3', kind: 'product', title: 'All-Weather HDPE Folding Adirondack Chair Fire Pit...', seller: 'The Stop & Shop', price: 99.00, commission: 10, image: 'https://images.unsplash.com/photo-1591129841117-3adfd313e34f?q=80&w=500' },
  { id: 'm4', kind: 'product', title: '2025 Topps Museum Chris Sale #31/199', seller: 'The Stop & Shop', price: 30.00, commission: 10, image: 'https://images.unsplash.com/photo-1519750783826-e2420f4d687f?q=80&w=500' },
  { id: 'm5', kind: 'product', title: 'Handmade Beaded Carnival Earrings', seller: 'Kay Designs', price: 18.00, commission: 15, image: 'https://images.unsplash.com/photo-1535633302704-b02923659b38?q=80&w=500' },
  { id: 'm6', kind: 'product', title: 'Carnival Costume Deposit — Frontline', seller: 'Carnival Collective', price: 450.00, commission: 10, image: 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?q=80&w=500' },
];

const mockEvents = (SEED.upcoming_events || []).map(e => ({
  id: 'e' + e.id,
  kind: 'event',
  title: e.title,
  organizer_name: e.location || 'Event Organizer',
  price: e.price || 45,
  commission: 10,
  image: e.image
}));

const marketplaceProducts = computed(() => (page.props.createModalData?.allProducts || []).map((product) => ({
  ...product,
  kind: 'product',
  commission: product.commission ?? 10,
})));

const liveEvents = computed(() => (page.props.createModalData?.allEvents || []).map((event) => ({
  ...event,
  kind: 'event',
  seller: event.organizer || event.organizer_name || 'Event Organizer',
  commission: event.commission ?? 10,
})));

const filteredItems = computed(() => {
  const list = activeTab.value === 'marketplace' ? marketplaceProducts.value : liveEvents.value;
  const q = searchQuery.value.toLowerCase().trim();
  if (!q) return list;
  return list.filter(i => i.title.toLowerCase().includes(q) || (i.seller && i.seller.toLowerCase().includes(q)));
});

const open = () => {
  searchQuery.value = '';
  isLoading.value = true;
  router.reload({
    only: ['createModalData'],
    preserveScroll: true,
    preserveState: true,
    onFinish: () => {
      isLoading.value = false;
      modalRef.value?.open?.();
      nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
    },
  });
};

const close = () => {
  if (modalRef.value) modalRef.value.close();
};

const pick = (item) => {
  emit('pick', item);
  close();
};

defineExpose({ open, close });
</script>
