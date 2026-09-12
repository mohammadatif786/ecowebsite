<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';

import { Head, router, Link, useForm } from '@inertiajs/vue3';
import { Tags, Trash2, X } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    categories: any; // Paginator
    filters: any;
    totalProducts: number;
    totalCountries: number;
    totalFeatured: number;
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const units = ref(props.initialUnits);
const countries = ref(props.initialCountries);

// Modal Logic
const showModal = ref(false);
const editingCategory = ref<any>(null);
const categoryForm = useForm({
    name: '',
    status: true,
    is_featured: false,
});

const openEditModal = (category: any) => {
    editingCategory.value = category;
    categoryForm.name = category.name;
    categoryForm.status = category.status;
    categoryForm.is_featured = category.is_featured;
    showModal.value = true;
};

const submitForm = () => {
    if (editingCategory.value) {
        categoryForm.put(route('admin.commerce.marketplace.categories.update', editingCategory.value.id), {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        categoryForm.post(route('admin.commerce.marketplace.categories.store'), {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
};

const deleteCategory = (category: any) => {
    if (category.products_count > 0) {
        alert('Cannot delete category with associated products.');
        return;
    }
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(route('admin.commerce.marketplace.categories.destroy', category.id));
    }
};

const formatCurrency = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(n);
const formatNumber = (n: number) => new Intl.NumberFormat('en-US').format(n);

const ribbonMetrics = computed(() => {
    const rs = countries.value;
    const t = rs.reduce((a, c) => {
        units.value.forEach(u => { a.gtv += (Number(c[u.key]) || 0); });
        a.users += (Number(c.users) || 0);
        a.merchants += (Number(c.merchants) || 0);
        a.organizers += (Number(c.organizers) || 0);
        return a;
    }, { gtv: 0, users: 0, merchants: 0, organizers: 0 });

    return {
        gtv: fmt(t.gtv),
        linkupRev: fmt(t.gtv * 0.12),
        procPool: fmt(t.gtv * 0.03),
        netProfit: fmt(t.gtv * 0.09),
        users: num(t.users),
        merchants: num(t.merchants),
        organizers: num(t.organizers),
        countries: num(rs.length)
    };
});

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

const renderAll = async () => {
    for (let i = 0; i < 3; i++) {
        await nextTick();
        const rs = countries.value;
        const t = rs.reduce((a, c) => {
            units.value.forEach(u => { a.gtv += (Number(c[u.key]) || 0); });
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
    <Head title="Marketplace Categories" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="marketplaceCategoriesCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Marketplace Categories" :countries="countries" :metrics="ribbonMetrics" @toggle-sidebar="toggleSidebar" @filter-change="renderAll" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-slate-950 flex items-center gap-3"><Tags class="w-8 h-8" /> Marketplace Categories</h3>
                        <p class="text-slate-500 font-medium mt-1">Organize products for shopping discovery and featured collections.</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm text-center">
                        <h3 class="text-4xl font-black text-slate-900">{{ categories.total }}</h3>
                        <p class="text-slate-500 font-black text-[10px] uppercase tracking-widest mt-1">Categories</p>
                    </div>
                    <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm text-center">
                        <h3 class="text-4xl font-black text-sky-600">{{ totalFeatured }}</h3>
                        <p class="text-slate-500 font-black text-[10px] uppercase tracking-widest mt-1">Featured</p>
                    </div>
                    <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm text-center">
                        <h3 class="text-4xl font-black text-emerald-600">{{ totalProducts }}</h3>
                        <p class="text-slate-500 font-black text-[10px] uppercase tracking-widest mt-1">Products</p>
                    </div>
                    <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm text-center">
                        <h3 class="text-4xl font-black text-amber-600">{{ totalCountries }}</h3>
                        <p class="text-slate-500 font-black text-[10px] uppercase tracking-widest mt-1">Countries</p>
                    </div>
                </div>

                <!-- Categories Grid -->
                <div class="card rounded-3xl bg-white border-slate-100 shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div
                            v-for="c in categories.data"
                            :key="c.id"
                            @click="openEditModal(c)"
                            role="button"
                            tabindex="0"
                            @keydown.enter="openEditModal(c)"
                            class="group relative rounded-2xl bg-slate-100 hover:bg-slate-200 transition-colors px-6 py-5 text-left cursor-pointer"
                            :class="{ 'opacity-50': !c.status }"
                        >
                            <span class="text-lg font-black text-slate-900">{{ c.name }}</span>
                            <button
                                @click.stop="deleteCategory(c)"
                                class="absolute top-1/2 right-3 -translate-y-1/2 h-8 w-8 rounded-xl bg-white/0 grid place-items-center opacity-0 group-hover:opacity-100 hover:bg-white hover:text-rose-600 transition text-slate-400"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Empty State -->
                        <div v-if="!categories.data.length" class="col-span-full py-20 text-center">
                            <div class="h-16 w-16 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-300 mx-auto mb-4">
                                <Tags class="w-8 h-8" />
                            </div>
                            <h4 class="text-lg font-black text-slate-900">No categories found</h4>
                            <p class="text-slate-500 font-medium text-sm mt-1">Try a different search term or add a new category.</p>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="categories.data.length" class="flex items-center justify-between py-6">
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">
                        Showing {{ categories.from }} - {{ categories.to }} of {{ categories.total }}
                    </p>
                    <div class="flex items-center gap-2">
                        <Link v-if="categories.prev_page_url" :href="categories.prev_page_url" class="h-10 px-4 rounded-xl bg-slate-100 flex items-center text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition">Prev</Link>
                        <div class="h-10 px-4 rounded-xl bg-purple-600 text-white flex items-center text-xs font-black uppercase tracking-widest">Page {{ categories.current_page }}</div>
                        <Link v-if="categories.next_page_url" :href="categories.next_page_url" class="h-10 px-4 rounded-xl bg-slate-100 flex items-center text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition">Next</Link>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[100] grid place-items-center p-4 bg-slate-950/40 backdrop-blur-sm">
        <div class="w-full max-w-lg bg-white rounded-[40px] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
            <div class="p-8 lg:p-10 space-y-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-3xl font-black text-slate-950">{{ editingCategory ? 'Edit Category' : 'New Category' }}</h3>
                        <p class="text-slate-500 font-medium mt-1">Organize products into meaningful groups.</p>
                    </div>
                    <button @click="showModal = false" class="h-12 w-12 rounded-2xl bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-900 transition flex items-center justify-center">
                        <X class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Category Name</label>
                        <input v-model="categoryForm.name" type="text" placeholder="e.g. Fashion, Electronics..."
                            class="w-full rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-sky-500 font-bold text-sm py-4 px-5 transition-all" />
                        <div v-if="categoryForm.errors.name" class="text-rose-500 text-[10px] font-black uppercase ml-1">{{ categoryForm.errors.name }}</div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Status</label>
                        <div class="flex items-center gap-6 py-2 px-1">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" :value="true" v-model="categoryForm.status" class="hidden" />
                                <div class="w-6 h-6 rounded-full border-2 border-slate-200 flex items-center justify-center transition group-hover:border-emerald-500" :class="{ 'border-emerald-500 bg-emerald-500': categoryForm.status === true }">
                                    <div class="w-2.5 h-2.5 rounded-full bg-white"></div>
                                </div>
                                <span class="text-sm font-bold" :class="categoryForm.status === true ? 'text-slate-900' : 'text-slate-500'">Active</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" :value="false" v-model="categoryForm.status" class="hidden" />
                                <div class="w-6 h-6 rounded-full border-2 border-slate-200 flex items-center justify-center transition group-hover:border-rose-500" :class="{ 'border-rose-500 bg-rose-500': categoryForm.status === false }">
                                    <div class="w-2.5 h-2.5 rounded-full bg-white"></div>
                                </div>
                                <span class="text-sm font-bold" :class="categoryForm.status === false ? 'text-slate-900' : 'text-slate-500'">Inactive</span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Featured</label>
                        <label class="flex items-center gap-3 cursor-pointer group py-2 px-1 w-fit">
                            <div class="w-6 h-6 rounded-lg border-2 border-slate-200 flex items-center justify-center transition group-hover:border-sky-500" :class="{ 'border-sky-500 bg-sky-500': categoryForm.is_featured }">
                                <X v-if="!categoryForm.is_featured" class="w-3.5 h-3.5 opacity-0" />
                                <div v-else class="w-2.5 h-2.5 rounded-sm bg-white"></div>
                            </div>
                            <input type="checkbox" v-model="categoryForm.is_featured" class="hidden" />
                            <span class="text-sm font-bold" :class="categoryForm.is_featured ? 'text-slate-900' : 'text-slate-500'">Show in featured collections</span>
                        </label>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="submit" :disabled="categoryForm.processing"
                            class="flex-1 rounded-2xl bg-slate-950 text-white py-4 font-black shadow-lg shadow-slate-900/20 hover:scale-[1.02] active:scale-95 transition disabled:opacity-50">
                            {{ editingCategory ? 'Save Changes' : 'Create Category' }}
                        </button>
                        <button type="button" @click="showModal = false"
                            class="flex-1 rounded-2xl bg-slate-100 text-slate-600 py-4 font-black hover:bg-slate-200 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
