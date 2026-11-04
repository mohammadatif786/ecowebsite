<script setup lang="ts">
defineProps<{
    products: Array<{
        product_id: number;
        product_name: string;
        cover_image?: string;
        revenue: number;
        units_sold: number;
        order_count: number;
    }>;
}>();

const fmt = (v: number) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(v);
</script>

<template>
    <div class="top-table">
        <div v-if="!products.length" class="empty-state">No sales yet. List your first product to get started!</div>
        <div v-else>
            <div v-for="(p, i) in products" :key="p.product_id" class="top-row">
                <div class="rank" :class="['rank-1','rank-2','rank-3'][i] ?? 'rank-n'">
                </div>
                <div class="prod-img">
                    <img v-if="p.cover_image" :src="p.cover_image" alt="" />
                    <div v-else class="prod-placeholder"></div>
                </div>
                <div class="prod-info flex-1 min-w-0">
                    <div class="prod-name">{{ p.product_name }}</div>
                    <div class="prod-meta">{{ p.units_sold }} units · {{ p.order_count }} orders</div>
                </div>
                <div class="prod-rev">{{ fmt(p.revenue) }}</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.top-table { display: flex; flex-direction: column; gap: 0; }
.empty-state { text-align: center; color: #94a3b8; font-size: .9rem; padding: 2rem 0; }
.top-row {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid rgba(148, 163, 184, .18);
    transition: background .15s;
}
.top-row:last-child { border-bottom: none; }
.top-row:hover { background: rgba(14,165,233,.04); border-radius: 12px; padding-left: 8px; }
.rank { font-size: 1.2rem; width: 32px; text-align: center; flex-shrink: 0; }
.prod-img { width: 40px; height: 40px; border-radius: 10px; overflow: hidden; flex-shrink: 0; }
.prod-img img { width: 100%; height: 100%; object-fit: cover; }
.prod-placeholder { width: 40px; height: 40px; background: #f1f5f9; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
.prod-name { font-size: .88rem; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.prod-meta { font-size: .72rem; color: #94a3b8; margin-top: 1px; }
.prod-rev { font-size: .95rem; font-weight: 900; color: #0ea5e9; white-space: nowrap; }
</style>
