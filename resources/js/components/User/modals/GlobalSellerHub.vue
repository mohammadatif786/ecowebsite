<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const props = defineProps({
    activeTab: String
});

const emit = defineEmits(['back', 'refresh']);

const page = usePage();
const sellerTab = ref('overview');
const sellerTabs = [
    { id: 'overview', label: 'Overview' },
    { id: 'inventory', label: 'Inventory' },
    { id: 'orders', label: 'Orders' }
];

const sellerHub = computed(() => page.props.createModalData?.sellerHub || { revenue: 0, unitsSold: 0, orderCount: 0, inventoryCount: 0, earnings: {}, orders: [] });
const userProducts = computed(() => page.props.createModalData?.userProducts || []);

const refreshIcons = () => {
    nextTick(() => {
        if (window.lucide) window.lucide.createIcons();
    });
};

onMounted(() => {
    refreshIcons();
});
</script>

<template>
    <div class="space-y-4" v-if="activeTab === 'seller'">
        <div class="flex gap-2 p-1 bg-slate-100 rounded-full">
            <button v-for="t in sellerTabs" :key="t.id" @click="sellerTab = t.id"
                :class="['flex-1 rounded-full py-2 text-xs font-black transition', sellerTab === t.id ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']">
                {{ t.label }}
            </button>
        </div>

        <!-- OVERVIEW -->
        <template v-if="sellerTab === 'overview'">
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Revenue</p>
                    <p class="text-xl font-black text-slate-800 mt-1">{{ money(sellerHub.revenue) }}</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Units Sold</p>
                    <p class="text-xl font-black text-slate-800 mt-1">{{ sellerHub.unitsSold }}</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Orders</p>
                    <p class="text-xl font-black text-slate-800 mt-1">{{ sellerHub.orderCount }}</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">In Stock</p>
                    <p class="text-xl font-black text-slate-800 mt-1">{{ sellerHub.inventoryCount }}</p>
                </div>
            </div>

            <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Available to transfer</p>
                    <p class="text-2xl font-black text-emerald-700 mt-1">{{ money(sellerHub.earnings?.available || 0) }}</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
        </template>

        <!-- INVENTORY -->
        <template v-else-if="sellerTab === 'inventory'">
            <div class="space-y-2 max-h-[50vh] overflow-y-auto pr-1 custom-scrollbar">
                <div v-for="p in userProducts" :key="p.id" class="flex items-center gap-3 rounded-2xl border border-slate-100 p-2.5 hover:bg-slate-50 transition">
                    <img :src="p.cover_image || p.image_url || 'https://picsum.photos/200'" class="h-12 w-12 rounded-xl object-cover shadow-sm"/>
                    <div class="flex-1 min-w-0">
                        <p class="font-black text-sm truncate text-slate-800">{{ p.name }}</p>
                        <p class="text-[11px] text-slate-500 font-bold">{{ money(p.price) }} · {{ p.category?.name || 'Other' }}</p>
                    </div>
                    <span :class="['px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider', p.qty > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600']">
                        {{ p.qty > 0 ? p.qty + ' left' : 'Sold out' }}
                    </span>
                </div>
            </div>
        </template>

        <!-- ORDERS -->
        <template v-else-if="sellerTab === 'orders'">
            <div class="space-y-2 max-h-[50vh] overflow-y-auto pr-1 custom-scrollbar">
                <p v-if="!sellerHub.orders?.length" class="text-center text-slate-400 text-sm py-12 font-bold italic">No orders yet.</p>
                <div v-for="o in sellerHub.orders" :key="o.id" class="p-4 rounded-2xl border border-slate-100 bg-white hover:border-blue-100 transition shadow-sm">
                    <div class="flex items-center justify-between mb-1">
                        <p class="font-black text-sm text-slate-800">{{ o.id }}</p>
                        <span :class="['px-2 py-0.5 rounded-full text-[10px] font-black uppercase', o.status === 'Delivered' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700']">
                            {{ o.status }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 font-bold mb-2">{{ o.item }} · {{ o.buyer }}</p>
                    <div class="flex items-center justify-between border-t border-slate-50 pt-2">
                        <span class="text-[10px] font-black text-slate-400 uppercase">{{ o.units }} unit{{ o.units > 1 ? 's' : '' }}</span>
                        <span class="font-black text-slate-800">{{ money(o.total) }}</span>
                    </div>
                </div>
            </div>
        </template>

        <div class="pt-2">
            <button class="w-full rounded-2xl py-4 font-black border-2 border-slate-100 text-slate-500 hover:bg-slate-50 transition" @click="$emit('back')">
                ← Back to Menu
            </button>
        </div>
    </div>
</template>

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
