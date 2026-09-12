<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Plus, Search, Tags, MapPin, Pencil, Eye, Trash2, X, Store, Banknote, Camera, Info, Save } from 'lucide-vue-next';
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
    { country: 'Jamaica', region: 'Regional', users: 280000, merchants: 1200, organizers: 250, tickets: 720000, subscriptions: 90000, marketplace: 410000, eats: 650000, merchantPay: 1600000, wallet: 950000, live: 180000, ads: 45000, wellness: 190000, cookouts: 140000, linkup360: 55000, coinsPurchased: 420000, coinsRedeemed: 170000 },
];

const filters = ref({
    region: 'All',
    country: 'All Countries',
    period: 'Monthly',
});

const handleFilterChange = (newFilters: any) => {
    filters.value = newFilters;
};

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

// Restaurant State
const restaurants = ref([
    { id: 1, name: 'Bahama Grill', owner: 'Monique Johnson', cuisine: 'Seafood', description: 'Casual Bahamian seafood & grill.', email: 'monique@bahamagrill.com', phone: '242-555-1001', country: 'Bahamas', city: 'Nassau', address: '12 Bay Street, Nassau', status: 'Active', kyc: 'Verified', commission: 12, menuItems: 42, orders: 542, revenue: 12450, bankName: 'Scotiabank', acct: '001234567' },
    { id: 2, name: 'Island Jerk Kitchen', owner: 'Andre Campbell', cuisine: 'Jerk Meals', description: 'Authentic Jamaican jerk cuisine.', email: 'andre@islandjerk.com', phone: '876-555-1002', country: 'Jamaica', city: 'Montego Bay', address: '5 Hip Strip, Montego Bay', status: 'Active', kyc: 'Verified', commission: 12, menuItems: 36, orders: 411, revenue: 8930, bankName: 'NCB', acct: '778120033' },
    { id: 3, name: 'Trini Flavors', owner: 'Keisha Maharaj', cuisine: 'Trinidadian', description: 'Doubles, roti and island favorites.', email: 'keisha@triniflavors.com', phone: '868-555-1003', country: 'Trinidad & Tobago', city: 'Port of Spain', address: '9 Ariapita Ave, POS', status: 'Active', kyc: 'Verified', commission: 12, menuItems: 29, orders: 326, revenue: 6215, bankName: 'Republic Bank', acct: '553201998' },
    { id: 4, name: 'Bajan Bowl House', owner: 'Rhea Clarke', cuisine: 'Bowls', description: 'Healthy Barbadian bowls & smoothies.', email: 'rhea@bajanbowl.com', phone: '246-555-1004', country: 'Barbados', city: 'Bridgetown', address: '3 Broad Street, Bridgetown', status: 'Pending', kyc: 'Pending', commission: 12, menuItems: 18, orders: 0, revenue: 0, bankName: '', acct: '' }
]);

const cuisineCategories = ref(["Caribbean", "Seafood", "Latin", "Vegan", "BBQ", "Bakery"]);
const restaurantSearch = ref('');
const newCuisine = ref('');

const filteredRestaurants = computed(() => {
    const q = restaurantSearch.value.toLowerCase().trim();
    return restaurants.value.filter(r =>
        !q || [r.name, r.owner, r.city, r.country, r.cuisine, r.status].join(' ').toLowerCase().includes(q)
    );
});

// Modal Logic
const showModal = ref(false);
const editingRestaurant = ref<any>(null);
const modalForm = ref({
    name: '', owner: '', cuisine: '', description: '', email: '', phone: '',
    country: 'Bahamas', city: '', address: '', status: 'Pending', kyc: 'Pending',
    commission: 12, menuItems: 0, bankName: '', acct: ''
});

const openModal = (r: any = null) => {
    if (r) {
        editingRestaurant.value = r;
        modalForm.value = { ...r };
    } else {
        editingRestaurant.value = null;
        modalForm.value = { name: '', owner: '', cuisine: '', description: '', email: '', phone: '', country: 'Bahamas', city: '', address: '', status: 'Pending', kyc: 'Pending', commission: 12, menuItems: 0, bankName: '', acct: '' };
    }
    showModal.value = true;
};

const saveRestaurant = () => {
    if (editingRestaurant.value) {
        Object.assign(editingRestaurant.value, modalForm.value);
    } else {
        restaurants.value.unshift({ id: Date.now(), orders: 0, revenue: 0, ...modalForm.value });
    }
    showModal.value = false;
};

const addCuisine = () => {
    if (newCuisine.value.trim() && !cuisineCategories.value.includes(newCuisine.value.trim())) {
        cuisineCategories.value.push(newCuisine.value.trim());
        newCuisine.value = '';
    }
};

const removeCuisine = (c: string) => {
    cuisineCategories.value = cuisineCategories.value.filter(x => x !== c);
};

// Profile View
const showProfile = ref(false);
const selectedRestaurant = ref<any>(null);
const openProfile = (r: any) => {
    selectedRestaurant.value = r;
    showProfile.value = true;
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Eats Restaurants" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eatsRestaurantsCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Eats Restaurants" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-orange-600">LinkUp Eats Restaurants</h3>
                        <p class="text-slate-500 font-medium">Onboard, monitor, activate, and manage food places coming onto LinkUp Eats.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <div class="relative">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <input v-model="restaurantSearch" class="rounded-2xl border border-slate-200 pl-10 pr-4 py-3 w-80 outline-none focus:ring-4 focus:ring-orange-50 transition" placeholder="Search restaurant, owner, city...">
                        </div>
                        <button @click="openModal()" class="rounded-2xl bg-orange-600 text-white px-6 py-3 font-black flex items-center gap-2 shadow-lg shadow-orange-100 transition active:scale-95">
                            <Plus class="w-5 h-5" /> Add Restaurant
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div class="card metric dark rounded-3xl p-5 xl:col-span-2 shadow-xl">
                        <p class="text-slate-300 font-bold">Active Restaurants</p>
                        <h3 class="text-5xl font-black mt-2">{{ num(restaurants.filter(r=>r.status==='Active').length) }}</h3>
                        <p class="text-lime-300 font-bold mt-1 uppercase text-xs tracking-wider">Across {{ new Set(restaurants.map(r=>r.country)).size }} countries</p>
                    </div>
                    <div class="card rounded-3xl p-5 border border-slate-100">
                        <p class="text-slate-500 font-bold">Pending Onboarding</p>
                        <h3 class="text-4xl font-black mt-1 text-amber-500">{{ num(restaurants.filter(r=>r.status==='Pending').length) }}</h3>
                    </div>
                    <div class="card rounded-3xl p-5 border border-slate-100">
                        <p class="text-slate-500 font-bold">Gross Sales</p>
                        <h3 class="text-4xl font-black mt-1 text-slate-900">{{ fmt(restaurants.reduce((s,r)=>s+r.revenue, 0)) }}</h3>
                    </div>
                </div>

                <!-- Cuisine Categories -->
                <div class="card rounded-3xl p-6 border-slate-100 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="h-10 w-10 rounded-xl bg-orange-100 text-orange-600 grid place-items-center"><Tags class="w-6 h-6" /></span>
                        <div>
                            <h4 class="text-xl font-black text-slate-800">Cuisine Categories</h4>
                            <p class="text-sm text-slate-500 font-medium">Add or remove types diners filter by in the app.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <input v-model="newCuisine" @keyup.enter="addCuisine" class="rounded-2xl border border-slate-200 px-5 py-3 flex-1 min-w-[250px] outline-none focus:ring-4 focus:ring-orange-50 transition" placeholder="New cuisine type, e.g. Vegan, BBQ">
                        <button @click="addCuisine" class="rounded-2xl bg-orange-600 text-white px-6 py-3 font-black flex items-center gap-2 transition active:scale-95">Add Category</button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="c in cuisineCategories" :key="c" class="inline-flex items-center gap-2 rounded-full bg-orange-50 text-orange-700 border border-orange-200 px-4 py-2 text-sm font-black transition hover:bg-orange-100">
                            {{ c }}
                            <button @click="removeCuisine(c)" class="text-orange-400 hover:text-rose-500 transition"><X class="w-4 h-4" /></button>
                        </span>
                    </div>
                </div>

                <!-- Table -->
                <div class="card rounded-3xl border-slate-100 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-black tracking-widest">
                                <tr>
                                    <th class="py-4 px-6">Restaurant</th>
                                    <th>Owner</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Stats</th>
                                    <th>Revenue</th>
                                    <th>KYC</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="font-bold text-slate-700">
                                <tr v-for="r in filteredRestaurants" :key="r.id" class="border-t border-slate-50 hover:bg-slate-50 transition">
                                    <td class="py-5 px-6">
                                        <b class="text-base text-slate-900">{{ r.name }}</b>
                                        <p class="text-xs text-slate-400 font-bold uppercase tracking-tighter">{{ r.cuisine }}</p>
                                    </td>
                                    <td>{{ r.owner }}</td>
                                    <td>
                                        <div class="flex items-center gap-1.5 text-slate-600 font-black uppercase text-[11px]">
                                            <MapPin class="w-3 h-3 text-slate-400" /> {{ r.country }} / {{ r.city }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase border" :class="r.status === 'Active' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-amber-50 text-amber-700 border-amber-100'">{{ r.status }}</span>
                                    </td>
                                    <td>
                                        <div class="text-[13px] text-slate-900">{{ num(r.menuItems) }} Items</div>
                                        <div class="text-[11px] text-slate-400 uppercase font-black tracking-tighter">{{ num(r.orders) }} orders</div>
                                    </td>
                                    <td class="text-base font-black text-slate-900">{{ fmt(r.revenue) }}</td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase border" :class="r.kyc === 'Verified' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-amber-50 text-amber-700 border-amber-100'">{{ r.kyc }}</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-1.5">
                                            <button @click="openProfile(r)" class="h-9 w-9 rounded-xl bg-slate-100 grid place-items-center text-slate-500 hover:bg-slate-200 transition"><Eye class="h-4 w-4" /></button>
                                            <button @click="openModal(r)" class="h-9 w-9 rounded-xl bg-slate-100 grid place-items-center text-slate-500 hover:bg-slate-200 transition"><Pencil class="h-4 w-4" /></button>
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
                    <div class="h-16 w-16 rounded-2xl bg-white/20 grid place-items-center"><Store class="w-8 h-8" /></div>
                    <div class="flex-1">
                        <h3 class="text-3xl font-black">{{ editingRestaurant ? 'Edit' : 'Add' }} Restaurant</h3>
                        <p class="text-orange-100 font-bold mt-1 text-sm uppercase tracking-widest">Onboard a new merchant into LinkUp Eats</p>
                    </div>
                    <button @click="showModal = false" class="h-12 w-12 rounded-2xl bg-white/20 grid place-items-center hover:bg-white/30 transition">✕</button>
                </div>
                <div class="p-8 space-y-6 max-h-[75vh] overflow-y-auto scrollbar bg-slate-50/30">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 space-y-6">
                            <div class="card rounded-[32px] p-8 border-slate-200/60 shadow-sm">
                                <h4 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-2"><Store class="w-5 h-5 text-orange-600" /> Restaurant Details</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="md:col-span-2"><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Restaurant Name</label><input v-model="modalForm.name" class="w-full rounded-2xl border border-slate-200 px-5 py-4 focus:ring-4 focus:ring-orange-100 transition outline-none font-bold text-slate-700"></div>
                                    <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Owner Name</label><input v-model="modalForm.owner" class="w-full rounded-2xl border border-slate-200 px-5 py-4 focus:ring-4 focus:ring-orange-100 transition outline-none font-bold text-slate-700"></div>
                                    <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Cuisine</label><select v-model="modalForm.cuisine" class="w-full rounded-2xl border border-slate-200 px-5 py-4 bg-white font-bold"><option v-for="c in cuisineCategories" :key="c">{{ c }}</option></select></div>
                                    <div class="md:col-span-2"><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Full Address</label><textarea v-model="modalForm.address" rows="2" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold"></textarea></div>
                                </div>
                            </div>
                            <div class="card rounded-[32px] p-8 border-slate-200/60 shadow-sm">
                                <h4 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-2"><Banknote class="w-5 h-5 text-emerald-600" /> Business & Banking</h4>
                                <div class="grid grid-cols-2 gap-5">
                                    <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Commission (%)</label><input v-model.number="modalForm.commission" type="number" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-black"></div>
                                    <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Status</label><select v-model="modalForm.status" class="w-full rounded-2xl border border-slate-200 px-5 py-4 bg-white font-black"><option>Active</option><option>Pending</option><option>Suspended</option></select></div>
                                    <div class="col-span-2"><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Bank Name</label><input v-model="modalForm.bankName" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold"></div>
                                    <div class="col-span-2"><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Account Number</label><input v-model="modalForm.acct" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-mono font-bold"></div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div class="card rounded-[32px] p-6 border-slate-200 shadow-sm text-center">
                                <h4 class="text-sm font-black text-slate-800 mb-4 text-left uppercase tracking-widest">Logo</h4>
                                <label class="block rounded-[28px] border-2 border-dashed border-slate-300 p-8 cursor-pointer hover:bg-slate-50 transition">
                                    <Camera class="w-10 h-10 mx-auto text-slate-300 mb-2" />
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-tighter">Upload Photo</span>
                                    <input type="file" class="hidden">
                                </label>
                            </div>
                            <div class="rounded-[32px] bg-orange-50 p-6 border border-orange-100">
                                <h4 class="flex items-center gap-2 text-orange-700 font-black mb-3"><Info class="w-5 h-5" /> Restaurant Info</h4>
                                <ul class="space-y-2 text-orange-600 text-xs font-bold leading-relaxed">
                                    <li class="flex gap-2"><span>•</span> Commission applies to gross food sales</li>
                                    <li class="flex gap-2"><span>•</span> Verified status is required for app listing</li>
                                    <li class="flex gap-2"><span>•</span> Bank details are used for weekly settlement</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-8 border-t border-slate-200 flex justify-end gap-4 sticky bottom-0 bg-white z-[110]">
                    <button @click="showModal = false" class="rounded-2xl bg-slate-100 px-10 py-4 font-black text-slate-500 hover:bg-slate-200 transition">Cancel</button>
                    <button @click="saveRestaurant" class="rounded-2xl bg-orange-600 text-white px-12 py-4 font-black shadow-xl shadow-orange-100 hover:bg-orange-700 transition active:scale-95 uppercase tracking-wider">
                        <Save class="w-5 h-5 inline-block mr-2" /> {{ editingRestaurant ? 'Update' : 'Add' }} Restaurant
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
