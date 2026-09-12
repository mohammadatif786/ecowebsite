<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Send, Search, Wallet, Landmark, ArrowUpRight } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    transfers: {
        data: any[];
        meta: any;
    };
    stats: {
        total_payouts: number;
        avg_fee: number;
    };
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
const status = ref(props.filters?.status || 'All Status');
const globalFilters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });

watch([search, status], () => {
    debounceSearch();
});

let debounceTimeout: any = null;
const debounceSearch = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get(route('admin.commerce.marketplace.transfers'), {
            search: search.value,
            status: status.value,
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
    <Head title="Seller Transfers" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="marketplaceSellerTransfersCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Marketplace Seller Transfers Command Center" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" @search="handleSearch" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-slate-950">Seller Transfers</h3>
                        <p class="text-slate-500 font-medium mt-1">Transfer marketplace proceeds from escrow to seller wallets or bank accounts.</p>
                    </div>
                </div>

                <div class="card rounded-3xl bg-white border-slate-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-[10px] font-black uppercase text-slate-400 tracking-widest">
                                <tr>
                                    <th class="px-6 py-6">Transfer ID</th>
                                    <th class="px-6 py-6">Seller</th>
                                    <th class="px-6 py-6">Method</th>
                                    <th class="px-6 py-6">Amount</th>
                                    <th class="px-6 py-6">Fee</th>
                                    <th class="px-6 py-6">Net</th>
                                    <th class="px-6 py-6">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="t in transfers.data" :key="t.id" class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-5 font-black text-purple-600 uppercase">{{ t.id }}</td>
                                    <td class="px-6 py-5 font-black text-slate-800">{{ t.seller }}</td>
                                    <td class="px-6 py-5 font-bold text-slate-600">
                                        {{ t.method }}
                                    </td>
                                    <td class="px-6 py-5 font-bold text-slate-600">${{ t.amount.toFixed(2) }}</td>
                                    <td class="px-6 py-5 font-bold text-slate-600">${{ t.fee.toFixed(2) }}</td>
                                    <td class="px-6 py-5 font-black text-slate-800">${{ t.net.toFixed(2) }}</td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 w-1.5 rounded-full" :class="t.status === 'Completed' || t.status === 'Approved' ? 'bg-green-500' : 'bg-amber-500'"></div>
                                            <span class="text-xs font-black uppercase tracking-wider" :class="t.status === 'Completed' || t.status === 'Approved' ? 'text-green-600' : 'text-amber-600'">
                                                {{ t.status }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!transfers.data.length">
                                    <td colspan="7" class="px-6 py-20 text-center">
                                        <div class="h-16 w-16 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-300 mx-auto mb-4">
                                            <Send class="w-8 h-8" />
                                        </div>
                                        <h4 class="text-lg font-black text-slate-900">No transfer records found</h4>
                                        <p class="text-slate-500 font-medium text-sm mt-1">Try a different search term or check back later.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transfers.data.length" class="p-6 border-t border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">
                            Showing {{ transfers.data.length }} of {{ transfers.meta.total }} records
                        </p>
                        <div class="flex items-center gap-2">
                            <Link v-for="link in transfers.meta.links" :key="link.label"
                                :href="link.url || '#'"
                                v-html="link.label"
                                class="h-10 px-4 rounded-xl flex items-center text-xs font-black uppercase tracking-widest transition"
                                :class="[
                                    link.active ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            />
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
