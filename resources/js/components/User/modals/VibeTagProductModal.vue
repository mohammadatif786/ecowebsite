<template>
  <Modal ref="modalRef" maxWidth="max-w-lg">
    <div class="flex items-center justify-between border-b p-4">
      <div>
        <h3 class="text-lg font-black leading-tight text-slate-800">Tag a product or event</h3>
        <p class="text-[11px] font-bold text-slate-400">Viewers can buy / get tickets without leaving</p>
      </div>
      <button @click="close" class="text-slate-400 hover:text-slate-600 transition">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <div class="p-4 space-y-4">
      <!-- Tabs -->
      <div class="flex bg-slate-100 p-1 rounded-2xl">
        <button
          v-for="t in tabs"
          :key="t.id"
          @click="activeTab = t.id"
          class="flex-1 py-2.5 rounded-xl font-black text-sm transition-all flex items-center justify-center gap-2"
          :class="activeTab === t.id ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
        >
          <span>{{ t.icon }}</span>
          <span>{{ t.label }}</span>
        </button>
      </div>

      <!-- Search -->
      <div class="relative">
        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
        <input
          v-model="search"
          :placeholder="activeTab === 'marketplace' ? 'Search the Marketplace...' : 'Search events...'"
          class="w-full bg-slate-50 border-none rounded-2xl py-3 pl-10 pr-4 text-sm font-semibold outline-none focus:ring-2 focus:ring-lkblue/20 transition"
        />
      </div>

      <!-- List -->
      <div class="space-y-2 max-h-[50vh] overflow-y-auto pr-1 custom-scrollbar">
        <div
          v-for="item in filteredItems"
          :key="item.id + item.kind"
          class="flex items-center gap-3 p-2.5 rounded-2xl border border-slate-100 hover:border-blue-100 hover:bg-blue-50/20 transition group"
        >
          <img :src="item.image || 'https://picsum.photos/200'" class="w-14 h-14 rounded-xl object-cover shadow-sm shrink-0" />

          <div class="flex-1 min-w-0">
            <h4 class="font-black text-sm text-slate-800 truncate">{{ item.title }}</h4>
            <p class="text-[11px] font-bold text-slate-400 truncate">
              <template v-if="item.kind === 'event'">
                {{ fmtDate(item.date) }} · {{ item.location }}
              </template>
              <template v-else>
                {{ item.seller }}
              </template>
            </p>
            <p class="text-[11px] font-black text-lkblue mt-0.5">
              {{ money(item.price) }} ·
              <span class="text-emerald-600">{{ commLabel(item) }}</span>
            </p>
          </div>

          <button
            @click="selectItem(item)"
            class="w-8 h-8 rounded-full border-2 border-lkblue/20 text-lkblue flex items-center justify-center hover:bg-lkblue hover:text-white hover:border-lkblue transition group-hover:scale-110"
          >
            <i data-lucide="plus" class="w-5 h-5"></i>
          </button>
        </div>

        <div v-if="!filteredItems.length" class="text-center py-12">
          <i data-lucide="package-search" class="w-12 h-12 text-slate-200 mx-auto mb-3"></i>
          <p class="text-sm font-bold text-slate-400">No {{ activeTab === 'marketplace' ? 'products' : 'events' }} found</p>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import Modal from '../ui/Modal.vue';

const props = defineProps({
  items: { type: Array, default: () => [] }
});

const emit = defineEmits(['selected']);

const modalRef = ref();
const activeTab = ref('marketplace');
const search = ref('');

const tabs = [
  { id: 'marketplace', label: 'Marketplace', icon: '🛍️' },
  { id: 'events', label: 'LinkUp Events', icon: '🎟️' }
];

const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const fmtDate = (d) => {
  if (!d) return '';
  const MONTHS = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
  const dt = new Date(d);
  return MONTHS[dt.getMonth()] + ' ' + dt.getDate();
};

const commMode = (t) => { if (!t) return 'none'; if (t.commMode) return t.commMode; return (t.commission > 0) ? 'pct' : 'none'; };
const tagCommAmt = (t) => { const m = commMode(t); if (m === 'none') return 0; if (m === 'flat') return +(t.commFlat || 0); return +(((t.price || 0) * (t.commission || 0)) / 100).toFixed(2); };
const commLabel = (t) => { const m = commMode(t); if (m === 'none') return 'no commission'; if (m === 'flat') return 'you earn ' + money(t.commFlat || 0) + ' per sale'; return 'you earn ' + (t.commission || 0) + '% · ' + money(tagCommAmt(t)) + ' per sale'; };

const filteredItems = computed(() => {
  const kind = activeTab.value === 'marketplace' ? 'product' : 'event';
  let list = props.items.filter(i => i.kind === kind);

  if (search.value.trim()) {
    const q = search.value.toLowerCase();
    list = list.filter(i => i.title.toLowerCase().includes(q) || (i.seller && i.seller.toLowerCase().includes(q)));
  }

  return list;
});

const selectItem = (item) => {
  emit('selected', item);
  close();
};

const open = () => {
  modalRef.value?.open();
  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
  });
};

const close = () => modalRef.value?.close();

defineExpose({ open, close });
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
</style>
