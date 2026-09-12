<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Plus, Search, Pencil, Trash2, X, Utensils, Camera, Save } from 'lucide-vue-next';
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

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 2 }).format(n);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

// Menu State
const menuItems = ref([
    { id: 1, restaurant: 'Bahama Grill', category: 'Seafood', item: 'Grilled Salmon Bowl', description: 'Fresh salmon over rice with greens.', price: 18.50, image: true, status: 'Available' },
    { id: 2, restaurant: 'Island Jerk Kitchen', category: 'Jerk Meals', item: 'Jerk Chicken Plate', description: 'Spicy jerk chicken with festival.', price: 14.00, image: true, status: 'Available' },
    { id: 3, restaurant: 'Trini Flavors', category: 'Local Favorites', item: 'Doubles Combo', description: 'Two doubles with channa & pepper.', price: 7.25, image: true, status: 'Low Stock' },
    { id: 4, restaurant: 'Bajan Bowl House', category: 'Lunch Bowls', item: 'Flying Fish Bowl', description: 'Bajan flying fish over slaw bowl.', price: 16.75, image: false, status: 'Draft' }
]);

const menuSearch = ref('');
const filteredMenuItems = computed(() => {
    const q = menuSearch.value.toLowerCase().trim();
    return menuItems.value.filter(m =>
        !q || [m.restaurant, m.category, m.item, m.status].join(' ').toLowerCase().includes(q)
    );
});

// Modal Logic
const showModal = ref(false);
const editingItem = ref<any>(null);
const modalForm = ref({ restaurant: '', category: '', item: '', description: '', price: 0, status: 'Available', image: false });

const openModal = (m: any = null) => {
    if (m) {
        editingItem.value = m;
        modalForm.value = { ...m };
    } else {
        editingItem.value = null;
        modalForm.value = { restaurant: '', category: '', item: '', description: '', price: 0, status: 'Available', image: false };
    }
    showModal.value = true;
};

const saveItem = () => {
    if (editingItem.value) {
        Object.assign(editingItem.value, modalForm.value);
    } else {
        menuItems.value.unshift({ id: Date.now(), ...modalForm.value });
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
    <Head title="Eats Menus" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eatsMenusCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Eats Menus" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="() => {}" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-orange-600">Menus</h3>
                        <p class="text-slate-500 font-medium">Manage menu categories, items, food photos, availability, and pricing.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <div class="relative">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <input v-model="menuSearch" class="rounded-2xl border border-slate-200 pl-10 pr-4 py-3 w-80 outline-none focus:ring-4 focus:ring-orange-50 transition" placeholder="Search item, restaurant...">
                        </div>
                        <button @click="openModal()" class="rounded-2xl bg-orange-600 text-white px-6 py-3 font-black flex items-center gap-2 shadow-lg shadow-orange-100 transition active:scale-95">
                            <Plus class="w-5 h-5" /> Add Item
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="card rounded-3xl p-5 border border-slate-100"><p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Menu Items</p><h3 class="text-4xl font-black mt-1">{{ num(menuItems.length) }}</h3></div>
                    <div class="card rounded-3xl p-5 border border-slate-100"><p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Available</p><h3 class="text-4xl font-black mt-1 text-green-600">{{ num(menuItems.filter(m=>m.status==='Available').length) }}</h3></div>
                    <div class="card rounded-3xl p-5 border border-slate-100"><p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Out of Stock</p><h3 class="text-4xl font-black mt-1 text-rose-500">{{ num(menuItems.filter(m=>m.status==='Out of Stock').length) }}</h3></div>
                    <div class="card rounded-3xl p-5 border border-slate-100"><p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Categories</p><h3 class="text-4xl font-black mt-1 text-sky-600">{{ num(new Set(menuItems.map(m=>m.category)).size) }}</h3></div>
                </div>

                <!-- Table -->
                <div class="card rounded-3xl border-slate-100 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-black tracking-widest">
                                <tr>
                                    <th class="py-4 px-6">Restaurant</th>
                                    <th>Category</th>
                                    <th>Item</th>
                                    <th>Price</th>
                                    <th>Image</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="font-bold text-slate-700">
                                <tr v-for="m in filteredMenuItems" :key="m.id" class="border-t border-slate-50 hover:bg-slate-50 transition">
                                    <td class="py-5 px-6">{{ m.restaurant }}</td>
                                    <td><span class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-black uppercase text-slate-500">{{ m.category }}</span></td>
                                    <td><b class="text-slate-900">{{ m.item }}</b></td>
                                    <td class="text-slate-900 font-black">{{ fmt(m.price) }}</td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div v-if="m.image" class="h-6 w-8 rounded bg-emerald-100 text-emerald-600 grid place-items-center"><ImageIcon class="w-3 h-3" /></div>
                                            <span :class="m.image ? 'text-emerald-600' : 'text-slate-300'">{{ m.image ? 'Uploaded' : 'Needed' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase border" :class="m.status === 'Available' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-amber-50 text-amber-700 border-amber-100'">{{ m.status }}</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-1.5">
                                            <button @click="openModal(m)" class="h-9 w-9 rounded-xl bg-slate-100 grid place-items-center text-slate-500 hover:bg-slate-200 transition"><Pencil class="h-4 w-4" /></button>
                                            <button class="h-9 w-9 rounded-xl bg-slate-100 grid place-items-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition"><Trash2 class="h-4 w-4" /></button>
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
            <div class="bg-white rounded-[40px] max-w-2xl w-full shadow-2xl my-6 overflow-hidden animate-in zoom-in duration-200">
                <div class="p-8 bg-gradient-to-r from-orange-500 to-amber-500 text-white flex items-center gap-6 sticky top-0 z-[110]">
                    <div class="h-16 w-16 rounded-2xl bg-white/20 grid place-items-center"><Utensils class="w-8 h-8" /></div>
                    <div class="flex-1">
                        <h3 class="text-3xl font-black">{{ editingItem ? 'Edit' : 'Add' }} Menu Item</h3>
                        <p class="text-orange-100 font-bold mt-1 text-sm uppercase tracking-widest">Update item price, description and availability</p>
                    </div>
                    <button @click="showModal = false" class="h-12 w-12 rounded-2xl bg-white/20 grid place-items-center hover:bg-white/30 transition">✕</button>
                </div>
                <div class="p-8 space-y-6 max-h-[70vh] overflow-y-auto scrollbar bg-slate-50/30">
                    <div class="card rounded-[32px] p-8 border-slate-200/60 shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Restaurant</label><input v-model="modalForm.restaurant" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold text-slate-700"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Category</label><input v-model="modalForm.category" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold text-slate-700"></div>
                            <div class="md:col-span-2"><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Item Name</label><input v-model="modalForm.item" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-black text-slate-800 text-lg"></div>
                            <div class="md:col-span-2"><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Description</label><textarea v-model="modalForm.description" rows="2" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold"></textarea></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Price ($)</label><input v-model.number="modalForm.price" type="number" step="0.01" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-black text-xl"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Status</label><select v-model="modalForm.status" class="w-full rounded-2xl border border-slate-200 px-5 py-4 bg-white font-black"><option>Available</option><option>Low Stock</option><option>Out of Stock</option><option>Draft</option></select></div>
                        </div>
                    </div>
                    <div class="card rounded-[32px] p-8 border-slate-200/60 shadow-sm text-center">
                        <h4 class="text-sm font-black text-slate-800 mb-4 text-left uppercase tracking-widest">Food Photo</h4>
                        <label class="block rounded-[28px] border-2 border-dashed border-slate-300 p-10 cursor-pointer hover:bg-slate-50 transition">
                            <Camera class="w-12 h-12 mx-auto text-slate-200 mb-2" />
                            <span class="text-xs font-black text-slate-400 uppercase tracking-tighter">Upload High-Res Photo</span>
                            <input type="file" class="hidden">
                        </label>
                        <div class="mt-4 flex items-center justify-center gap-2">
                            <input type="checkbox" v-model="modalForm.image" class="h-5 w-5 accent-orange-600">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Image is already uploaded</span>
                        </div>
                    </div>
                </div>
                <div class="p-8 border-t border-slate-200 flex justify-end gap-4 sticky bottom-0 bg-white z-[110]">
                    <button @click="showModal = false" class="rounded-2xl bg-slate-100 px-10 py-4 font-black text-slate-500 hover:bg-slate-200 transition">Cancel</button>
                    <button @click="saveItem" class="rounded-2xl bg-orange-600 text-white px-12 py-4 font-black shadow-xl shadow-orange-100 hover:bg-orange-700 transition active:scale-95 uppercase tracking-wider">
                        <Save class="w-5 h-5 inline-block mr-2" /> {{ editingItem ? 'Update' : 'Add' }} Item
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
