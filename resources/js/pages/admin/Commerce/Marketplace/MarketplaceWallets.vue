<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Wallet, Search, ArrowDownLeft, ArrowUpRight, Filter } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    wallets: any[];
    filters: any;
}>();

import { router } from '@inertiajs/vue3';
import { watch } from 'vue';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const countries = ref(props.initialCountries);
const search = ref(props.filters?.search || '');
const globalFilters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });

watch(search, () => {
    debounceSearch();
});

let debounceTimeout: any = null;
const debounceSearch = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get(route('admin.commerce.marketplace.wallets'), {
            search: search.value,
        }, {
            preserveState: true,
            replace: true,
        });
    }, 400);
};

const handleSearch = (query: string) => {
    search.value = query;
};

const handleFilterChange = (newFilters: any) => {
    globalFilters.value = newFilters;
    renderAll();
};

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

const getScale = () => {
    const p = globalFilters.value.period;
    return p === 'Today' ? 1 / 30 : p === 'Weekly' ? 0.25 : p === 'Quarterly' ? 3 : p === 'Yearly' ? 12 : p === '5-Year' ? 60 : 1;
};

const renderAll = async () => {
    for (let i = 0; i < 3; i++) {
        await nextTick();
        const s = getScale();
        const rs = countries.value.filter(c => (globalFilters.value.region === 'All' || c.region === globalFilters.value.region) && (globalFilters.value.country === 'All Countries' || c.country === globalFilters.value.country));

        const t = rs.reduce((a, c) => {
            props.initialUnits.forEach(u => {
                a.gtv += (Number(c[u.key]) || 0);
            });
            a.users += (Number(c.users) || 0);
            a.merchants += (Number(c.merchants) || 0);
            a.organizers += (Number(c.organizers) || 0);
            return a;
        }, { gtv: 0, users: 0, merchants: 0, organizers: 0 });

        const set = (id: string, v: string) => {
            const e = document.getElementById(id);
            if (e) {
                e.textContent = v;
                return true;
            }
            return false;
        };

        const found = set('rGTV', fmt(t.gtv * s));
        set('rLinkUp', fmt(t.gtv * s * 0.12));
        set('rBank', fmt(t.gtv * s * 0.03));
        set('rNet', fmt(t.gtv * s * 0.09));
        set('rUsers', num(t.users * s));
        set('rMerchants', num(t.merchants));
        set('rOrganizers', num(t.organizers));
        set('rCountries', num(rs.length));
        set('sideGTV', fmt(t.gtv * s));

        if (found) break;
        await new Promise(r => setTimeout(r, 100));
    }
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
    renderAll();
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Marketplace Wallets" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="marketplaceWalletsCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Marketplace Wallets Command Center" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" @search="handleSearch" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-slate-950">Marketplace Wallets</h3>
                        <p class="text-slate-500 font-medium mt-1">Seller wallet balances connected to marketplace sales and payout history.</p>
                    </div>
                </div>

                <div class="card rounded-3xl bg-white border-slate-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-[10px] font-black uppercase text-slate-400 tracking-widest">
                                <tr>
                                    <th class="px-6 py-6">Seller</th>
                                    <th class="px-6 py-6">Available Balance</th>
                                    <th class="px-6 py-6">Escrow Held</th>
                                    <th class="px-6 py-6">Total Payouts</th>
                                    <th class="px-6 py-6">Currency</th>
                                    <th class="px-6 py-6 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="w in wallets" :key="w.seller" class="hover:bg-slate-50/50 transition group">
                                    <td class="px-6 py-5 font-black text-slate-800">{{ w.seller }}</td>
                                    <td class="px-6 py-5 font-black text-slate-900 text-lg">${{ w.available.toLocaleString() }}</td>
                                    <td class="px-6 py-5 font-bold text-amber-600">${{ w.held.toLocaleString() }}</td>
                                    <td class="px-6 py-5 font-bold text-slate-400">${{ w.paidOut.toLocaleString() }}</td>
                                    <td class="px-6 py-5 font-black text-slate-400 uppercase tracking-widest text-xs">{{ w.currency }}</td>
                                    <td class="px-6 py-5 text-right font-black text-[10px] uppercase text-emerald-600 tracking-wider">
                                        {{ w.status }}
                                    </td>
                                </tr>
                                <tr v-if="!wallets.length">
                                    <td colspan="6" class="px-6 py-20 text-center">
                                        <div class="h-16 w-16 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-300 mx-auto mb-4">
                                            <Wallet class="w-8 h-8" />
                                        </div>
                                        <h4 class="text-lg font-black text-slate-900">No wallet records found</h4>
                                        <p class="text-slate-500 font-medium text-sm mt-1">Try a different search term or check back later.</p>
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
