<script setup lang="ts">
import { ref } from 'vue';
import { Toaster } from 'vue-sonner';
import 'vue-sonner/style.css';
import Header from './Components/Header.vue';
import StoreListingsGrid from './Components/StoreListingsGrid.vue';
import EditListingModal from './Components/EditListingModal.vue';

const props = defineProps<{
    products:   any[];
    merchants:  any[];
    categories: any[];
}>();

const editingProduct = ref<any>(null);
const search = ref('');

const filteredProducts = () => {
    if (!search.value.trim()) return props.products;
    const q = search.value.toLowerCase();
    return props.products.filter(p =>
        p.name.toLowerCase().includes(q) || p.category?.name?.toLowerCase().includes(q)
    );
};

const activeCount   = () => props.products.filter(p => p.status).length;
const inactiveCount = () => props.products.filter(p => !p.status).length;
</script>

<template>
    <div class="page-wrap">
        <Header />
        <main class="main-content">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">My Store</h1>
                    <p class="page-sub">{{ products.length }} listings · {{ activeCount() }} active · {{ inactiveCount() }} inactive</p>
                </div>
            </div>

            <!-- Store Stats -->
            <div class="store-stats">
                <div class="sstat-card card">
                    <!-- <div class="sstat-icon" style="background:rgba(34,197,94,.08)"></div> -->
                    <div><div class="sstat-label">Total Listings</div><div class="sstat-val">{{ products.length }}</div></div>
                </div>
                <div class="sstat-card card">
                    <!-- <div class="sstat-icon" style="background:rgba(14,165,233,.08)"></div> -->
                    <div><div class="sstat-label">Active</div><div class="sstat-val">{{ activeCount() }}</div></div>
                </div>
                <div class="sstat-card card">
                    <!-- <div class="sstat-icon" style="background:rgba(239,68,68,.06)"></div> -->
                    <div><div class="sstat-label">Inactive</div><div class="sstat-val">{{ inactiveCount() }}</div></div>
                </div>
                <div class="sstat-card card">
                    <!-- <div class="sstat-icon" style="background:rgba(168,85,247,.08)"></div> -->
                    <div><div class="sstat-label">Stores</div><div class="sstat-val">{{ merchants.length }}</div></div>
                </div>
            </div>

            <!-- Search & Grid -->
            <div class="card section-card">
                <!-- Search -->
                <div class="search-bar">
                    <div class="search-wrap">
                        <span class="search-icon"></span>
                        <input class="search-input" v-model="search" placeholder="Search listings…" />
                    </div>
                </div>

                <!-- Grid -->
                <StoreListingsGrid
                    :products="filteredProducts()"
                    :categories="categories"
                    @edit="editingProduct = $event"
                    @deleted="() => {}" />
            </div>

        </main>

        <!-- Edit Modal -->
        <EditListingModal
            :product="editingProduct"
            :categories="categories"
            @close="editingProduct = null" />

        <Toaster position="top-center"/>
    </div>
</template>

<style scoped>
.page-wrap { min-height: 100vh; background: #f5f7fb; color: #0f172a; }
.main-content { max-width: 1200px; margin: 0 auto; padding: 1.5rem 1rem; display: flex; flex-direction: column; gap: 1.25rem; }
.page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.page-title { font-size: 1.5rem; font-weight: 900; color: #0f172a; margin: 0; }
.page-sub   { font-size: .83rem; color: #64748b; margin: 3px 0 0; }

.store-stats { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
.sstat-card { display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-radius: 18px; background: #fff; border: 1px solid rgba(148,163,184,.2); box-shadow: 0 4px 18px rgba(2,6,23,.07); }
.sstat-icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.sstat-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; letter-spacing: .04em; }
.sstat-val { font-size: 1.4rem; font-weight: 900; color: #0f172a; line-height: 1.1; }

.card { background: #fff; border-radius: 22px; box-shadow: 0 8px 32px rgba(2,6,23,.08); border: 1px solid rgba(148,163,184,.2); }
.section-card { padding: 20px 22px; display: flex; flex-direction: column; gap: 18px; }

.search-bar { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.search-wrap { flex: 1; position: relative; min-width: 200px; max-width: 380px; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); }
.search-input { width: 100%; border: 1px solid rgba(148,163,184,.4); border-radius: 14px; padding: .65rem .9rem .65rem 2.2rem; outline: none; background: #f8fafc; font-size: .88rem; }
.search-input:focus { border-color: rgba(14,165,233,.6); box-shadow: 0 0 0 3px rgba(14,165,233,.1); background: #fff; }
</style>
