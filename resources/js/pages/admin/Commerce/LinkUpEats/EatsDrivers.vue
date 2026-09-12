<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Search, Plus, Eye, Pencil, Trash2, X, Bike, Mail, Phone, MapPin, Banknote, Camera, Info, Save } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

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

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

// Drivers State
const drivers = ref([
    { id: 'DRV-001', name: 'Devon Rolle', email: 'devon.rolle@drivers.linkup', phone: '242-555-1100', country: 'Bahamas', city: 'Nassau', vehicle: 'Car', kyc: 'Approved', status: 'Active', deliveries: 182, rating: 4.8, earnings: 4200 },
    { id: 'DRV-002', name: 'Tamika Bain', email: 'tamika.bain@drivers.linkup', phone: '242-555-2222', country: 'Bahamas', city: 'Nassau', vehicle: 'Scooter', kyc: 'Approved', status: 'Active', deliveries: 126, rating: 4.7, earnings: 3100 },
    { id: 'DRV-003', name: 'Andre Blake', email: 'andre.blake@drivers.linkup', phone: '876-555-3000', country: 'Jamaica', city: 'Kingston', vehicle: 'Motorbike', kyc: 'Pending Review', status: 'Review', deliveries: 74, rating: 4.5, earnings: 1800 }
]);

const driverSearch = ref('');
const filteredDrivers = computed(() => {
    const q = driverSearch.value.toLowerCase().trim();
    return drivers.value.filter(d =>
        !q || [d.name, d.email, d.country, d.vehicle, d.status].join(' ').toLowerCase().includes(q)
    );
});

// Modal Logic
const showModal = ref(false);
const editingDriver = ref<any>(null);
const modalForm = ref({ name: '', email: '', phone: '', country: 'Bahamas', city: '', vehicle: 'Car', kyc: 'Pending Review', status: 'Review', bank: '', wallet: '' });

const openModal = (d: any = null) => {
    if (d) {
        editingDriver.value = d;
        modalForm.value = { ...d };
    } else {
        editingDriver.value = null;
        modalForm.value = { name: '', email: '', phone: '', country: 'Bahamas', city: '', vehicle: 'Car', kyc: 'Pending Review', status: 'Review', bank: '', wallet: '' };
    }
    showModal.value = true;
};

const saveDriver = () => {
    if (editingDriver.value) {
        Object.assign(editingDriver.value, modalForm.value);
    } else {
        drivers.value.unshift({ id: 'DRV-' + Date.now().toString().slice(-3), deliveries: 0, rating: 0, earnings: 0, ...modalForm.value });
    }
    showModal.value = false;
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Eats Drivers" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eatsDriversCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Eats Drivers" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="() => {}" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-orange-600">Eats Drivers</h3>
                        <p class="text-slate-500 font-medium">Onboarding, KYC, offline/online status, and performance history.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <div class="relative">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <input v-model="driverSearch" class="rounded-2xl border border-slate-200 pl-10 pr-4 py-3 w-80 outline-none focus:ring-4 focus:ring-orange-50 transition" placeholder="Search driver, vehicle, city...">
                        </div>
                        <button @click="openModal()" class="rounded-2xl bg-orange-600 text-white px-6 py-3 font-black flex items-center gap-2 shadow-lg shadow-orange-100 transition active:scale-95">
                            <Plus class="w-5 h-5" /> Add Driver
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-5 gap-5">
                    <div class="card rounded-3xl p-5 border border-slate-100"><p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Total Drivers</p><h3 class="text-4xl font-black mt-1">{{ num(drivers.length) }}</h3></div>
                    <div class="card rounded-3xl p-5 border border-slate-100"><p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Active</p><h3 class="text-4xl font-black mt-1 text-green-600">{{ num(drivers.filter(d=>d.status==='Active').length) }}</h3></div>
                    <div class="card rounded-3xl p-5 border border-slate-100"><p class="text-slate-500 font-bold text-xs uppercase tracking-widest">In Review</p><h3 class="text-4xl font-black mt-1 text-amber-500">{{ num(drivers.filter(d=>d.status==='Review').length) }}</h3></div>
                    <div class="card rounded-3xl p-5 border border-slate-100"><p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Pending KYC</p><h3 class="text-4xl font-black mt-1 text-rose-500">{{ num(drivers.filter(d=>d.kyc!=='Approved').length) }}</h3></div>
                    <div class="card rounded-3xl p-5 border border-slate-100"><p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Total Deliveries</p><h3 class="text-4xl font-black mt-1 text-slate-900">{{ num(drivers.reduce((s,d)=>s+d.deliveries, 0)) }}</h3></div>
                </div>

                <!-- Table -->
                <div class="card rounded-[32px] border-slate-100 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-black tracking-widest">
                                <tr>
                                    <th class="py-4 px-6">Driver</th>
                                    <th>Location</th>
                                    <th>Vehicle</th>
                                    <th>KYC</th>
                                    <th>Status</th>
                                    <th>Performance</th>
                                    <th>Earnings</th>
                                    <th class="text-center pr-6">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="font-bold text-slate-700">
                                <tr v-for="d in filteredDrivers" :key="d.id" class="border-t border-slate-50 hover:bg-slate-50 transition">
                                    <td class="py-5 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-orange-100 text-orange-600 grid place-items-center font-black text-xs uppercase">{{ d.name.split(' ').map(x=>x[0]).join('') }}</div>
                                            <div>
                                                <b class="text-slate-900 leading-tight block">{{ d.name }}</b>
                                                <p class="text-[11px] text-slate-400 font-bold mt-0.5 tracking-tight">{{ d.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-1.5 text-slate-600 font-black uppercase text-[11px]">
                                            <MapPin class="w-3 h-3 text-slate-400" /> {{ d.country }} / {{ d.city }}
                                        </div>
                                    </td>
                                    <td><span class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-black uppercase text-slate-500">{{ d.vehicle }}</span></td>
                                    <td><span class="text-xs" :class="d.kyc === 'Approved' ? 'text-green-600' : 'text-amber-600'">{{ d.kyc }}</span></td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase border" :class="d.status === 'Active' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-amber-50 text-amber-700 border-amber-100'">{{ d.status }}</span>
                                    </td>
                                    <td>
                                        <div class="text-[13px] text-slate-900">{{ num(d.deliveries) }} Dels.</div>
                                        <div class="text-[11px] text-amber-500 uppercase font-black tracking-tighter">⭐ {{ d.rating }} Rating</div>
                                    </td>
                                    <td class="text-base font-black text-slate-900">{{ fmt(d.earnings) }}</td>
                                    <td class="text-center pr-6">
                                        <div class="flex gap-1.5 justify-center">
                                            <button class="h-8 w-8 rounded-lg bg-slate-100 grid place-items-center text-slate-500 hover:bg-slate-200 transition"><Eye class="h-4 w-4" /></button>
                                            <button @click="openModal(d)" class="h-8 w-8 rounded-lg bg-slate-100 grid place-items-center text-slate-500 hover:bg-slate-200 transition"><Pencil class="h-4 w-4" /></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>

        <!-- Edit/Add Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 z-[100] flex items-start justify-center p-3 overflow-y-auto backdrop-blur-md">
            <div class="bg-white rounded-[40px] max-w-4xl w-full shadow-2xl my-4 overflow-hidden animate-in zoom-in duration-200">
                <div class="p-8 bg-gradient-to-r from-orange-500 to-amber-500 text-white flex items-center gap-6 sticky top-0 z-[110]">
                    <div class="h-16 w-16 rounded-2xl bg-white/20 grid place-items-center"><Bike class="w-8 h-8" /></div>
                    <div class="flex-1">
                        <h3 class="text-3xl font-black">{{ editingDriver ? 'Edit' : 'Add' }} Driver</h3>
                        <p class="text-orange-100 font-bold mt-1 text-sm uppercase tracking-widest">Update driver profile, vehicle docs and KYC</p>
                    </div>
                    <button @click="showModal = false" class="h-12 w-12 rounded-2xl bg-white/20 grid place-items-center hover:bg-white/30 transition">✕</button>
                </div>
                <div class="p-8 space-y-6 max-h-[75vh] overflow-y-auto scrollbar bg-slate-50/30">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 space-y-6">
                            <div class="card rounded-[32px] p-8 border-slate-200/60 shadow-sm">
                                <h4 class="text-xl font-black text-slate-800 mb-6">Personal Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="md:col-span-2"><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Full Name</label><input v-model="modalForm.name" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold text-slate-700"></div>
                                    <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Email</label><input v-model="modalForm.email" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold"></div>
                                    <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Phone</label><input v-model="modalForm.phone" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold"></div>
                                    <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Country</label><input v-model="modalForm.country" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold"></div>
                                    <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">City</label><input v-model="modalForm.city" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold"></div>
                                </div>
                            </div>
                            <div class="card rounded-[32px] p-8 border-slate-200/60 shadow-sm">
                                <h4 class="text-xl font-black text-slate-800 mb-6">Vehicle & Banking</h4>
                                <div class="grid grid-cols-2 gap-5">
                                    <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Vehicle Type</label><select v-model="modalForm.vehicle" class="w-full rounded-2xl border border-slate-200 px-5 py-4 bg-white font-bold"><option>Car</option><option>Scooter</option><option>Motorbike</option><option>Van</option></select></div>
                                    <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Status</label><select v-model="modalForm.status" class="w-full rounded-2xl border border-slate-200 px-5 py-4 bg-white font-black"><option>Active</option><option>Review</option><option>Suspended</option></select></div>
                                    <div class="col-span-2"><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Bank Name</label><input v-model="modalForm.bank" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold"></div>
                                    <div class="col-span-2"><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Wallet ID</label><input v-model="modalForm.wallet" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-mono font-bold"></div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div class="card rounded-[32px] p-6 border-slate-200 shadow-sm text-center">
                                <h4 class="text-sm font-black text-slate-800 mb-4 text-left uppercase tracking-widest">Driver Photo</h4>
                                <label class="block rounded-[28px] border-2 border-dashed border-slate-300 p-8 cursor-pointer hover:bg-slate-50 transition">
                                    <Camera class="w-10 h-10 mx-auto text-slate-300 mb-2" />
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-tighter">Upload Profile</span>
                                    <input type="file" class="hidden">
                                </label>
                            </div>
                            <div class="rounded-[32px] bg-orange-50 p-6 border border-orange-100">
                                <h4 class="flex items-center gap-2 text-orange-700 font-black mb-3"><Info class="w-5 h-5" /> Driver Onboarding</h4>
                                <ul class="space-y-2 text-orange-600 text-xs font-bold leading-relaxed">
                                    <li class="flex gap-2"><span>•</span> Documents must be verified to go active</li>
                                    <li class="flex gap-2"><span>•</span> Earnings settle automatically to wallet</li>
                                    <li class="flex gap-2"><span>•</span> GPS tracking is used for dispatch priority</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-8 border-t border-slate-200 flex justify-end gap-4 sticky bottom-0 bg-white z-[110]">
                    <button @click="showModal = false" class="rounded-2xl bg-slate-100 px-10 py-4 font-black text-slate-500 hover:bg-slate-200 transition">Cancel</button>
                    <button @click="saveDriver" class="rounded-2xl bg-orange-600 text-white px-12 py-4 font-black shadow-xl shadow-orange-100 hover:bg-orange-700 transition active:scale-95 uppercase tracking-wider">
                        <Save class="w-5 h-5 inline-block mr-2" /> {{ editingDriver ? 'Update' : 'Add' }} Driver
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
