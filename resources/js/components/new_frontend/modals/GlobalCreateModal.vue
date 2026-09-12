<script setup>
import { ref, computed, h, onMounted, nextTick, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import StoreTab from '@/pages/User/LinkUpShop/Products/Components/tabs/StoreTab.vue';
import StoreCarnival from '@/pages/User/LinkUpShop/Products/Components/tabs/StoreCarnival.vue';
import ProductTab from '@/pages/User/LinkUpShop/Products/Components/tabs/Product.vue';
import GlobalAffiliateHub from './GlobalAffiliateHub.vue';
import GlobalSellerHub from './GlobalSellerHub.vue';

const props = defineProps({
    isOpen: Boolean
});

const emit = defineEmits(['close']);

const page = usePage();
const activeCreateTab = ref('');

// Data for the tabs
const merchants = computed(() => page.props.createModalData?.merchants || []);
const categories = computed(() => page.props.createModalData?.categories || []);
const userProducts = computed(() => page.props.createModalData?.userProducts || []);

const close = () => {
    activeCreateTab.value = '';
    emit('close');
};

const openCreateStore = () => { activeCreateTab.value = 'store'; };
const openCreateGroup = () => { activeCreateTab.value = 'group'; };
const openAddProduct = () => { activeCreateTab.value = 'product'; };
const openAffiliateHub = () => { activeCreateTab.value = 'affiliate'; };

const openSellerHub = () => { activeCreateTab.value = 'seller'; };

const refresh = () => {
    router.reload({
        only: ['createModalData'],
        preserveScroll: true,
        preserveState: true
    });
};

const refreshIcons = () => {
    nextTick(() => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
};

// --- INLINE COMPONENT ---
const CreateCard = (props, { emit }) => {
  return h('button', {
    onClick: () => emit('click'),
    class: 'w-full text-left rounded-2xl border border-slate-100 p-4 hover:border-blue-200 hover:bg-slate-50/50 flex items-center gap-4 transition group'
  }, [
    h('span', {
      class: 'h-14 w-14 rounded-2xl grid place-items-center text-white shrink-0 shadow-sm transition group-hover:scale-105',
      style: 'background:linear-gradient(135deg, #3b82f6 0%, #10b981 100%)'
    }, [h('i', { 'data-lucide': props.icon, class: 'w-6 h-6' })]),
    h('div', { class: 'flex-1 min-w-0' }, [
      h('p', { class: 'text-base font-black text-slate-800' }, props.title),
      h('p', { class: 'text-xs font-bold text-slate-400 mt-0.5' }, props.desc)
    ])
  ]);
};
CreateCard.props = ['icon', 'title', 'desc'];
CreateCard.emits = ['click'];

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        refreshIcons();
    }
});

onMounted(() => {
    refreshIcons();
});
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center p-4" @click.self="close">
      <div :class="['bg-white rounded-3xl w-full shadow-2xl overflow-hidden flex flex-col transition-all duration-300', activeCreateTab ? 'max-w-xl max-h-[90vh]' : 'max-w-md']">
        <!-- Header -->
        <div class="p-4 text-white flex items-center justify-between shrink-0" style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%)">
          <div class="flex items-center gap-3">
            <span class="h-11 w-11 rounded-xl grid place-items-center bg-white/20">
              <i :data-lucide="activeCreateTab === 'store' ? 'store' : activeCreateTab === 'group' ? 'users' : activeCreateTab === 'product' ? 'package-plus' : activeCreateTab === 'affiliate' ? 'handshake' : activeCreateTab === 'seller' ? 'bar-chart-2' : 'plus'" class="w-6 h-6"></i>
            </span>
            <div>
              <h3 class="text-xl font-black leading-tight">
                {{ activeCreateTab === 'store' ? 'Create Store' : activeCreateTab === 'group' ? 'Create Carnival Group / Band' : activeCreateTab === 'product' ? 'Add Product / Costume' : activeCreateTab === 'affiliate' ? 'Promote & Earn' : activeCreateTab === 'seller' ? 'Seller Hub' : 'Create on LinkUp' }}
              </h3>
              <p v-if="activeCreateTab" class="text-white/80 text-[11px] font-bold">
                {{ activeCreateTab === 'store' ? 'Stores offer pickup and/or delivery (inherited by products).' : activeCreateTab === 'group' ? 'Organize costumes, sections and pickup for your band.' : activeCreateTab === 'product' ? 'List individual items, store stock, or carnival costumes.' : activeCreateTab === 'affiliate' ? 'Sell for any store — earn on every sale you drive.' : activeCreateTab === 'seller' ? 'Manage your stores, products & orders.' : '' }}
              </p>
            </div>
          </div>
          <button @click="close" class="h-9 w-9 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <div class="p-4 overflow-y-auto hide-scroll">
          <!-- Card List -->
          <div class="space-y-3" v-if="!activeCreateTab">
            <CreateCard icon="store" title="Create Store" desc="Sell products with pickup & delivery options" @click="openCreateStore" />
            <CreateCard icon="users" title="Create Carnival Group / Band" desc="Sell costumes & sections (frontline, backline…)" @click="openCreateGroup" />
            <CreateCard icon="package-plus" title="Add Product / Costume" desc="List an item for sale in the Marketplace" @click="openAddProduct" />
            <CreateCard icon="handshake" title="Promote & Earn (Affiliate)" desc="No products? Promote any store & earn commission" @click="openAffiliateHub" />
            <CreateCard icon="bar-chart-2" title="Seller Hub" desc="Manage your stores, products & orders" @click="openSellerHub" />
          </div>

          <!-- Tabs (Shown when activeCreateTab is set) -->
          <div v-else class="space-y-4">
             <StoreTab :activeTab="activeCreateTab" :merchants="merchants" @back="activeCreateTab = ''" @refresh="refresh" />
             <StoreCarnival :activeTab="activeCreateTab" :merchants="merchants" @back="activeCreateTab = ''" @refresh="refresh" />
             <ProductTab :activeTab="activeCreateTab" :merchants="merchants" :categories="categories" :userProducts="userProducts"
                @back="activeCreateTab = ''" @refresh="refresh" @close="close" />
             <GlobalAffiliateHub :activeTab="activeCreateTab" @back="activeCreateTab = ''" />
             <GlobalSellerHub :activeTab="activeCreateTab" @back="activeCreateTab = ''" @refresh="refresh" />
          </div>
        </div>
      </div>
    </div>
</template>
