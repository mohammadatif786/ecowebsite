<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Search, Plus, BadgePercent } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const countries = [
    { country: 'Bahamas', region: 'Local', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000, wellness: 150000, cookouts: 90000, linkup360: 40000, coinsPurchased: 250000, coinsRedeemed: 90000 },
];

const handleFilterChange = () => {};

// Promotions State
const promotions = ref([
    { id: 1, name: '20% Off Lunch', restaurant: 'Bahama Grill', type: 'Discount', start: 'Jun 1', end: 'Jun 15', redemptions: 342, status: 'Active' },
    { id: 2, name: 'Free Delivery Friday', restaurant: 'Island Jerk Kitchen', type: 'Free Delivery', start: 'Jun 7', end: 'Jun 28', redemptions: 188, status: 'Scheduled' },
    { id: 3, name: 'Buy One Get One', restaurant: 'Trini Flavors', type: 'BOGO', start: 'Jun 2', end: 'Jun 9', redemptions: 91, status: 'Active' }
]);

const promoSearch = ref('');
const filteredPromotions = computed(() => {
    const q = promoSearch.value.toLowerCase().trim();
    return promotions.value.filter(p =>
        !q || [p.name, p.restaurant, p.type, p.status].join(' ').toLowerCase().includes(q)
    );
});

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Eats Promotions" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eatsPromotionsCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Eats Promotions" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-orange-600">Promotions</h3>
                        <p class="text-slate-500 font-medium">Create and manage discounts, BOGO, and free delivery offers.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <div class="relative">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <input v-model="promoSearch" class="rounded-2xl border border-slate-200 pl-10 pr-4 py-3 w-80 outline-none focus:ring-4 focus:ring-orange-50 transition" placeholder="Search promotion, restaurant...">
                        </div>
                        <button class="rounded-2xl bg-orange-600 text-white px-6 py-3 font-black flex items-center gap-2 shadow-lg shadow-orange-100 transition active:scale-95">
                            <Plus class="w-5 h-5" /> Add Promotion
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="card rounded-[32px] border-slate-100 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-black tracking-widest">
                                <tr>
                                    <th class="py-4 px-6">Promotion</th>
                                    <th>Restaurant</th>
                                    <th>Type</th>
                                    <th>Validity</th>
                                    <th>Redemptions</th>
                                    <th>Status</th>
                                    <th class="pr-6">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="font-bold text-slate-700">
                                <tr v-for="p in filteredPromotions" :key="p.id" class="border-t border-slate-50 hover:bg-slate-50 transition">
                                    <td class="py-5 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-xl bg-orange-100 text-orange-600 grid place-items-center"><BadgePercent class="w-5 h-5" /></div>
                                            <b class="text-slate-900 leading-tight">{{ p.name }}</b>
                                        </div>
                                    </td>
                                    <td>{{ p.restaurant }}</td>
                                    <td><span class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-black uppercase text-slate-500">{{ p.type }}</span></td>
                                    <td>
                                        <div class="text-[13px] text-slate-900">{{ p.start }} – {{ p.end }}</div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden max-w-[60px]">
                                                <div class="h-full bg-orange-500" :style="{ width: (p.redemptions / 500 * 100) + '%' }"></div>
                                            </div>
                                            <span class="text-xs text-slate-400">{{ p.redemptions }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase border" :class="p.status === 'Active' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-amber-50 text-amber-700 border-amber-100'">{{ p.status }}</span>
                                    </td>
                                    <td class="pr-6">
                                        <button class="h-9 w-9 rounded-xl bg-slate-100 grid place-items-center text-slate-500 hover:bg-slate-200 transition">✕</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
