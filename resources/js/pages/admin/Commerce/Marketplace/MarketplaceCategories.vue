<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';

import { Head, router, Link, useForm, usePage } from '@inertiajs/vue3';
import { Pencil, Plus, Search, Star, Tags, Trash2, X } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

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
    totalActive?: number;
}>();

// Flash feedback from the server (create / save / delete / blocked delete)
const page = usePage<any>();
const notice = ref<{ ok: boolean; text: string } | null>(null);
watch(() => [page.props.flash?.success, page.props.flash?.error, page.props.message], () => {
    const f = page.props.flash || {};
    if (f.error) notice.value = { ok: false, text: f.error };
    else if (f.success || page.props.message) notice.value = { ok: true, text: f.success || page.props.message };
}, { immediate: true });

// Search + status filter (server-side)
const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch([search, statusFilter], () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(route('admin.commerce.marketplace.categories'), { search: search.value || undefined, status: statusFilter.value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
    }, 300);
});

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

const openCreateModal = () => {
    editingCategory.value = null;
    categoryForm.reset();
    categoryForm.clearErrors();
    categoryForm.name = '';
    categoryForm.status = true;
    categoryForm.is_featured = false;
    showModal.value = true;
};

const openEditModal = (category: any) => {
    categoryForm.clearErrors();
    editingCategory.value = category;
    categoryForm.name = category.name;
    categoryForm.status = category.status;
    categoryForm.is_featured = category.is_featured;
    showModal.value = true;
};

const submitForm = () => {
    if (editingCategory.value) {
        categoryForm.put(route('admin.commerce.marketplace.categories.update', editingCategory.value.id), {
            preserveScroll: true,
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        categoryForm.post(route('admin.commerce.marketplace.categories.store'), {
            preserveScroll: true,
            onSuccess: () => { showModal.value = false; },
        });
    }
};

// Delete with an in-page confirmation (no browser pop-ups)
const deleting = ref<any>(null);
const deleteBusy = ref(false);
const deleteCategory = (category: any) => { deleting.value = category; };
const confirmDelete = () => {
    if (!deleting.value) return;
    deleteBusy.value = true;
    router.delete(route('admin.commerce.marketplace.categories.destroy', deleting.value.id), {
        preserveScroll: true,
        onFinish: () => { deleteBusy.value = false; deleting.value = null; },
    });
};
const deactivateInstead = (category: any) => {
    router.put(route('admin.commerce.marketplace.categories.update', category.id), { name: category.name, status: false, is_featured: false }, {
        preserveScroll: true,
        onFinish: () => { deleting.value = null; },
    });
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
                        <p class="text-slate-500 font-medium mt-1">These are the categories members see in the Marketplace. Inactive ones are hidden; featured ones show first.</p>
                    </div>
                    <button @click="openCreateModal" class="inline-flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 font-black text-white shadow-lg hover:scale-[1.02] transition">
                        <Plus class="w-5 h-5" /> New Category
                    </button>
                </div>

                <div v-if="notice" :class="notice.ok ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100'" class="flex items-start justify-between gap-3 rounded-2xl border p-4 font-bold">
                    <span>{{ notice.text }}</span>
                    <button @click="notice = null" class="opacity-60 hover:opacity-100"><X class="w-4 h-4" /></button>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm text-center">
                        <h3 class="text-4xl font-black text-slate-900">{{ totalActive ?? categories.total }}</h3>
                        <p class="text-slate-500 font-black text-[10px] uppercase tracking-widest mt-1">Active (shown to members)</p>
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
                    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center">
                        <div class="relative flex-1">
                            <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                            <input v-model="search" type="search" placeholder="Search categories…" class="w-full rounded-2xl border border-slate-200 py-3 pl-11 pr-4 font-bold" />
                        </div>
                        <select v-model="statusFilter" class="rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                            <option value="">All statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div
                            v-for="c in categories.data"
                            :key="c.id"
                            class="rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 transition hover:border-slate-200 hover:bg-white"
                            :class="{ 'opacity-60': !c.status }"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="flex items-center gap-2 text-lg font-black text-slate-900">
                                        <Star v-if="c.is_featured" class="h-4 w-4 shrink-0 fill-amber-400 text-amber-400" />
                                        <span class="truncate">{{ c.name }}</span>
                                    </p>
                                    <p class="mt-1 text-xs font-bold text-slate-500">{{ c.products_count }} product{{ c.products_count === 1 ? '' : 's' }}</p>
                                </div>
                                <span :class="c.status ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500'" class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-black uppercase">{{ c.status ? 'Active' : 'Inactive' }}</span>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <button @click="openEditModal(c)" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm hover:bg-slate-100">
                                    <Pencil class="h-3.5 w-3.5" /> Edit
                                </button>
                                <button @click="deleteCategory(c)" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-white px-3 py-2 text-xs font-black text-rose-600 shadow-sm hover:bg-rose-50">
                                    <Trash2 class="h-3.5 w-3.5" /> Delete
                                </button>
                            </div>
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
    <!-- Delete confirmation -->
    <div v-if="deleting" class="fixed inset-0 z-[110] grid place-items-center bg-slate-950/40 p-4 backdrop-blur-sm" @click.self="deleting = null">
        <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl">
            <template v-if="deleting.products_count > 0">
                <h3 class="text-2xl font-black">Can't delete "{{ deleting.name }}"</h3>
                <p class="mt-2 text-slate-600">It has {{ deleting.products_count }} product{{ deleting.products_count === 1 ? '' : 's' }}. Move them to another category first, or hide the category from members by setting it to Inactive.</p>
                <div class="mt-6 flex gap-3">
                    <button @click="deleting = null" class="flex-1 rounded-2xl bg-slate-100 py-3 font-black">Close</button>
                    <button v-if="deleting.status" @click="deactivateInstead(deleting)" class="flex-1 rounded-2xl bg-slate-950 py-3 font-black text-white">Set Inactive</button>
                </div>
            </template>
            <template v-else>
                <h3 class="text-2xl font-black">Delete "{{ deleting.name }}"?</h3>
                <p class="mt-2 text-slate-600">It disappears from the member Marketplace straight away. This can't be undone.</p>
                <div class="mt-6 flex gap-3">
                    <button @click="deleting = null" class="flex-1 rounded-2xl bg-slate-100 py-3 font-black">Cancel</button>
                    <button :disabled="deleteBusy" @click="confirmDelete" class="flex-1 rounded-2xl bg-rose-600 py-3 font-black text-white disabled:opacity-50">{{ deleteBusy ? 'Deleting…' : 'Delete' }}</button>
                </div>
            </template>
        </div>
    </div>
</template>
