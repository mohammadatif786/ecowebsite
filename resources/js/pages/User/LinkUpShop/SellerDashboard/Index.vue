<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Toaster } from 'vue-sonner';
import 'vue-sonner/style.css';
import Header from './Components/Header.vue';
import StatCard from './Components/StatCard.vue';
import SalesTrendChart from './Components/SalesTrendChart.vue';
import TopProductsTable from './Components/TopProductsTable.vue';

const props = defineProps<{
    summary: {
        total_revenue:   number;
        pending_revenue: number;
        total_orders:    number;
        total_products:  number;
        units_sold:      number;
        avg_order_value: number;
    };
    salesTrend: Array<{ date: string; revenue: number; orders: number }>;
    topProducts: any[];
    productPerf: any[];
    days: number;
    walletBalance: number;
}>();

const activeDays = ref(props.days);

const switchDays = (d: number) => {
    activeDays.value = d;
    router.get('/seller/dashboard', { days: d }, { preserveScroll: true });
};

const fmt = (v: number) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(v);
</script>

<template>
    <div class="page-wrap">
        <Header />
        <main class="main-content">

            <!-- Page Title -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Analytics Dashboard</h1>
                    <p class="page-sub">Your store performance at a glance</p>
                </div>
                <div class="period-switcher">
                    <button v-for="d in [7, 30, 90]" :key="d"
                        class="period-btn" :class="activeDays === d ? 'period-active' : ''"
                        @click="switchDays(d)">{{ d }}d</button>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="stats-grid">
                <StatCard label="Total Revenue" :value="fmt(summary.total_revenue)"
                    icon="" gradient="linear-gradient(135deg,rgba(14,165,233,.18),rgba(14,165,233,.06))" />
                <StatCard label="Pending Revenue" :value="fmt(summary.pending_revenue || 0)"
                    icon="" gradient="linear-gradient(135deg,rgba(245,158,11,.18),rgba(245,158,11,.06))" />
                <StatCard label="Total Orders" :value="summary.total_orders"
                    icon="" gradient="linear-gradient(135deg,rgba(34,197,94,.18),rgba(34,197,94,.06))" />
                <StatCard label="Active Products" :value="summary.total_products"
                    icon="" gradient="linear-gradient(135deg,rgba(168,85,247,.18),rgba(168,85,247,.06))" />
                <StatCard label="Avg Order Value" :value="fmt(summary.avg_order_value)"
                    icon="" gradient="linear-gradient(135deg,rgba(245,158,11,.18),rgba(245,158,11,.06))" />
                <StatCard label="Units Sold" :value="summary.units_sold"
                    icon="" gradient="linear-gradient(135deg,rgba(236,72,153,.18),rgba(236,72,153,.06))" />
                <StatCard label="Wallet Balance" :value="fmt(walletBalance)"
                    icon="" gradient="linear-gradient(135deg,rgba(59,130,246,.18),rgba(59,130,246,.06))" />
            </div>

            <!-- Sales Trend -->
            <div class="card section-card">
                <div class="section-head">
                    <div class="section-title">Sales Trend</div>
                    <div class="section-sub">Revenue over the last {{ activeDays }} days</div>
                </div>
                <SalesTrendChart :data="salesTrend" :days="activeDays" />
            </div>

            <!-- Bottom: Top Products + Product Performance -->
            <div class="bottom-grid">
                <!-- Top Products -->
                <div class="card section-card">
                    <div class="section-head">
                        <div class="section-title">Top Products</div>
                        <div class="section-sub">By revenue all-time</div>
                    </div>
                    <TopProductsTable :products="topProducts" />
                </div>

                <!-- Product Performance -->
                <div class="card section-card">
                    <div class="section-head">
                        <div class="section-title"> Product Analytics</div>
                        <div class="section-sub">Views · Clicks · Conversions</div>
                    </div>
                    <div v-if="productPerf.length" class="perf-table-wrap">
                        <table class="perf-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="tr">Views</th>
                                    <th class="tr">Clicks</th>
                                    <th class="tr">Conv.</th>
                                    <th class="tr">Conv. Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in productPerf" :key="p.product_id">
                                    <td class="prod-name-cell">{{ p.product_name }}</td>
                                    <td class="tr num-cell">{{ p.total_views.toLocaleString() }}</td>
                                    <td class="tr num-cell">{{ p.total_clicks.toLocaleString() }}</td>
                                    <td class="tr num-cell">{{ p.total_conversions }}</td>
                                    <td class="tr">
                                        <span class="conv-chip" :class="p.conversion_rate > 3 ? 'conv-good' : p.conversion_rate > 1 ? 'conv-ok' : 'conv-low'">
                                            {{ p.conversion_rate }}%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="empty-perf">Analytics data will appear once product views are tracked.</div>
                </div>
            </div>

        </main>
        <Toaster position="top-center"/>
    </div>
</template>

<style scoped>
.page-wrap { min-height: 100vh; background: #f5f7fb; color: #0f172a; }
.main-content { max-width: 1280px; margin: 0 auto; padding: 1.5rem 1rem; display: flex; flex-direction: column; gap: 1.5rem; }
.page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.page-title { font-size: 1.5rem; font-weight: 900; color: #0f172a; margin: 0; }
.page-sub   { font-size: .85rem; color: #64748b; margin: 3px 0 0; }
.period-switcher { display: flex; gap: 6px; background: #fff; border-radius: 14px; padding: 5px; border: 1px solid rgba(148,163,184,.25); box-shadow: 0 2px 12px rgba(2,6,23,.06); }
.period-btn { font-size: .78rem; font-weight: 800; padding: 5px 14px; border-radius: 10px; border: none; cursor: pointer; background: transparent; color: #64748b; transition: all .15s; }
.period-active { background: linear-gradient(135deg,#0ea5e9,#22c55e); color: #fff; box-shadow: 0 4px 14px rgba(14,165,233,.3); }
.stats-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
.card { background: #fff; border-radius: 22px; box-shadow: 0 8px 32px rgba(2,6,23,.08); border: 1px solid rgba(148,163,184,.2); }
.section-card { padding: 20px 22px; display: flex; flex-direction: column; gap: 14px; }
.section-head { display: flex; flex-direction: column; gap: 2px; }
.section-title { font-size: 1rem; font-weight: 900; color: #0f172a; }
.section-sub   { font-size: .75rem; color: #94a3b8; }
.bottom-grid { display: grid; gap: 1.5rem; grid-template-columns: 1fr; }
@media(min-width:1024px) { .bottom-grid { grid-template-columns: 1fr 1fr; } }
.perf-table-wrap { overflow-x: auto; }
.perf-table { width: 100%; border-collapse: collapse; min-width: 400px; }
.perf-table th { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; padding: 6px 10px; border-bottom: 1px solid rgba(148,163,184,.15); text-align: left; }
.perf-table td { padding: 9px 10px; font-size: .82rem; border-bottom: 1px solid rgba(148,163,184,.08); color: #0f172a; }
.perf-table tr:last-child td { border-bottom: none; }
.perf-table tr:hover td { background: rgba(14,165,233,.04); }
.tr { text-align: right; }
.prod-name-cell { font-weight: 700; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.num-cell { font-variant-numeric: tabular-nums; color: #475569; }
.conv-chip { font-size: .68rem; font-weight: 900; padding: 3px 8px; border-radius: 999px; }
.conv-good { background: #dcfce7; color: #166534; }
.conv-ok   { background: #fef9c3; color: #854d0e; }
.conv-low  { background: #f1f5f9;  color: #64748b; }
.empty-perf { text-align: center; color: #94a3b8; font-size: .85rem; padding: 1.5rem 0; }
</style>
