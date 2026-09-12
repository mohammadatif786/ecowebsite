<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head, router, Link, useForm } from '@inertiajs/vue3';
import { LockKeyhole, Unlock, AlertCircle, CheckCircle2, Search, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    escrowItems: {
        data: any[];
        meta: any;
    };
    stats: {
        pending: number;
        released: number;
        disputes: number;
    };
    filters: any;
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const countries = ref(props.initialCountries);

const search = ref(props.filters.search || '');
watch(search, () => {
    debounceSearch();
});

let debounceTimeout: any = null;
const debounceSearch = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get(route('admin.commerce.marketplace.escrow'), {
            search: search.value,
        }, {
            preserveState: true,
            replace: true,
        });
    }, 400);
};

const releaseForm = useForm({});
const releaseFunds = (id: number) => {
    if (confirm('Are you sure you want to release these funds to the seller?')) {
        releaseForm.post(route('admin.commerce.marketplace.escrow.release', id), {
            preserveScroll: true,
            onSuccess: () => {
                // Success feedback
            }
        });
    }
};

const formatCurrency = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 }).format(n);
const formatNumber = (n: number) => new Intl.NumberFormat('en-US').format(n);

const ribbonMetrics = computed(() => {
    const rs = countries.value;
    const t = rs.reduce((a, c) => {
        props.initialUnits.forEach(u => { a.gtv += (Number(c[u.key]) || 0); });
        a.users += (Number(c.users) || 0);
        a.merchants += (Number(c.merchants) || 0);
        a.organizers += (Number(c.organizers) || 0);
        return a;
    }, { gtv: 0, users: 0, merchants: 0, organizers: 0 });

    return {
        gtv: formatCurrency(t.gtv),
        linkupRev: formatCurrency(t.gtv * 0.12),
        procPool: formatCurrency(t.gtv * 0.03),
        netProfit: formatCurrency(t.gtv * 0.09),
        users: formatNumber(t.users),
        merchants: formatNumber(t.merchants),
        organizers: formatNumber(t.organizers),
        countries: formatNumber(rs.length)
    };
});

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n);

const renderAll = async () => {
    for (let i = 0; i < 3; i++) {
        await nextTick();
        const rs = countries.value;
        const t = rs.reduce((a, c) => {
            props.initialUnits.forEach(u => { a.gtv += (Number(c[u.key]) || 0); });
            return a;
        }, { gtv: 0 });

        const set = (id: string, v: string) => {
            const e = document.getElementById(id);
            if (e) { e.textContent = v; return true; }
            return false;
        };
        set('sideGTV', fmt(t.gtv));
        if (set('rGTV', fmt(t.gtv))) break;
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
    <Head title="Marketplace Escrow" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="marketplaceEscrowCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Marketplace Escrow" :countries="countries" :metrics="ribbonMetrics" @toggle-sidebar="toggleSidebar" @filter-change="renderAll" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-slate-950 flex items-center gap-3"><LockKeyhole class="w-8 h-8" /> Escrow & Releases</h3>
                        <p class="text-slate-500 font-medium mt-1">Hold buyer payments until delivery confirmation, then release funds to sellers.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                        <p class="text-slate-500 font-bold text-sm">Escrow Pending</p>
                        <h3 class="text-4xl font-black mt-1 text-amber-600">${{ stats.pending.toFixed(2) }}</h3>
                    </div>
                    <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                        <p class="text-slate-500 font-bold text-sm">Released</p>
                        <h3 class="text-4xl font-black mt-1 text-emerald-600">${{ stats.released.toFixed(2) }}</h3>
                    </div>
                    <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                        <p class="text-slate-500 font-bold text-sm">Disputes</p>
                        <h3 class="text-4xl font-black mt-1 text-rose-600">{{ stats.disputes }}</h3>
                    </div>
                </div>

                <div class="card rounded-3xl bg-white border-slate-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-50">
                        <div class="relative w-full md:w-96">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <input v-model="search" type="text" placeholder="Search order #, seller..." class="w-full pl-11 pr-4 py-3.5 rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-sky-500 font-bold text-sm" />
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50/50 text-[10px] font-black uppercase text-slate-400 tracking-widest">
                                <tr>
                                    <th class="px-6 py-4">Order ID / Req</th>
                                    <th class="px-6 py-4">Seller</th>
                                    <th class="px-6 py-4">Gross</th>
                                    <th class="px-6 py-4">LinkUp Fee</th>
                                    <th class="px-6 py-4">Seller Net</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Release Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="item in escrowItems.data" :key="item.id" class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-5 font-mono font-bold text-xs text-sky-600">{{ item.id }}</td>
                                    <td class="px-6 py-5 font-black text-slate-800">{{ item.seller }}</td>
                                    <td class="px-6 py-5 font-bold text-slate-400">${{ item.gross.toFixed(2) }}</td>
                                    <td class="px-6 py-5 font-bold text-rose-500">-${{ item.fee.toFixed(2) }}</td>
                                    <td class="px-6 py-5 font-black text-emerald-600">${{ item.net.toFixed(2) }}</td>
                                    <td class="px-6 py-5">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider"
                                            :class="item.status === 'Held' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700'">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <button v-if="item.status === 'Held'" @click="releaseFunds(item.raw_id)" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-black text-xs transition hover:bg-emerald-700 active:scale-95 flex items-center gap-2 ml-auto">
                                            <Unlock class="w-3.5 h-3.5" /> Release
                                        </button>
                                        <div v-else class="text-slate-400 font-bold text-xs flex items-center justify-end gap-1">
                                            <CheckCircle2 class="w-4 h-4 text-emerald-500" /> Completed
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!escrowItems.data.length">
                                    <td colspan="7" class="px-6 py-20 text-center">
                                        <div class="h-16 w-16 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-300 mx-auto mb-4">
                                            <LockKeyhole class="w-8 h-8" />
                                        </div>
                                        <h4 class="text-lg font-black text-slate-900">No escrow records found</h4>
                                        <p class="text-slate-500 font-medium text-sm mt-1">Try a different search term or check back later.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="escrowItems.data.length" class="p-6 border-t border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">
                            Showing {{ escrowItems.data.length }} of {{ escrowItems.meta.total }} records
                        </p>
                        <div class="flex items-center gap-2">
                            <Link v-for="link in escrowItems.meta.links" :key="link.label"
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

                <div class="p-5 rounded-2xl bg-amber-50 border border-amber-100 flex items-start gap-3">
                    <AlertCircle class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                    <div>
                        <b class="text-amber-900 block">Payout Safety Guard</b>
                        <p class="text-amber-700 text-sm mt-0.5">Automated release triggers after 72 hours of marked delivery. Manual override should only be used after confirmed receipt or dispute resolution.</p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
