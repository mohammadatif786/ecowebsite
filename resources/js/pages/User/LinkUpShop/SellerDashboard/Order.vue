<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Toaster } from 'vue-sonner';
import 'vue-sonner/style.css';
import Header from './Components/Header.vue';
import OrdersTable from './Components/OrdersTable.vue';
import OrderDetailModal from './Components/OrderDetailModal.vue';

const props = defineProps<{
    orders: {
        data: any[];
        current_page: number;
        last_page: number;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: { search?: string; status?: string };
}>();

const search  = ref(props.filters.search ?? '');
const status  = ref(props.filters.status ?? '');
const selectedOrder = ref<any>(null);

const STATUS_OPTS = ['', 'pending', 'processing', 'shipped', 'delivered', 'cancelled'];

const applyFilters = () => {
    router.get('/seller/orders', { search: search.value, status: status.value }, { preserveScroll: true });
};

const paginate = (url: string | null) => {
    if (url) router.get(url, {}, { preserveScroll: true });
};
</script>

<template>
    <div class="page-wrap">
        <Header />
        <main class="main-content">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Orders</h1>
                    <p class="page-sub">{{ orders.total }} total orders · Page {{ orders.current_page }} of {{ orders.last_page }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="card filter-bar">
                <div class="filter-inner">
                    <div class="search-wrap">
                        <span class="search-icon">🔍</span>
                        <input class="search-input" v-model="search" @keyup.enter="applyFilters"
                            placeholder="Search by order number…" />
                    </div>
                    <select class="input status-select" v-model="status" @change="applyFilters">
                        <option v-for="s in STATUS_OPTS" :key="s" :value="s">
                            {{ s ? s.charAt(0).toUpperCase() + s.slice(1) : 'All Statuses' }}
                        </option>
                    </select>
                    <button class="btn" @click="applyFilters">Filter</button>
                </div>
            </div>

            <!-- Status chips quick-filter -->
            <div class="chip-row">
                <button v-for="s in STATUS_OPTS" :key="s"
                    class="chip" :class="status === s ? 'chip-active' : ''"
                    @click="status = s; applyFilters()">
                    {{ s || 'All' }}
                </button>
            </div>

            <!-- Table -->
            <div class="card section-card">
                <OrdersTable :orders="orders" @open="selectedOrder = $event" />

                <!-- Pagination -->
                <div v-if="orders.last_page > 1" class="pagination">
                    <button class="page-btn" :disabled="!orders.prev_page_url" @click="paginate(orders.prev_page_url)">← Prev</button>
                    <span class="page-info">{{ orders.current_page }} / {{ orders.last_page }}</span>
                    <button class="page-btn" :disabled="!orders.next_page_url" @click="paginate(orders.next_page_url)">Next →</button>
                </div>
            </div>

        </main>

        <!-- Order Detail Modal -->
        <OrderDetailModal :order="selectedOrder" @close="selectedOrder = null" />

        <Toaster position="top-center"/>
    </div>
</template>

<style scoped>
.page-wrap { min-height: 100vh; background: #f5f7fb; color: #0f172a; }
.main-content { max-width: 1200px; margin: 0 auto; padding: 1.5rem 1rem; display: flex; flex-direction: column; gap: 1.25rem; }
.page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.page-title { font-size: 1.5rem; font-weight: 900; color: #0f172a; margin: 0; }
.page-sub   { font-size: .83rem; color: #64748b; margin: 3px 0 0; }

.card { background: #fff; border-radius: 22px; box-shadow: 0 8px 32px rgba(2,6,23,.08); border: 1px solid rgba(148,163,184,.2); }
.filter-bar { padding: 14px 18px; }
.filter-inner { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.search-wrap { flex: 1; position: relative; min-width: 200px; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: .9rem; }
.search-input { width: 100%; border: 1px solid rgba(148,163,184,.45); border-radius: 14px; padding: .65rem .9rem .65rem 2.2rem; outline: none; font-size: .88rem; background: #f8fafc; }
.search-input:focus { border-color: rgba(14,165,233,.6); box-shadow: 0 0 0 3px rgba(14,165,233,.1); background: #fff; }
.status-select { border: 1px solid rgba(148,163,184,.45); border-radius: 14px; padding: .65rem .9rem; outline: none; background: #f8fafc; font-size: .88rem; cursor: pointer; }
.input { font-size: .88rem; }
.btn { background: linear-gradient(135deg,#0ea5e9,#22c55e); color: #fff; border: none; border-radius: 14px; font-weight: 900; padding: .65rem 1.2rem; cursor: pointer; box-shadow: 0 6px 18px rgba(14,165,233,.25); transition: opacity .15s; white-space: nowrap; font-size: .87rem; }
.btn:hover { opacity: .9; }

.chip-row { display: flex; gap: 8px; flex-wrap: wrap; }
.chip { border: 1.5px solid rgba(148,163,184,.3); border-radius: 999px; padding: 4px 14px; font-size: .75rem; font-weight: 800; background: #fff; cursor: pointer; text-transform: capitalize; transition: all .15s; color: #64748b; }
.chip-active { border-color: #0ea5e9; background: rgba(14,165,233,.08); color: #0284c7; }
.chip:hover:not(.chip-active) { border-color: rgba(14,165,233,.4); }

.section-card { padding: 20px; display: flex; flex-direction: column; gap: 16px; }

.pagination { display: flex; align-items: center; justify-content: center; gap: 14px; padding-top: 8px; border-top: 1px solid rgba(148,163,184,.12); }
.page-btn { font-size: .82rem; font-weight: 800; padding: 6px 16px; border-radius: 12px; border: 1.5px solid rgba(148,163,184,.3); background: #fff; cursor: pointer; color: #475569; transition: all .15s; }
.page-btn:hover:not(:disabled) { border-color: #0ea5e9; color: #0284c7; }
.page-btn:disabled { opacity: .4; cursor: not-allowed; }
.page-info { font-size: .82rem; color: #94a3b8; font-weight: 700; }
</style>
