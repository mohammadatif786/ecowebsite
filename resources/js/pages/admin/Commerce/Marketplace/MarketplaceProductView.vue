<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head, Link } from '@inertiajs/vue3';
import { PackagePlus, ChevronLeft, Edit3, Store, Tag, Box, DollarSign, CheckCircle2, XCircle } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    product: any;
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

const countries = ref(props.initialCountries);
const filters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });

const handleFilterChange = (newFilters: any) => {
    filters.value = newFilters;
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head :title="`View ${product.name}`" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="marketplaceProductsCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Marketplace Products" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <button @click="$inertia.visit(route('admin.commerce.marketplace.products'))" class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-slate-200 transition">
                                <ChevronLeft class="w-4 h-4" />
                            </button>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Back to products</span>
                        </div>
                        <h3 class="text-3xl font-black text-slate-950 flex items-center gap-3"><PackagePlus class="w-8 h-8" /> Product Details</h3>
                    </div>
                    <Link :href="route('admin.commerce.marketplace.products.edit', product.id)"
                        class="rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-500 text-white px-6 py-3.5 font-black flex items-center gap-2 shadow-lg shadow-purple-600/20 transition hover:scale-105 active:scale-95">
                        <Edit3 class="w-5 h-5" /> Edit Product
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="card rounded-3xl bg-white border-slate-100 shadow-sm overflow-hidden">
                            <div class="relative h-64 md:h-96">
                                <img :src="product.cover_image" class="w-full h-full object-cover" />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 to-transparent flex items-end p-8">
                                    <div>
                                        <span class="px-3 py-1 rounded-lg bg-white/20 backdrop-blur-md text-white text-[10px] font-black uppercase tracking-widest">{{ product.category?.name }}</span>
                                        <h1 class="text-4xl font-black text-white mt-2">{{ product.name }}</h1>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8 space-y-6">
                                <div class="space-y-2">
                                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-400">Description</h4>
                                    <p class="text-slate-600 font-medium leading-relaxed">{{ product.description }}</p>
                                </div>

                                <div v-if="product.images && product.images.length" class="space-y-4">
                                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-400">Gallery</h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                        <div v-for="(img, idx) in product.images" :key="idx" class="aspect-square rounded-2xl overflow-hidden border-2 border-slate-50">
                                            <img :src="img" class="w-full h-full object-cover hover:scale-110 transition duration-500" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Stats & Info -->
                    <div class="space-y-6">
                        <div class="card rounded-3xl bg-white border-slate-100 shadow-sm p-6 space-y-6">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight">Status & Inventory</h4>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm border"
                                    :class="product.status ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100'">
                                    {{ product.status ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 gap-4">
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600">
                                        <DollarSign class="w-6 h-6" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Price</p>
                                        <p class="text-xl font-black text-slate-900">${{ product.price }}</p>
                                    </div>
                                </div>
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-xl bg-sky-100 flex items-center justify-center text-sky-600">
                                        <Box class="w-6 h-6" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">In Stock</p>
                                        <p class="text-xl font-black text-slate-900">{{ product.qty }} units</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4 pt-4 border-t border-slate-50">
                                <div class="flex items-center gap-3">
                                    <Store class="w-5 h-5 text-slate-400" />
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Seller</p>
                                        <p class="font-bold text-slate-700 mt-0.5">{{ product.seller?.name || 'Unknown' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <Tag class="w-5 h-5 text-slate-400" />
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Listing Type</p>
                                        <p class="font-bold text-slate-700 mt-0.5">{{ product.listing_type || 'Standard' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card rounded-3xl bg-slate-900 p-6 text-white space-y-4">
                            <h4 class="text-sm font-black uppercase tracking-widest opacity-60">Admin Overview</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center text-sm font-bold">
                                    <span class="opacity-60">Total Sales</span>
                                    <span>$0.00</span>
                                </div>
                                <div class="flex justify-between items-center text-sm font-bold">
                                    <span class="opacity-60">Revenue (Fee)</span>
                                    <span>$0.00</span>
                                </div>
                                <div class="flex justify-between items-center text-sm font-bold border-t border-white/10 pt-3">
                                    <span class="opacity-60">Created At</span>
                                    <span>{{ new Date(product.created_at).toLocaleDateString() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
