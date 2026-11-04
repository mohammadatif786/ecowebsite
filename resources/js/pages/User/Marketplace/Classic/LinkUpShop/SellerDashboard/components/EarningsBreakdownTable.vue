<script setup lang="ts">
defineProps<{
    byPeriod: Array<{ date: string; earnings: number; orders: number; units_sold: number }>;
    byProduct: Array<{
        product_id: number;
        product_name: string;
        revenue: number;
        units_sold: number;
        order_count: number;
        percent_of_total: number;
    }>;
    activeTab: 'period' | 'product';
}>();

const fmt = (v: number) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 2 }).format(v);
</script>

<template>
    <div>
        <!-- Period Breakdown -->
        <div v-if="activeTab === 'period'">
            <table class="earn-table" v-if="byPeriod.length">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-right">Revenue</th>
                        <th class="text-right">Orders</th>
                        <th class="text-right">Units</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in byPeriod" :key="row.date">
                        <td class="date-col">{{ row.date }}</td>
                        <td class="text-right rev-col">{{ fmt(row.earnings) }}</td>
                        <td class="text-right">{{ row.orders }}</td>
                        <td class="text-right">{{ row.units_sold }}</td>
                    </tr>
                </tbody>
            </table>
            <div v-else class="empty-state">No earnings in this period.</div>
        </div>

        <!-- Product Breakdown -->
        <div v-else>
            <table class="earn-table" v-if="byProduct.length">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-right">Revenue</th>
                        <th class="text-right">Units</th>
                        <th class="text-right">Orders</th>
                        <th class="text-right">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in byProduct" :key="row.product_id">
                        <td class="prod-col">{{ row.product_name }}</td>
                        <td class="text-right rev-col">{{ fmt(row.revenue) }}</td>
                        <td class="text-right">{{ row.units_sold }}</td>
                        <td class="text-right">{{ row.order_count }}</td>
                        <td class="text-right">
                            <div class="pct-wrap">
                                <div class="pct-bar" :style="`width:${row.percent_of_total}%`"></div>
                                <span>{{ row.percent_of_total }}%</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-else class="empty-state">No product earnings in this period.</div>
        </div>
    </div>
</template>

<style scoped>
.earn-table { width: 100%; border-collapse: collapse; }
.earn-table th {
    font-size: .72rem; font-weight: 800; color: #64748b;
    text-transform: uppercase; letter-spacing: .05em;
    padding: 8px 12px; border-bottom: 2px solid rgba(148,163,184,.2);
    text-align: left;
}
.earn-table td {
    padding: 11px 12px; font-size: .85rem; color: #0f172a;
    border-bottom: 1px solid rgba(148,163,184,.1);
}
.earn-table tr:hover td { background: rgba(14,165,233,.04); }
.text-right { text-align: right !important; }
.date-col { font-variant-numeric: tabular-nums; color: #475569; }
.prod-col { font-weight: 700; }
.rev-col { font-weight: 800; color: #0ea5e9; }
.pct-wrap { display: flex; align-items: center; gap: 8px; justify-content: flex-end; }
.pct-bar {
    height: 6px; border-radius: 999px;
    background: linear-gradient(90deg, #0ea5e9, #22c55e);
    min-width: 4px; max-width: 80px;
    transition: width .4s ease;
}
.empty-state { text-align: center; color: #94a3b8; padding: 2rem 0; font-size: .9rem; }
</style>
