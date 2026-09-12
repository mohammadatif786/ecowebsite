<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import ConfirmDeleteDialog from '@/components/admin/ConfirmDeleteDialog.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { Check, CircleCheck, CircleX, Layers, Star, X } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    initialCategories: any[];
    initialStats: any;
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const SCOTIA_SHARE = 0.4;
const units = props.initialUnits;
const countries = props.initialCountries;
const categories = ref([...props.initialCategories]);
const categorySearch = ref('');

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

const getFilteredCountries = () => {
    return countries.filter(
        (c) =>
            (filters.value.region === 'All' || c.region === filters.value.region) &&
            (filters.value.country === 'All Countries' || c.country === filters.value.country),
    );
};

const countryFin = (c: any) => {
    let gross = 0, platform = 0, bank = 0, cost = 0;
    units.forEach((u) => {
        const v = (Number(c[u.key]) || 0) * getScale();
        gross += v;
        platform += v * u.platformRate;
        bank += v * u.bankRate;
        cost += v * u.costRate;
    });
    return { gross, platform, bank, cost, net: platform - cost };
};

const getTotals = () => {
    const rs = getFilteredCountries();
    const fins = rs.map(countryFin);
    return {
        gross: fins.reduce((s, x) => s + x.gross, 0),
        platform: fins.reduce((s, x) => s + x.platform, 0),
        bank: fins.reduce((s, x) => s + x.bank, 0),
        cost: fins.reduce((s, x) => s + x.cost, 0),
        net: fins.reduce((s, x) => s + x.net, 0),
        users: rs.reduce((s, c) => s + (Number(c.users) || 0), 0) * getScale(),
        merchants: rs.reduce((s, c) => s + (Number(c.merchants) || 0), 0),
        organizers: rs.reduce((s, c) => s + (Number(c.organizers) || 0), 0),
        countries: rs.length,
    };
};

// Category Specific Logic
const filteredCategories = computed(() => {
    const q = categorySearch.value.toLowerCase();
    return categories.value.filter(c => !q || [c.name, c.status, c.featured ? 'featured' : 'not featured'].join(' ').toLowerCase().includes(q));
});

const stats = computed(() => {
    const rows = filteredCategories.value;
    return {
        total: rows.length,
        featured: rows.filter(c => c.featured).length,
        active: rows.filter(c => c.status === 'Active').length,
        inactive: rows.filter(c => c.status === 'Inactive').length
    };
});

// Charts
const charts = ref<any>({});
const performanceChartRef = ref<HTMLCanvasElement | null>(null);
const statusChartRef = ref<HTMLCanvasElement | null>(null);

const renderCharts = () => {
    Object.values(charts.value).forEach((c: any) => c && c.destroy());

    const cats = filteredCategories.value;

    // Performance Chart
    const perfCtx = performanceChartRef.value;
    if (perfCtx) {
        charts.value.performance = new Chart(perfCtx, {
            type: 'bar',
            data: {
                labels: cats.map(c => c.name),
                datasets: [{
                    label: 'Events',
                    data: cats.map(c => c.events),
                    backgroundColor: '#8B5CF6',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: '#eef2f7' } }, x: { grid: { color: '#eef2f7' } } },
            }
        });
    }

    // Status Chart
    const statusCtx = statusChartRef.value;
    if (statusCtx) {
        charts.value.status = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Inactive', 'Featured'],
                datasets: [{
                    data: [
                        cats.filter(c => c.status === 'Active').length,
                        cats.filter(c => c.status === 'Inactive').length,
                        cats.filter(c => c.featured).length
                    ],
                    backgroundColor: ['#00C853', '#F43F5E', '#F59E0B']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
};

// Modal Logic
const showModal = ref(false);
const editingCategory = ref<any>(null);

const form = useForm({
    id: 0,
    name: '',
    status: 1, // Using numeric status as per backend expectation
    is_featured: false,
    image_object: null as string | null,
    category_image_file: null as File | null,
    _method: 'POST'
});

const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const openModal = (id: string | number | null = null) => {
    if (id) {
        const c = categories.value.find(x => x.id === id);
        if (c) {
            editingCategory.value = c;
            form.id = c.id;
            form.name = c.name;
            form.status = c.status === 'Active' ? 1 : 0;
            form.is_featured = c.featured;
            form.image_object = c.image;
            form._method = 'PUT';
            imagePreview.value = getCategoryImage(c.image || c.icon);
        }
    } else {
        editingCategory.value = null;
        form.reset();
        form.id = 0;
        form.status = 1;
        form.is_featured = false;
        form._method = 'POST';
        imagePreview.value = null;
    }
    showModal.value = true;
};

const handleImageUpload = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.category_image_file = file;

        const reader = new FileReader();
        reader.onload = (event) => {
            imagePreview.value = event.target?.result as string;
            form.image_object = imagePreview.value;
        };
        reader.readAsDataURL(file);
    }
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

const closeModal = () => {
    showModal.value = false;
    editingCategory.value = null;
    imagePreview.value = null;
    form.reset();
    form.clearErrors();
};

const saveCategory = () => {
    // Backup image_object and clear it before post to avoid payload size issues if sending file
    const imageObjectBackup = form.image_object;
    if (form.category_image_file) {
        form.image_object = null;
    }

    const isUpdate = form.id > 0;

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            toast.success(isUpdate ? 'Category updated successfully' : 'Category created successfully');
            // The page will reload via Inertia, props.initialCategories will update
            // We should sync categories ref with the new prop value if it changes,
            // but since it's a full page reload or partial reload, categories.value
            // should be refreshed. Let's watch the prop.
        },
        onError: () => {
            form.image_object = imageObjectBackup;
            toast.error(isUpdate ? 'Failed to update category' : 'Failed to create category');
        },
    };

    if (isUpdate) {
        form.post(route('admin.categories.update', form.id), options);
    } else {
        form.post(route('admin.categories.store'), options);
    }
};

const deletingCategoryId = ref<string | number | null>(null);
const showDeleteDialog = ref(false);
const deleteForm = useForm({});

const deleteCategory = (id: string | number) => {
    deletingCategoryId.value = id;
    showDeleteDialog.value = true;
};

function confirmDeleteCategory() {
    if (!deletingCategoryId.value) return;
    const url = route('admin.categories.destroy', deletingCategoryId.value);
    deleteForm.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Category deleted successfully');
        },
        onError: () => {
            toast.error('Failed to delete category');
        },
        onFinish: () => (showDeleteDialog.value = false),
    });
}

// Sync categories ref when props update from backend
watch(() => props.initialCategories, (newVal) => {
    categories.value = [...newVal];
    renderCharts();
}, { deep: true });

const getCategoryImage = (image: string) => {
    if (!image || image === '🏷️') return null;
    if (image.length <= 4) return null; // Likely an emoji
    if (image.startsWith('http://') || image.startsWith('https://')) return image;
    if (image.startsWith('/storage')) return image;
    return `/storage/${image}`;
};

const renderAll = async () => {
    await nextTick();
    const t = getTotals();
    const totalRev = t.platform + t.bank * (1 - SCOTIA_SHARE);
    const net = totalRev - t.cost;

    const set = (id: string, v: string) => {
        const e = document.getElementById(id);
        if (e) e.textContent = v;
    };
    set('rGTV', fmt(t.gross));
    set('rLinkUp', fmt(t.platform));
    set('rBank', fmt(t.bank));
    set('rNet', fmt(net));
    set('rUsers', num(t.users));
    set('rMerchants', num(t.merchants));
    set('rOrganizers', num(t.organizers));
    set('rCountries', num(t.countries));
    set('sideGTV', fmt(t.gross));

    renderCharts();
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
    <Head title="Event Categories" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eventCategoriesCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Event Categories" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="space-y-6 p-5 lg:p-8">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-purple-600">Event Categories</h3>
                        <p class="text-slate-500">Manage categories for organizing events and featured event discovery.</p>
                    </div>
                    <div class="flex gap-2">
                        <input
                            v-model="categorySearch"
                            class="rounded-2xl border border-slate-200 px-4 py-2 w-80"
                            placeholder="Search categories..."
                        />
                        <button @click="openModal()" class="rounded-2xl bg-purple-600 text-white px-5 py-2 font-black shadow-lg shadow-purple-200">
                            + Add Category
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-purple-100 text-purple-600 grid place-items-center"><Layers class="h-6 w-6" /></div>
                        <div><p class="text-slate-500">Total Categories</p><h3 class="text-3xl font-black">{{ num(stats.total) }}</h3></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-amber-100 text-amber-600 grid place-items-center"><Star class="h-6 w-6" /></div>
                        <div><p class="text-slate-500">Featured</p><h3 class="text-3xl font-black">{{ num(stats.featured) }}</h3></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-green-100 text-green-600 grid place-items-center"><CircleCheck class="h-6 w-6" /></div>
                        <div><p class="text-slate-500">Active</p><h3 class="text-3xl font-black">{{ num(stats.active) }}</h3></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-rose-100 text-rose-600 grid place-items-center"><CircleX class="h-6 w-6" /></div>
                        <div><p class="text-slate-500">Inactive</p><h3 class="text-3xl font-black">{{ num(stats.inactive) }}</h3></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 2xl:grid-cols-3 gap-6">
                    <div class="2xl:col-span-2 card rounded-3xl p-6">
                        <h3 class="text-xl font-black mb-4">Category Performance</h3>
                        <div class="relative h-[240px] w-full">
                            <canvas ref="performanceChartRef"></canvas>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="text-xl font-black mb-4">Category Status Mix</h3>
                        <div class="relative h-[240px] w-full">
                            <canvas ref="statusChartRef"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card rounded-3xl p-6">
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left">
                            <thead class="text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="py-3">Category</th>
                                    <th>Events</th>
                                    <th>Featured</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="c in filteredCategories" :key="c.id" class="border-t hover:bg-slate-50 transition">
                                    <td class="py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-12 w-12 rounded-2xl bg-slate-100 overflow-hidden flex-shrink-0 grid place-items-center text-2xl">
                                                <img
                                                    v-if="getCategoryImage(c.image || c.icon)"
                                                    :src="getCategoryImage(c.image || c.icon)"
                                                    class="w-full h-full object-cover"
                                                    :alt="c.name"
                                                />
                                                <span v-else>{{ c.icon || '🏷️' }}</span>
                                            </div>
                                            <b class="truncate max-w-[200px]">{{ c.name }}</b>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="rounded-full bg-purple-50 text-purple-700 px-3 py-1 text-xs font-black">
                                            {{ num(c.events) }} events
                                        </span>
                                    </td>
                                    <td>
                                        <button
                                            @click="c.featured = !c.featured; renderCharts();"
                                            class="rounded-full px-3 py-1 text-xs font-black transition border"
                                            :class="c.featured ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-slate-100 text-slate-500 border-transparent'"
                                        >
                                            ★ {{ c.featured ? 'Featured' : 'Not Featured' }}
                                        </button>
                                    </td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-xs font-black border" :class="c.status === 'Active' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-slate-100 text-slate-600 border-slate-200'">
                                            {{ c.status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex gap-1">
                                            <button @click="openModal(c.id)" class="rounded-xl bg-slate-100 px-3 py-2 hover:bg-slate-200 transition">✎</button>
                                            <button @click="deleteCategory(c.id)" class="rounded-xl bg-slate-100 px-3 py-2 hover:bg-rose-50 hover:text-rose-600 transition">🗑</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-5 backdrop-blur-md">
            <div class="bg-white rounded-[2rem] max-w-2xl w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
                <!-- Header -->
                <div class="p-6 bg-[#B111D8] text-white flex justify-between items-start">
                    <div>
                        <h3 class="text-3xl font-black">{{ editingCategory ? 'Edit Category' : 'Add Category' }}</h3>
                        <p class="text-purple-50 text-sm mt-1">Update category details below</p>
                    </div>
                    <button @click="closeModal" class="text-white hover:opacity-80 transition-opacity">
                        <X class="w-7 h-7" />
                    </button>
                </div>

                <div class="p-8 space-y-6 max-h-[75vh] overflow-y-auto scrollbar">
                    <!-- 1. Category Image Input -->
                    <div>
                        <p class="text-slate-800 font-bold mb-3">Category Image <span class="text-slate-400 font-medium">(Optional)</span></p>
                        <div
                            @click="triggerFileInput"
                            class="h-44 w-full rounded-[1.5rem] border-2 border-dashed border-slate-200 bg-slate-50 flex flex-col items-center justify-center cursor-pointer hover:border-purple-300 hover:bg-white transition-all overflow-hidden group relative"
                        >
                            <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover" />
                            <div v-else class="flex flex-col items-center">
                                <div class="h-20 w-20 rounded-full bg-amber-100 flex items-center justify-center mb-1">
                                    <span class="text-4xl">🏷️</span>
                                </div>
                            </div>
                        </div>
                        <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleImageUpload">
                    </div>

                    <!-- 2. Category Name Input -->
                    <div>
                        <label class="text-slate-800 font-bold block mb-3">Category Name</label>
                        <input
                            v-model="form.name"
                            class="w-full rounded-2xl border border-slate-100 bg-white px-5 py-4 text-lg font-medium focus:outline-none focus:ring-4 focus:ring-purple-50 focus:border-purple-200 transition-all shadow-sm"
                            placeholder="e.g., Music Festivals"
                        >
                        <p v-if="form.errors.name" class="mt-1 text-sm text-rose-500 font-bold ml-1">{{ form.errors.name }}</p>
                    </div>

                    <!-- 3. Status Selection -->
                    <div>
                        <p class="text-slate-800 font-bold mb-3">Status</p>
                        <div class="grid grid-cols-2 gap-4">
                            <button
                                @click="form.status = 1"
                                type="button"
                                class="rounded-[1.2rem] border-2 px-6 py-4 text-left flex items-center gap-4 transition-all"
                                :class="form.status === 1 ? 'border-green-500 bg-white shadow-sm' : 'border-slate-100 bg-white hover:border-slate-200 opacity-60'"
                            >
                                <span class="h-10 w-10 rounded-full flex items-center justify-center transition-colors" :class="form.status === 1 ? 'bg-green-500 text-white' : 'bg-slate-100 text-slate-400'">
                                    <CircleCheck class="h-6 w-6" />
                                </span>
                                <span class="text-xl font-black" :class="form.status === 1 ? 'text-slate-800' : 'text-slate-400'">Active</span>
                            </button>
                            <button
                                @click="form.status = 0"
                                type="button"
                                class="rounded-[1.2rem] border-2 px-6 py-4 text-left flex items-center gap-4 transition-all"
                                :class="form.status === 0 ? 'border-slate-400 bg-white shadow-sm' : 'border-slate-100 bg-white hover:border-slate-200 opacity-60'"
                            >
                                <span class="h-10 w-10 rounded-full flex items-center justify-center transition-colors" :class="form.status === 0 ? 'bg-slate-400 text-white' : 'bg-slate-50 text-slate-300'">
                                    <X class="h-6 w-6" />
                                </span>
                                <span class="text-xl font-black text-slate-400">Inactive</span>
                            </button>
                        </div>
                    </div>

                    <!-- 4. Featured Category -->
                    <div>
                        <p class="text-slate-800 font-bold mb-3">Featured Category</p>
                        <button
                            @click="form.is_featured = !form.is_featured"
                            type="button"
                            class="w-full rounded-[1.5rem] border border-slate-100 bg-white px-6 py-5 text-left flex items-center gap-5 transition-all shadow-sm hover:border-amber-200"
                        >
                            <div class="h-10 w-10 rounded-full bg-amber-400 text-white flex items-center justify-center shadow-lg shadow-amber-100">
                                <Star class="h-6 w-6 fill-current" />
                            </div>
                            <div>
                                <b class="text-xl block text-slate-800">Mark as Featured</b>
                                <span class="text-slate-500 text-sm">Display this category prominently</span>
                            </div>
                            <!-- Success indicator if featured -->
                            <div v-if="form.is_featured" class="ml-auto">
                                <div class="h-6 w-6 rounded-full bg-green-500 text-white flex items-center justify-center">
                                    <Check class="h-4 w-4" />
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-6 border-t border-slate-100 flex justify-end gap-3">
                    <button @click="closeModal" type="button" class="rounded-2xl bg-white border border-slate-200 text-slate-800 px-8 py-3.5 font-black hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button @click="saveCategory" :disabled="form.processing" class="rounded-2xl bg-[#B111D8] text-white px-8 py-3.5 font-black shadow-lg shadow-purple-200 hover:opacity-90 transition-opacity disabled:opacity-50 flex items-center gap-2">
                        <span v-if="form.processing" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                        {{ form.processing ? 'Saving...' : (editingCategory ? 'Update Category' : 'Create Category') }}
                    </button>
                </div>
            </div>
        </div>

        <ConfirmDeleteDialog
            v-model="showDeleteDialog"
            :form="deleteForm"
            title="Delete Category"
            description="Are you sure you want to delete this category? This action cannot be undone."
            @submit="confirmDeleteCategory"
        />

        <Toaster rich-colors position="top-right" />
    </div>
</template>
