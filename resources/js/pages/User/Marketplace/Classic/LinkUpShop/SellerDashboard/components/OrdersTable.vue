<script setup lang="ts">
defineProps<{
    orders: {
        data: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
}>();

const emits = defineEmits<{ (e: 'open', order: any): void }>();

const statusStyle: Record<string, string> = {
    pending:    'badge-pending',
    processing: 'badge-processing',
    shipped:    'badge-shipped',
    delivered:  'badge-delivered',
    cancelled:  'badge-cancelled',
};

const fmt = (v: number) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 2 }).format(v);

const fmtDate = (d: string) =>
    new Date(d).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
</script>

<template>
    <div>
        <!-- Table -->
        <div class="table-wrap" v-if="orders.data.length">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Buyer</th>
                        <th>Items</th>
                        <th class="text-right">Processing Fee</th>
                        <th class="text-right">Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in orders.data" :key="order.id" class="order-row">
                        <td class="order-num">{{ order.number }}</td>
                        <td class="date-col">{{ fmtDate(order.created_at) }}</td>
                        <td>
                            <div class="buyer-cell">
                                <div class="buyer-avatar">
                                    <img v-if="order.customer?.avatar" :src="order.customer.avatar" alt="" />
                                    <span v-else>{{ order.customer?.name?.charAt(0) ?? 'B' }}</span>
                                </div>
                                <span class="buyer-name">{{ order.customer?.name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="items-col">{{ order.items?.length ?? 0 }} item(s)</td>
                        <td class="text-right total-col">{{ fmt(order.process_fee_amount ?? 0) }}</td>
                        <td class="text-right total-col">{{ fmt(order.total ?? 0) }}</td>
                        <td>
                            <span class="status-badge" :class="statusStyle[order.status] ?? ''">
                                {{ order.status }}
                            </span>
                        </td>
                        <td>
                            <button class="view-btn" @click="emits('open', order)">View →</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="empty-state">
            <div class="empty-icon">📦</div>
            <p>No orders found for the current filter.</p>
        </div>
    </div>
</template>

<style scoped>
.table-wrap { overflow-x: auto; border-radius: 16px; border: 1px solid rgba(148,163,184,.2); }
.orders-table { width: 100%; border-collapse: collapse; min-width: 700px; }
.orders-table th {
    padding: 11px 14px; font-size: .72rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .05em; color: #64748b;
    background: #f8fafc; border-bottom: 1px solid rgba(148,163,184,.2);
    text-align: left;
}
.orders-table td {
    padding: 13px 14px; font-size: .85rem; color: #0f172a;
    border-bottom: 1px solid rgba(148,163,184,.1);
    vertical-align: middle;
}
.order-row { transition: background .15s; }
.order-row:hover td { background: rgba(14,165,233,.04); }
.order-row:last-child td { border-bottom: none; }
.order-num { font-family: monospace; font-size: .8rem; font-weight: 800; color: #475569; }
.date-col { color: #64748b; white-space: nowrap; }
.buyer-cell { display: flex; align-items: center; gap: 8px; }
.buyer-avatar {
    width: 30px; height: 30px; border-radius: 50%;
    background: linear-gradient(135deg, #0ea5e9, #22c55e);
    display: flex; align-items: center; justify-content: center;
    font-size: .7rem; font-weight: 900; color: #fff; flex-shrink: 0; overflow: hidden;
}
.buyer-avatar img { width: 100%; height: 100%; object-fit: cover; }
.buyer-name { font-weight: 700; font-size: .85rem; }
.items-col { color: #64748b; }
.text-right { text-align: right; }
.total-col { font-weight: 800; color: #0ea5e9; }
.status-badge {
    font-size: .7rem; font-weight: 900; padding: 4px 10px;
    border-radius: 999px; text-transform: capitalize; white-space: nowrap;
}
.badge-pending    { background: #fef9c3; color: #854d0e; }
.badge-processing { background: #dbeafe; color: #1e40af; }
.badge-shipped    { background: #ede9fe; color: #6d28d9; }
.badge-delivered  { background: #dcfce7; color: #166534; }
.badge-cancelled  { background: #fee2e2; color: #991b1b; }
.view-btn {
    font-size: .78rem; font-weight: 800; color: #0ea5e9;
    background: rgba(14,165,233,.08); border: none; border-radius: 10px;
    padding: 5px 12px; cursor: pointer; transition: background .15s;
    white-space: nowrap;
}
.view-btn:hover { background: rgba(14,165,233,.18); }
.empty-state { text-align: center; padding: 3rem 0; color: #94a3b8; }
.empty-icon { font-size: 2.5rem; margin-bottom: .5rem; }
</style>
