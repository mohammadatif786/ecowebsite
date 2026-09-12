<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import axios from 'axios';

const props = defineProps<{ order: any | null }>();
const emit = defineEmits<{ (e: 'close'): void }>();

const detail = ref<any>(null);
const loading = ref(false);
const newStatus = ref('');
const updating = ref(false);

const STATUS_OPTIONS = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

const statusStyle: Record<string, string> = {
    pending: 'badge-pending', processing: 'badge-processing',
    shipped: 'badge-shipped', delivered: 'badge-delivered', cancelled: 'badge-cancelled',
};

watch(() => props.order, async (o) => {
    if (!o) { detail.value = null; return; }
    loading.value = true;
    try {
        const res = await axios.get(`/seller/orders/${o.id}`);
        detail.value = res.data;
        newStatus.value = res.data.status;
    } catch {
        toast.error('Failed to load order details.');
    } finally {
        loading.value = false;
    }
}, { immediate: true });

const updateStatus = () => {
    if (!detail.value || newStatus.value === detail.value.status) return;
    updating.value = true;

    router.patch(route('frontend.seller.orders.update', detail.value.id), {
        status: newStatus.value
    }, {
        onSuccess: () => {
            detail.value.status = newStatus.value;
            toast.success('Order status updated!');
        },
        onError: () => {
            toast.error('Failed to update status.');
        },
        onFinish: () => {
            updating.value = false;
        }
    });
};

const fmt = (v: number) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 2 }).format(v);

const fmtDate = (d: string) =>
    new Date(d).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <Teleport to="body">
        <div v-if="order" class="modal-backdrop" @click.self="emit('close')">
            <div class="modal card">
                <!-- Header -->
                <div class="modal-head">
                    <div>
                        <div class="modal-title">Order Detail</div>
                        <div class="modal-sub" v-if="detail">{{ detail.number }}</div>
                    </div>
                    <button class="close-btn" @click="emit('close')">✕</button>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="modal-loading">
                    <div class="spinner"></div>
                    <span>Loading order…</span>
                </div>

                <!-- Content -->
                <div v-else-if="detail" class="modal-body">
                    <!-- Buyer Info -->
                    <div class="section-title">Buyer Information</div>
                    <div class="buyer-info-card">
                        <div class="buyer-avatar-lg">
                            <img v-if="detail.customer?.avatar" :src="detail.customer.avatar" alt="" />
                            <span v-else>{{ detail.customer?.name?.charAt(0) ?? 'B' }}</span>
                        </div>
                        <div class="buyer-details">
                            <div class="buyer-name-lg">{{ detail.customer?.name }}</div>
                            <div class="buyer-email">✉ {{ detail.customer?.email }}</div>
                            <div class="buyer-addr" v-if="detail.street_address">
                                📍 {{ detail.street_address }}, {{ detail.city }}, {{ detail.state }} {{ detail.zipcode }}, {{ detail.country }}
                            </div>
                        </div>
                    </div>

                    <!-- Order Meta -->
                    <div class="meta-row">
                        <div class="meta-item"><span class="meta-label">Date</span><span class="meta-value">{{ fmtDate(detail.created_at) }}</span></div>
                        <div class="meta-item"><span class="meta-label">Payment</span><span class="meta-value">{{ detail.payment_method ?? '—' }}</span></div>
                        <div class="meta-item"><span class="meta-label">Shipping</span><span class="meta-value">{{ detail.shipping_method ?? '—' }}</span></div>
                        <div class="meta-item">
                            <span class="meta-label">Status</span>
                            <span class="status-badge" :class="statusStyle[detail.status]">{{ detail.status }}</span>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="section-title"> Items</div>
                    <div class="items-list">
                        <div v-for="item in detail.items" :key="item.id" class="item-row">
                            <div class="item-img">
                                <img v-if="item.product?.cover_image" :src="item.product.cover_image" alt="" />
                                <div v-else class="item-placeholder"></div>
                            </div>
                            <div class="item-info flex-1 min-w-0">
                                <div class="item-name">{{ item.product?.name }}</div>
                                <div class="item-meta">{{ item.qty }} × {{ fmt(item.unit_price) }}</div>
                            </div>
                            <div class="item-total">{{ fmt(item.sub_total) }}</div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="totals-block">
                        <div class="total-row"><span>Subtotal</span><span>{{ fmt(detail.subtotal_amount) }}</span></div>
                        <div class="total-row" v-if="detail.shipping_amount > 0"><span>Shipping</span><span>{{ fmt(detail.shipping_amount) }}</span></div>
                        <div class="total-row" v-if="detail.tax_amount > 0"><span>Tax</span><span>{{ fmt(detail.tax_amount) }}</span></div>
                        <div class="total-row" v-if="detail.discount_amount > 0"><span>Discount</span><span class="discount">-{{ fmt(detail.discount_amount) }}</span></div>
                        <div class="total-row" v-if="detail.process_fee_amount > 0"><span>Processing Fee</span><span>{{ fmt(detail.process_fee_amount) }}</span></div>
                        <div class="total-row grand"><span>Total</span><span>{{ fmt(detail.total) }}</span></div>
                    </div>

                    <!-- Status Update -->
                    <div class="section-title">Update Status</div>
                    <div class="status-update-row">
                        <select class="input" v-model="newStatus">
                            <option v-for="s in STATUS_OPTIONS" :key="s" :value="s">{{ s.charAt(0).toUpperCase() + s.slice(1) }}</option>
                        </select>
                        <button class="btn" @click="updateStatus" :disabled="updating || newStatus === detail.status">
                            {{ updating ? 'Saving…' : 'Save Status' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.modal-backdrop {
    position: fixed; inset: 0; background: rgba(2,6,23,.55);
    display: flex; align-items: center; justify-content: center;
    z-index: 1000; padding: 16px;
}
.modal {
    width: min(680px, 100%); max-height: 90vh;
    overflow-y: auto; background: #fff; border-radius: 22px;
    box-shadow: 0 24px 60px rgba(2,6,23,.18);
    animation: slideIn .2s ease;
}
@keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: none; opacity: 1; } }
.modal-head { display: flex; align-items: flex-start; justify-content: space-between; padding: 20px 24px 14px; border-bottom: 1px solid rgba(148,163,184,.2); }
.modal-title { font-size: 1.1rem; font-weight: 900; color: #0f172a; }
.modal-sub { font-size: .75rem; color: #94a3b8; font-family: monospace; }
.close-btn { background: #f1f5f9; border: none; border-radius: 10px; width: 32px; height: 32px; cursor: pointer; font-size: 1rem; color: #64748b; transition: background .15s; display: flex; align-items: center; justify-content: center; }
.close-btn:hover { background: #e2e8f0; }
.modal-loading { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 3rem; color: #94a3b8; }
.spinner { width: 32px; height: 32px; border: 3px solid rgba(14,165,233,.2); border-top-color: #0ea5e9; border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.modal-body { padding: 20px 24px 24px; display: flex; flex-direction: column; gap: 14px; }
.section-title { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #64748b; margin-top: 4px; }
.buyer-info-card { display: flex; align-items: flex-start; gap: 14px; background: #f8fafc; border-radius: 16px; padding: 14px; border: 1px solid rgba(148,163,184,.18); }
.buyer-avatar-lg { width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(135deg,#0ea5e9,#22c55e); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 900; color: #fff; flex-shrink: 0; overflow: hidden; }
.buyer-avatar-lg img { width: 100%; height: 100%; object-fit: cover; }
.buyer-name-lg { font-weight: 900; font-size: 1rem; color: #0f172a; }
.buyer-email { font-size: .8rem; color: #64748b; margin-top: 2px; }
.buyer-addr { font-size: .78rem; color: #64748b; margin-top: 4px; }
.meta-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
@media(min-width:640px) { .meta-row { grid-template-columns: repeat(4,1fr); } }
.meta-item { background: #f8fafc; border-radius: 12px; padding: 10px 12px; display: flex; flex-direction: column; gap: 2px; border: 1px solid rgba(148,163,184,.18); }
.meta-label { font-size: .68rem; font-weight: 800; text-transform: uppercase; color: #94a3b8; letter-spacing: .05em; }
.meta-value { font-size: .85rem; font-weight: 700; color: #0f172a; }
.items-list { display: flex; flex-direction: column; gap: 8px; }
.item-row { display: flex; align-items: center; gap: 12px; padding: 10px; background: #f8fafc; border-radius: 12px; border: 1px solid rgba(148,163,184,.18); }
.item-img { width: 44px; height: 44px; border-radius: 10px; overflow: hidden; flex-shrink: 0; }
.item-img img { width: 100%; height: 100%; object-fit: cover; }
.item-placeholder { width: 44px; height: 44px; background: #e2e8f0; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.item-name { font-weight: 700; font-size: .85rem; color: #0f172a; }
.item-meta { font-size: .75rem; color: #64748b; }
.item-total { font-weight: 900; font-size: .9rem; color: #0ea5e9; white-space: nowrap; }
.totals-block { border-top: 1px solid rgba(148,163,184,.18); padding-top: 10px; display: flex; flex-direction: column; gap: 6px; }
.total-row { display: flex; justify-content: space-between; font-size: .85rem; color: #475569; }
.discount { color: #22c55e; }
.grand { font-size: 1rem; font-weight: 900; color: #0f172a; padding-top: 6px; border-top: 1px solid rgba(148,163,184,.2); }
.status-update-row { display: flex; gap: 10px; }
.status-update-row .input { flex: 1; }
.status-badge { font-size: .7rem; font-weight: 900; padding: 4px 10px; border-radius: 999px; text-transform: capitalize; }
.badge-pending    { background: #fef9c3; color: #854d0e; }
.badge-processing { background: #dbeafe; color: #1e40af; }
.badge-shipped    { background: #ede9fe; color: #6d28d9; }
.badge-delivered  { background: #dcfce7; color: #166534; }
.badge-cancelled  { background: #fee2e2; color: #991b1b; }
.btn {
    background: linear-gradient(135deg, #0ea5e9, #22c55e);
    color: #fff; border: none; border-radius: 16px;
    font-weight: 900; padding: .75rem 1.2rem; cursor: pointer;
    box-shadow: 0 8px 24px rgba(14,165,233,.25); white-space: nowrap;
    transition: opacity .15s;
}
.btn:disabled { opacity: .5; cursor: not-allowed; }
.input {
    width: 100%; border: 1px solid rgba(148,163,184,.45);
    border-radius: 16px; padding: .75rem .9rem; outline: none; background: #fff; font-size: .9rem;
}
.input:focus { border-color: rgba(14,165,233,.7); box-shadow: 0 0 0 4px rgba(14,165,233,.12); }
</style>
