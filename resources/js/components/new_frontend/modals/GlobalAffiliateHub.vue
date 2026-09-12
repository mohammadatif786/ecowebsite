<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { DB } from '@/components/new_frontend/MockDataStore';

const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const props = defineProps({
    activeTab: String
});

const emit = defineEmits(['back']);

const page = usePage();
const affTab = ref('browse');
const affiliateTabs = [
    { id: 'browse', label: 'Promote' },
    { id: 'promos', label: 'My Promotions' },
    { id: 'earnings', label: 'Earnings' }
];

// Combine products and events for promotion
const affiliateItems = computed(() => {
    const products = (page.props.createModalData?.allProducts || []).map(p => ({ ...p, kind: 'product' }));
    const events = (page.props.createModalData?.allEvents || []).map(e => ({
        ...e,
        kind: 'event',
        seller: e.organizer,
        commission: 8 // Default event commission
    }));
    return [...products, ...events];
});

const promos = ref(DB.get('lk_affiliate_promos', []));

const isPromoting = (id) => promos.value.some(p => String(p.id) === String(id));

const togglePromote = (p) => {
    if (isPromoting(p.id)) {
        promos.value = promos.value.filter(x => String(x.id) !== String(p.id));
    } else {
        promos.value.unshift({ ...p });
    }
    DB.set('lk_affiliate_promos', promos.value);
    if (window.toast) window.toast(isPromoting(p.id) ? `🤝 Promoting ${p.title}` : 'Removed from promotions');
};

const removePromo = (id) => {
    promos.value = promos.value.filter(p => String(p.id) !== String(id));
    DB.set('lk_affiliate_promos', promos.value);
};

const affEarningsDB = computed(() => DB.get('lk_affiliate_earnings', { pending: 0, available: 0, paid: 0, clicks: 0, sales: [] }));
const affPending = computed(() => affEarningsDB.value.pending);
const affAvailable = computed(() => affEarningsDB.value.available);
const affSales = computed(() => affEarningsDB.value.sales || []);
const affEarningsTotal = computed(() => affPending.value + affAvailable.value + (affEarningsDB.value.paid || 0));

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
    <div class="space-y-4" v-if="activeTab === 'affiliate'">
        <div class="flex gap-2 p-1 bg-slate-100 rounded-full">
            <button v-for="t in affiliateTabs" :key="t.id" @click="affTab = t.id"
                :class="['flex-1 rounded-full py-2 text-xs font-black transition', affTab === t.id ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']">
                {{ t.label }}
            </button>
        </div>

        <!-- BROWSE tab -->
        <template v-if="affTab === 'browse'">
            <p class="text-[12px] text-slate-500 mb-3 px-1 leading-relaxed">
                Tag any of these on your Live or Vibes — you earn what the seller/organizer is offering, on every sale you drive.
            </p>
            <div class="space-y-2 max-h-[50vh] overflow-y-auto pr-1 custom-scrollbar">
                <div v-for="p in affiliateItems" :key="p.id + p.kind" class="flex items-center gap-3 rounded-2xl border border-slate-100 p-2.5 hover:border-blue-100 hover:bg-blue-50/30 transition group">
                    <div class="relative shrink-0">
                        <img :src="p.image || 'https://picsum.photos/200'" class="h-14 w-14 rounded-xl object-cover shadow-sm"/>
                        <span class="absolute -top-1 -left-1 text-[10px] p-1 rounded-full bg-white shadow-sm border border-slate-100">
                            {{ p.kind === 'product' ? '🛍️' : '🎟️' }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-black text-sm truncate text-slate-800">{{ p.title }}</p>
                        <p class="text-[11px] text-slate-500 truncate font-bold">{{ p.seller }} · {{ money(p.price) }}</p>
                        <p class="text-[11px] font-black text-emerald-600 mt-0.5">
                            {{ p.commission }}% commission · earn {{ money((p.price * p.commission) / 100) }}
                        </p>
                    </div>
                    <button @click="togglePromote(p)" :class="['btn px-4 py-2 text-[11px] font-black rounded-xl transition shrink-0', isPromoting(p.id) ? 'bg-slate-100 text-slate-500' : 'bg-blue-600 text-white shadow-md shadow-blue-200']">
                        {{ isPromoting(p.id) ? 'Promoting ✓' : 'Promote' }}
                    </button>
                </div>
            </div>
        </template>

        <!-- MY PROMOTIONS tab -->
        <template v-else-if="affTab === 'promos'">
            <div v-if="promos.length" class="space-y-2 max-h-[50vh] overflow-y-auto pr-1 custom-scrollbar">
                <div v-for="p in promos" :key="p.id" class="flex items-center gap-3 rounded-2xl border border-slate-100 p-2.5">
                    <img :src="p.image || 'https://picsum.photos/200'" class="h-14 w-14 rounded-xl object-cover"/>
                    <div class="flex-1 min-w-0">
                        <p class="font-black text-sm truncate">{{ p.title }}</p>
                        <p class="text-[11px] text-emerald-600 font-black">{{ p.commission }}% · earn {{ money((p.price * p.commission) / 100) }}</p>
                    </div>
                    <button @click="removePromo(p.id)" class="px-3 py-2 text-[11px] font-black text-rose-500 hover:bg-rose-50 rounded-xl transition">
                        Remove
                    </button>
                </div>
            </div>
            <div v-else class="p-12 text-center text-slate-400 font-bold border-2 border-dashed border-slate-100 rounded-3xl">
                No promotions yet. Go to Promote and pick items to earn on.
            </div>
        </template>

        <!-- EARNINGS tab -->
        <template v-else>
            <div class="grid grid-cols-2 gap-3 mb-2">
                <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100">
                    <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Total Earned</p>
                    <p class="text-2xl font-black text-blue-600 mt-1">{{ money(affEarningsTotal) }}</p>
                </div>
                <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100">
                    <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">Available</p>
                    <p class="text-2xl font-black text-emerald-600 mt-1">{{ money(affAvailable) }}</p>
                </div>
            </div>

            <p class="font-black text-xs text-slate-500 uppercase tracking-wider mb-2 px-1">Recent Activity</p>
            <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1 custom-scrollbar">
                <p v-if="!affSales.length" class="text-center text-slate-400 text-sm py-8 font-bold italic">No commissions yet — tag an item on your stream to start selling.</p>
                <div v-for="s in affSales" :key="s.title" class="flex items-center justify-between border border-slate-50 rounded-xl p-3 text-sm hover:bg-slate-50 transition">
                    <div class="min-w-0">
                        <p class="font-bold truncate text-slate-700">{{ s.title }}</p>
                        <p class="text-[11px] text-slate-400 font-medium">{{ money(s.amount) }} · {{ s.rate }}%</p>
                    </div>
                    <span class="font-black text-emerald-600 shrink-0">+{{ money(s.commission) }}</span>
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
