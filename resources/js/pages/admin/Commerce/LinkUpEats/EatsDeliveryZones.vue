<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Plus, MapPin, X, Save, Info } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

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

// Delivery Zones State
const zones = ref([
    { name: 'Nassau', country: 'Bahamas', base: 3.50, dist: 0.75, unit: 'mile', min: 10, smallFee: 2.00, free: 35, radius: 8, etaMin: 25, etaMax: 40, surge: 1.5, status: 'Active' },
    { name: 'Montego Bay', country: 'Jamaica', base: 3.00, dist: 0.65, unit: 'mile', min: 10, smallFee: 1.75, free: 30, radius: 7, etaMin: 20, etaMax: 35, surge: 1.4, status: 'Active' },
    { name: 'Port of Spain', country: 'Trinidad & Tobago', base: 3.25, dist: 0.70, unit: 'mile', min: 12, smallFee: 2.00, free: 35, radius: 8, etaMin: 25, etaMax: 45, surge: 1.5, status: 'Active' },
    { name: 'Bridgetown', country: 'Barbados', base: 3.00, dist: 0.60, unit: 'mile', min: 10, smallFee: 1.50, free: 30, radius: 6, etaMin: 20, etaMax: 30, surge: 1.3, status: 'Pending Setup' }
]);

// Estimator Logic
const estForm = ref({ zoneIndex: 0, distance: 3, orderValue: 18, isPeak: false });
const estimatedFee = computed(() => {
    const z = zones.value[estForm.value.zoneIndex];
    if (!z) return 0;
    if (z.free > 0 && estForm.value.orderValue >= z.free) return 0;
    let fee = z.base + Math.max(0, estForm.value.distance) * z.dist;
    if (z.min > 0 && estForm.value.orderValue < z.min) fee += z.smallFee;
    if (estForm.value.isPeak && z.surge > 1) fee *= z.surge;
    return fee;
});

// Modal Logic
const showModal = ref(false);
const editingZoneIndex = ref<number | null>(null);
const modalForm = ref({ name: '', country: '', base: 3.50, dist: 0.75, unit: 'mile', min: 10, smallFee: 2.00, free: 35, radius: 8, etaMin: 25, etaMax: 40, surge: 1.5, status: 'Active' });

const openModal = (index: number | null = null) => {
    editingZoneIndex.value = index;
    if (index !== null) modalForm.value = { ...zones.value[index] };
    else modalForm.value = { name: '', country: '', base: 3.50, dist: 0.75, unit: 'mile', min: 10, smallFee: 2.00, free: 35, radius: 8, etaMin: 25, etaMax: 40, surge: 1.5, status: 'Active' };
    showModal.value = true;
};

const saveZone = () => {
    if (editingZoneIndex.value !== null) zones.value[editingZoneIndex.value] = { ...modalForm.value };
    else zones.value.push({ ...modalForm.value });
    showModal.value = false;
};

const deleteZone = (index: number) => {
    if (confirm('Delete this delivery zone?')) zones.value.splice(index, 1);
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Eats Delivery Zones" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eatsDeliveryZonesCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Eats Delivery Zones" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-orange-600">Delivery Zones</h3>
                        <p class="text-slate-500 font-medium">Set service areas, base fees, distance rates, and surge pricing.</p>
                    </div>
                    <button @click="openModal()" class="rounded-2xl bg-orange-600 text-white px-6 py-3 font-black flex items-center gap-2 shadow-lg shadow-orange-100 transition active:scale-95">
                        <Plus class="w-5 h-5" /> Add Zone
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    <div v-for="(z, i) in zones" :key="z.name" class="card rounded-[32px] p-6 border-slate-100 shadow-sm relative overflow-hidden group">
                        <div class="flex justify-between items-start relative z-10">
                            <div>
                                <h4 class="text-xl font-black text-slate-800">{{ z.name }}</h4>
                                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">{{ z.country }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase border" :class="z.status === 'Active' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-amber-50 text-amber-700 border-amber-100'">{{ z.status }}</span>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3 relative z-10">
                            <div class="rounded-2xl bg-slate-50 p-3 border border-slate-100/50">
                                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Base Fee</p>
                                <b class="text-lg text-slate-900">${{ z.base.toFixed(2) }}</b>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3 border border-slate-100/50">
                                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Per {{ z.unit }}</p>
                                <b class="text-lg text-slate-900">${{ z.dist.toFixed(2) }}</b>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3 border border-slate-100/50">
                                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Min Order</p>
                                <b class="text-lg text-slate-900">${{ z.min.toFixed(0) }}</b>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3 border border-slate-100/50">
                                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Free Over</p>
                                <b class="text-lg text-slate-900">${{ z.free.toFixed(0) }}</b>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between text-[11px] font-bold text-slate-500 relative z-10">
                            <div class="flex items-center gap-1"><Info class="w-3 h-3 text-slate-300" /> ETA {{ z.etaMin }}-{{ z.etaMax }}m</div>
                            <div class="text-orange-600">Surge x{{ z.surge }}</div>
                        </div>

                        <div class="mt-5 flex gap-2 relative z-10">
                            <button @click="openModal(i)" class="flex-1 rounded-xl border border-slate-200 py-2.5 font-black text-sm text-slate-600 hover:bg-slate-50 transition">Edit</button>
                            <button @click="deleteZone(i)" class="rounded-xl bg-rose-50 text-rose-600 px-4 py-2.5 font-black text-sm hover:bg-rose-100 transition">✕</button>
                        </div>
                    </div>
                </div>

                <!-- Estimator -->
                <div class="card rounded-[40px] p-8 border-orange-100 bg-orange-50/20 shadow-sm border-2">
                    <h3 class="text-2xl font-black text-slate-800 mb-2">Delivery Fee Estimator</h3>
                    <p class="text-slate-500 font-medium mb-8">Preview customer delivery pricing for specific scenarios.</p>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest block">Select Zone</label>
                            <select v-model="estForm.zoneIndex" class="w-full rounded-2xl border border-slate-200 px-5 py-3.5 bg-white font-black text-slate-700 outline-none focus:ring-4 focus:ring-orange-100 transition">
                                <option v-for="(z, i) in zones" :key="z.name" :value="i">{{ z.name }}</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest block">Distance ({{ zones[estForm.zoneIndex]?.unit }})</label>
                            <input v-model.number="estForm.distance" type="number" step="0.1" class="w-full rounded-2xl border border-slate-200 px-5 py-3.5 bg-white font-black text-slate-700 outline-none focus:ring-4 focus:ring-orange-100 transition">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest block">Order Value ($)</label>
                            <input v-model.number="estForm.orderValue" type="number" step="1" class="w-full rounded-2xl border border-slate-200 px-5 py-3.5 bg-white font-black text-slate-700 outline-none focus:ring-4 focus:ring-orange-100 transition">
                        </div>
                        <div class="rounded-3xl bg-white p-6 border-2 border-orange-100 shadow-sm flex flex-col justify-center min-h-[92px]">
                            <p class="text-[10px] font-black uppercase text-orange-400 tracking-widest">Calculated Fee</p>
                            <h4 class="text-3xl font-black text-orange-600">${{ estimatedFee.toFixed(2) }}</h4>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <input v-underline-none v-model="estForm.isPeak" type="checkbox" class="h-6 w-6 rounded-lg accent-orange-600">
                        <span class="text-sm font-black text-slate-700 uppercase tracking-widest">Apply Peak Hours Surge</span>
                    </div>
                </div>
            </section>
        </main>

        <!-- Zone Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-3 backdrop-blur-md overflow-y-auto">
            <div class="bg-white rounded-[40px] max-w-lg w-full shadow-2xl my-4 overflow-hidden animate-in slide-in-from-bottom-4 duration-300">
                <div class="p-8 bg-slate-900 text-white flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-black">{{ editingZoneIndex !== null ? 'Edit' : 'Add' }} Zone</h3>
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">Configure area-specific logistics</p>
                    </div>
                    <button @click="showModal = false" class="h-12 w-12 rounded-2xl bg-white/10 grid place-items-center hover:bg-white/20 transition">✕</button>
                </div>
                <div class="p-8 space-y-5 bg-slate-50/30">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 space-y-1.5"><label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Zone / City Name</label><input v-model="modalForm.name" class="w-full rounded-xl border border-slate-200 px-4 py-3 font-bold"></div>
                        <div class="col-span-2 space-y-1.5"><label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Country</label><input v-model="modalForm.country" class="w-full rounded-xl border border-slate-200 px-4 py-3 font-bold"></div>
                        <div class="space-y-1.5"><label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Base Fee ($)</label><input v-model.number="modalForm.base" type="number" step="0.01" class="w-full rounded-xl border border-slate-200 px-4 py-3 font-black"></div>
                        <div class="space-y-1.5"><label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Distance Rate ($)</label><input v-model.number="modalForm.dist" type="number" step="0.01" class="w-full rounded-xl border border-slate-200 px-4 py-3 font-black"></div>
                        <div class="space-y-1.5"><label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Free Over ($)</label><input v-model.number="modalForm.free" type="number" step="1" class="w-full rounded-xl border border-slate-200 px-4 py-3 font-black"></div>
                        <div class="space-y-1.5"><label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Peak Surge (x)</label><input v-model.number="modalForm.surge" type="number" step="0.1" class="w-full rounded-xl border border-slate-200 px-4 py-3 font-black"></div>
                    </div>
                    <button @click="saveZone" class="w-full rounded-2xl bg-orange-600 text-white py-4 font-black shadow-xl shadow-orange-100 hover:bg-orange-700 transition active:scale-95 uppercase tracking-widest mt-4">Save Configuration</button>
                </div>
            </div>
        </div>
    </div>
</template>
