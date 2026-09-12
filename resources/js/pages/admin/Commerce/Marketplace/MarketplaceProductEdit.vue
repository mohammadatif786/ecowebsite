<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { PackagePlus, ChevronLeft, Edit3 } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import MarketplaceProductForm from './components/MarketplaceProductForm.vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    product: any;
    categories: any[];
    sellers: any[];
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
    <Head :title="`Edit ${product.name}`" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="marketplaceProductsCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Marketplace Products" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <button @click="$inertia.visit(route('admin.commerce.marketplace.products'))" class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-slate-200 transition">
                                <ChevronLeft class="w-4 h-4" />
                            </button>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Back to products</span>
                        </div>
                        <h3 class="text-3xl font-black text-slate-950 flex items-center gap-3"><Edit3 class="w-8 h-8" /> Edit Product</h3>
                        <p class="text-slate-500 font-medium mt-1">Modifying: <span class="text-purple-600 font-black">{{ product.name }}</span></p>
                    </div>
                </div>

                <MarketplaceProductForm :product="product" :categories="categories" :sellers="sellers" />
            </section>
        </main>
    </div>
</template>
