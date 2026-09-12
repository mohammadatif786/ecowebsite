<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { HeartPulse, Check, X, Info } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model (From HTML)
const SCOTIA_SHARE = 0.4;
const countries = [
    { country: 'Bahamas', region: 'Local', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000, wellness: 150000, cookouts: 90000, linkup360: 40000, coinsPurchased: 250000, coinsRedeemed: 90000 },
    { country: 'Jamaica', region: 'Regional', users: 280000, merchants: 1200, organizers: 250, tickets: 720000, subscriptions: 90000, marketplace: 410000, eats: 650000, merchantPay: 1600000, wallet: 950000, live: 180000, ads: 45000, wellness: 190000, cookouts: 140000, linkup360: 55000, coinsPurchased: 420000, coinsRedeemed: 170000 },
];

const filters = ref({
    region: 'All',
    country: 'All Countries',
    period: 'Monthly',
});

const handleFilterChange = (newFilters: any) => {
    filters.value = newFilters;
    renderAll();
};

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

const getScale = () => {
    const p = filters.value.period;
    return p === 'Today' ? 1 / 30 : p === 'Weekly' ? 0.25 : p === 'Quarterly' ? 3 : p === 'Yearly' ? 12 : p === '5-Year' ? 60 : 1;
};

// Providers & Bookings
const initialProviders = [
    { handle: '@reneeb', name: 'Renee Bethel', cert: 'Licensed Massage Therapist', city: 'Nassau', country: 'Bahamas', status: 'Approved', rating: 4.9, sessions: 128, docs: { id: 'Verified', cert: 'LMT-4471 verified', selfie: 'Face match 99%', police: 'Cleared' } },
    { handle: '@kimspa', name: 'Kimberly Saunders', cert: 'Spa Therapist · Aromatherapy', city: 'Freeport', country: 'Bahamas', status: 'Pending Review', rating: 0, sessions: 0, docs: { id: 'Uploaded', cert: 'Cert uploaded — verifying', selfie: 'Face match 96%', police: 'Pending' } },
    { handle: '@joelmt', name: 'Joel Marcelin', cert: 'Sports Massage Therapist', city: 'Kingston', country: 'Jamaica', status: 'Pending Review', rating: 0, sessions: 0, docs: { id: 'Uploaded', cert: 'Uploaded', selfie: 'Face match 73% — review', police: 'Uploaded' } }
];

const bookings = ref([
    { id: 'MSG-7781', client: 'Aaliyah C.', provider: 'Renee Bethel', svc: 'Deep tissue · 60 min', addr: 'Cable Beach, Nassau', amt: 55, status: 'En route' },
    { id: 'MSG-7782', client: 'Marcus J.', provider: 'Renee Bethel', svc: 'Swedish · 90 min', addr: 'Sandyport, Nassau', amt: 72, status: 'Completed' },
    { id: 'MSG-7783', client: 'Cassius Stuart', provider: 'Finding provider…', svc: 'Session · 2:00 PM', addr: 'Cable Beach, Nassau', amt: 45, status: 'Requested' }
]);

const providers = ref([...initialProviders]);

const providerDecision = (handle: string, decision: string) => {
    const p = providers.value.find(x => x.handle === handle);
    if (p) p.status = decision;
    alert(`${decision === 'Approved' ? '✅' : '⛔'} ${handle} ${decision}`);
};

const getChipClass = (val: string) => {
    const ok = /Verified|Cleared|9\d%/.test(val) && !/review/i.test(val);
    const warn = /review|73%/.test(val);
    const pend = /Pending|verifying|Uploaded/.test(val) && !ok;
    if (ok) return 'bg-green-50 text-green-600';
    if (warn) return 'bg-amber-50 text-amber-600';
    if (pend) return 'bg-rose-50 text-rose-600';
    return 'bg-slate-100 text-slate-500';
};

const getStatusClass = (s: string) => {
    if (s === 'Approved') return 'bg-green-50 text-green-700';
    if (s === 'Rejected') return 'bg-rose-50 text-rose-700';
    return 'bg-amber-50 text-amber-700';
};

const getBookingClass = (s: string) => {
    const m: any = { 'En route': 'bg-sky-50 text-sky-700', 'Completed': 'bg-green-50 text-green-700', 'Requested': 'bg-amber-50 text-amber-700' };
    return m[s] || 'bg-slate-100 text-slate-600';
};

const renderAll = async () => {
    await nextTick();
    const set = (id: string, v: string) => {
        const e = document.getElementById(id);
        if (e) e.textContent = v;
    };
    set('sideGTV', '$46,673,000');
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
    <Head title="Wellness Providers" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="wellnessView" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Wellness Providers" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6 max-w-5xl mx-auto">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 flex items-center gap-3">💆 Wellness Providers</h2>
                    <p class="text-slate-500 mt-2 font-medium leading-relaxed">Mobile massage & spa therapists — verify certifications & identity, and monitor live mobile bookings. Approvals unlock a provider to accept bookings in the <b>LinkUp Wellness Provider app</b>. New applications & presence appear automatically.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="card rounded-3xl p-6 border-slate-100 shadow-sm transition hover:shadow-md">
                        <p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Awaiting review</p>
                        <h3 class="text-3xl font-black mt-1" :class="providers.filter(p => p.status === 'Pending Review').length > 0 ? 'text-amber-600' : 'text-green-600'">
                            {{ providers.filter(p => p.status === 'Pending Review').length }}
                        </h3>
                    </div>
                    <div class="card rounded-3xl p-6 border-slate-100 shadow-sm transition hover:shadow-md">
                        <p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Providers</p>
                        <h3 class="text-3xl font-black mt-1 text-slate-900">{{ providers.length }}</h3>
                    </div>
                    <div class="card rounded-3xl p-6 border-slate-100 shadow-sm transition hover:shadow-md">
                        <p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Active mobile bookings</p>
                        <h3 class="text-3xl font-black mt-1 text-slate-900">{{ bookings.length }}</h3>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-xl font-black text-slate-800">Provider onboarding & KYC</h3>
                    <div v-for="p in providers" :key="p.handle" class="card rounded-2xl p-6 border-slate-100 hover:border-purple-200 transition duration-300">
                        <div class="flex flex-wrap justify-between items-start gap-4">
                            <div class="flex-1 min-w-[200px]">
                                <div class="text-lg font-black text-slate-900">{{ p.name }} <span class="text-slate-400 font-bold ml-1 text-sm">{{ p.handle }}</span></div>
                                <p class="text-slate-500 font-bold text-xs mt-0.5 leading-tight">{{ p.cert }} · {{ p.city }}, {{ p.country }} <span v-if="p.sessions" class="text-purple-600">· {{ p.sessions }} sessions · ⭐ {{ p.rating }}</span></p>
                            </div>
                            <span class="rounded-full px-4 py-1.5 text-[10px] font-black uppercase border" :class="getStatusClass(p.status)">{{ p.status }}</span>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span v-for="(val, key) in p.docs" :key="key" class="px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-tight border border-slate-50 shadow-sm" :class="getChipClass(val)">
                                {{ key }}: {{ val }}
                            </span>
                        </div>
                        <div v-if="p.status === 'Pending Review'" class="mt-5 flex gap-3">
                            <button @click="providerDecision(p.handle, 'Approved')" class="flex-1 rounded-xl bg-emerald-600 text-white px-4 py-2.5 font-black text-sm shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition active:scale-95">Approve provider</button>
                            <button @click="providerDecision(p.handle, 'Rejected')" class="rounded-xl border-2 border-rose-200 text-rose-600 px-6 py-2.5 font-black text-sm hover:bg-rose-50 transition active:scale-95">Reject</button>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-xl font-black text-slate-800">Live mobile bookings</h3>
                    <div class="card rounded-3xl border-slate-100 overflow-hidden shadow-sm">
                        <div class="overflow-x-auto scrollbar">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-black tracking-widest">
                                    <tr>
                                        <th class="py-4 px-4">ID</th>
                                        <th>Client</th>
                                        <th>Provider</th>
                                        <th>Service</th>
                                        <th>Location</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-center pr-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm font-bold text-slate-700">
                                    <tr v-for="b in bookings" :key="b.id" class="border-t border-slate-50 hover:bg-slate-50/50 transition">
                                        <td class="py-4 px-4 font-mono text-xs text-slate-400">{{ b.id }}</td>
                                        <td class="text-slate-900">{{ b.client }}</td>
                                        <td class="text-slate-600 font-medium">{{ b.provider }}</td>
                                        <td class="text-slate-600 font-medium text-xs">{{ b.svc }}</td>
                                        <td class="text-slate-500 font-medium text-xs">{{ b.addr }}</td>
                                        <td class="text-right font-black text-slate-900">${{ b.amt.toFixed(2) }}</td>
                                        <td class="text-center pr-4">
                                            <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-tighter" :class="getBookingClass(b.status)">{{ b.status }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
