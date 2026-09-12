<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import ConfirmDeleteDialog from '@/components/admin/ConfirmDeleteDialog.vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import { PackagePlus, Plus, Search, MoreVertical, Edit3, Trash2, Eye, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    products: any; // Paginator object
    filters: any;
    categories: any[];
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const units = ref(props.initialUnits);
const countries = ref(props.initialCountries);

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');
const status = ref(props.filters.status || 'All');

watch([search, category, status], () => {
    debounceSearch();
});

let debounceTimeout: any = null;
const debounceSearch = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get(route('admin.commerce.marketplace.products'), {
            search: search.value,
            category: category.value,
            status: status.value,
        }, {
            preserveState: true,
            replace: true,
        });
    }, 400);
};

const deletingProductId = ref<number | null>(null);
const showDeleteDialog = ref(false);
const deleteForm = useForm({});

const deleteProduct = (id: number) => {
    deletingProductId.value = id;
    showDeleteDialog.value = true;
};

function confirmDeleteProduct() {
    if (!deletingProductId.value) return;
    const url = route('admin.commerce.marketplace.products.destroy', deletingProductId.value);
    deleteForm.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Product deleted successfully');
        },
        onError: () => {
            toast.error('Failed to delete product');
        },
        onFinish: () => (showDeleteDialog.value = false),
    });
}

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

const getScale = () => {
    const p = 'Monthly'; // Default period for marketplace page
    return 1;
};

const renderAll = async () => {
    // Try multiple times to ensure DOM elements in child components are ready
    for (let i = 0; i < 3; i++) {
        await nextTick();
        const s = getScale();
        const rs = countries.value;

        const t = rs.reduce((a, c) => {
            units.value.forEach(u => {
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
    <Head title="Marketplace Products" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="marketplaceProductsCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Marketplace Products" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="renderAll" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-slate-950 flex items-center gap-3"><PackagePlus class="w-8 h-8" /> Marketplace Products</h3>
                        <p class="text-slate-500 font-medium mt-1">Products listed by sellers and store groups across the LinkUp network.</p>
                    </div>
                    <Link :href="route('admin.commerce.marketplace.products.create')" class="rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-500 text-white px-6 py-3.5 font-black flex items-center gap-2 shadow-lg shadow-purple-600/20 transition hover:scale-105 active:scale-95">
                        <Plus class="w-5 h-5" /> Add Product
                    </Link>
                </div>

                <div class="card rounded-3xl bg-white border-slate-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex flex-col md:flex-row md:items-center gap-4">
                        <div class="relative flex-1">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <input v-model="search" type="text" placeholder="Search products, sellers, categories..." class="w-full pl-11 pr-4 py-3.5 rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-sky-500 font-bold text-sm" />
                        </div>
                        <div class="flex gap-2">
                            <select v-model="category" class="rounded-2xl bg-slate-50 border-transparent py-3.5 px-4 font-black text-sm text-slate-600 outline-none">
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <select v-model="status" class="rounded-2xl bg-slate-50 border-transparent py-3.5 px-4 font-black text-sm text-slate-600 outline-none">
                                <option value="All">All Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50/50 text-[10px] font-black uppercase text-slate-400 tracking-widest">
                                <tr>
                                    <th class="px-6 py-4">Product</th>
                                    <th class="px-6 py-4">Seller</th>
                                    <th class="px-6 py-4">Category</th>
                                    <th class="px-6 py-4">Price</th>
                                    <th class="px-6 py-4">Stock</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="p in products.data" :key="p.id" class="hover:bg-slate-50/50 transition group">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-xl overflow-hidden flex-shrink-0 border border-slate-100">
                                                <img v-if="p.cover_image" :src="p.cover_image" class="w-full h-full object-cover" />
                                                <div v-else class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-300 font-black">{{ p.name.charAt(0) }}</div>
                                            </div>
                                            <div>
                                                <div class="font-black text-slate-900 leading-tight">{{ p.name }}</div>
                                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter mt-1">ID: PRD-{{ p.id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 font-bold text-slate-700">{{ p.seller?.name || 'Unknown' }}</td>
                                    <td class="px-6 py-5">
                                        <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-wider">{{ p.category?.name || 'Uncategorized' }}</span>
                                    </td>
                                    <td class="px-6 py-5 font-black text-slate-900 text-base tracking-tighter">${{ Number(p.price).toFixed(2) }}</td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-700 w-6">{{ p.qty }}</span>
                                            <div class="h-1.5 w-12 rounded-full bg-slate-100 overflow-hidden">
                                                <div class="h-full bg-emerald-500" :style="{ width: Math.min(p.qty, 100) + '%' }"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm border"
                                            :class="p.status ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100'">
                                            {{ p.status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <Link :href="route('admin.commerce.marketplace.products.show', p.id)" class="h-9 w-9 rounded-xl bg-slate-100 grid place-items-center hover:bg-sky-50 hover:text-sky-600 transition text-slate-400"><Eye class="w-4 h-4" /></Link>
                                            <Link :href="route('admin.commerce.marketplace.products.edit', p.id)" class="h-9 w-9 rounded-xl bg-slate-100 grid place-items-center hover:bg-purple-50 hover:text-purple-600 transition text-slate-400"><Edit3 class="w-4 h-4" /></Link>
                                            <button @click="deleteProduct(p.id)" class="h-9 w-9 rounded-xl bg-slate-100 grid place-items-center hover:bg-rose-50 hover:text-rose-600 transition text-slate-400"><Trash2 class="w-4 h-4" /></button>
                                        </div>
                                        <div class="group-hover:hidden text-slate-300">
                                            <MoreVertical class="w-4 h-4 ml-auto" />
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!products.data.length">
                                    <td colspan="7" class="px-6 py-20 text-center">
                                        <div class="h-16 w-16 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-300 mx-auto mb-4">
                                            <PackagePlus class="w-8 h-8" />
                                        </div>
                                        <h4 class="text-lg font-black text-slate-900">No products found</h4>
                                        <p class="text-slate-500 font-medium text-sm mt-1">Try adjusting your filters or search query.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products.data.length" class="p-6 border-t border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">
                            Showing {{ products.from }} to {{ products.to }} of {{ products.total }} products
                        </p>
                        <div class="flex items-center gap-2">
                            <Link v-if="products.prev_page_url" :href="products.prev_page_url" class="h-10 px-4 rounded-xl bg-slate-50 flex items-center gap-2 text-xs font-black uppercase tracking-widest text-slate-600 hover:bg-slate-100 transition">
                                <ChevronLeft class="w-4 h-4" /> Previous
                            </Link>
                            <div v-else class="h-10 px-4 rounded-xl bg-slate-50 flex items-center gap-2 text-xs font-black uppercase tracking-widest text-slate-300 cursor-not-allowed">
                                <ChevronLeft class="w-4 h-4" /> Previous
                            </div>

                            <div class="h-10 px-4 rounded-xl bg-purple-50 flex items-center text-xs font-black uppercase tracking-widest text-purple-700">
                                Page {{ products.current_page }}
                            </div>

                            <Link v-if="products.next_page_url" :href="products.next_page_url" class="h-10 px-4 rounded-xl bg-slate-50 flex items-center gap-2 text-xs font-black uppercase tracking-widest text-slate-600 hover:bg-slate-100 transition">
                                Next <ChevronRight class="w-4 h-4" />
                            </Link>
                            <div v-else class="h-10 px-4 rounded-xl bg-slate-50 flex items-center gap-2 text-xs font-black uppercase tracking-widest text-slate-300 cursor-not-allowed">
                                Next <ChevronRight class="w-4 h-4" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <ConfirmDeleteDialog
            v-model="showDeleteDialog"
            :form="deleteForm"
            title="Delete Product"
            description="Are you sure you want to delete this product? This action cannot be undone."
            @submit="confirmDeleteProduct"
        />

        <Toaster rich-colors position="top-right" />
    </div>
</template>
