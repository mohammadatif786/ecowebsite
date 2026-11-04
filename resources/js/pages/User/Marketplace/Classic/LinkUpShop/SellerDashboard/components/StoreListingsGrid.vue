<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { Trash2Icon } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import DeleteModal from '../../components/DeleteModal.vue';
import ProductDeleteWarningModal from '../../components/ProductDeleteWarningModal.vue';

const props = defineProps<{
    products: any[];
    categories: any[];
}>();


const page = usePage();

watch(
    () => page.props.flash,
    (flash:any) => {
        if (flash?.error) {
            toast.error(flash.error);
        }

        if (flash?.success) {
            toast.success(flash.success);
            emit('deleted');
        }
    },
    { immediate: true }
);
const emit = defineEmits<{ (e: 'edit', p: any): void; (e: 'deleted'): void }>();

const deleting = ref<number | null>(null);
const confirmDeleteProduct = ref<any>(null);
const deleteModal = ref<any>(null);

const toggleStatus = (product: any) => {
    router.patch(`/seller/store/products/${product.id}`, {
        price: product.price,
        qty: product.qty,
        status: !product.status,
    }, {
        preserveScroll: true,
        onSuccess: () => { product.status = !product.status; toast.success('Listing status updated.'); },
        onError: () => toast.error('Failed to update status.'),
    });
};

const deleteProduct = (product: any) => {
    if (product.order_items_count > 0) {
        confirmDeleteProduct.value = product;
        return;
    }

    deleteModal.value?.open(product.name, product.id);
};

const executeDelete = (productId: number) => {
    deleting.value = productId;
    router.delete(`/seller/store/products/${productId}`, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = null;
            confirmDeleteProduct.value = null;
        },
    });
};

const fmt = (v: number) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 2 }).format(v);
</script>

<template>
    <div>
        <!-- Empty state -->
        <div v-if="!products.length" class="empty-state">
            <div class="empty-icon"></div>
            <p>No products listed yet. Head to the shop to add your first listing!</p>
        </div>

        <!-- Grid -->
        <div v-else class="listings-grid">
            <div v-for="p in products" :key="p.id" class="listing-card">
                <!-- Cover -->
                <div class="listing-img">
                    <img v-if="p.cover_image" :src="p.cover_image" :alt="p.name" />
                    <div v-else class="listing-placeholder"></div>
                    <!-- Status pill -->
                    <div class="status-pill" :class="p.status ? 'status-active' : 'status-inactive'">
                        {{ p.status ? 'Active' : 'Inactive' }}
                    </div>
                </div>

                <!-- Info -->
                <div class="listing-info">
                    <div class="listing-name">{{ p.name }}</div>
                    <div class="listing-cat">{{ p.category?.name ?? 'Uncategorized' }}</div>

                    <div class="listing-stats">
                        <div class="stat-item">
                            <span class="stat-lbl">Price</span>
                            <span class="stat-val price-val">{{ fmt(p.price) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-lbl">Stock</span>
                            <span class="stat-val" :class="p.qty <= 5 ? 'low-stock' : ''">{{ p.qty }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="listing-actions">
                    <button class="action-btn edit-btn" @click="emit('edit', p)">✏ Edit</button>
                    <button class="action-btn toggle-btn" @click="toggleStatus(p)">
                        {{ p.status ? ' Deactivate' : ' Activate' }}
                    </button>
                    <button class="action-btn del-btn" @click="deleteProduct(p)" :disabled="deleting === p.id">
                        <Trash2Icon v-if="deleting !== p.id" />
                        <span v-else>…</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal (Only for products with orders) -->
        <ProductDeleteWarningModal
            :is-open="!!confirmDeleteProduct"
            :product="confirmDeleteProduct"
            :deleting="deleting !== null"
            @confirm="executeDelete"
            @close="confirmDeleteProduct = null"
        />

        <!-- Standard Delete Modal -->
        <DeleteModal
            ref="deleteModal"
            title="Remove Listing"
            message="Are you sure you want to remove this product from your store?"
            @confirm="executeDelete"
        />
    </div>
</template>

<style scoped>
.empty-state { text-align: center; padding: 3rem; color: #94a3b8; }
.empty-icon { font-size: 2.5rem; margin-bottom: .5rem; }
.listings-grid {
    display: grid; gap: 18px;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
}
.listing-card {
    background: #fff; border-radius: 18px;
    border: 1px solid rgba(148,163,184,.25);
    box-shadow: 0 4px 20px rgba(2,6,23,.07);
    overflow: hidden; transition: transform .2s, box-shadow .2s;
}
.listing-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(2,6,23,.12); }
.listing-img { position: relative; height: 160px; overflow: hidden; }
.listing-img img { width: 100%; height: 100%; object-fit: cover; }
.listing-placeholder { width: 100%; height: 100%; background: linear-gradient(135deg,#f0fdf4,#eff6ff); display: flex; align-items: center; justify-content: center; font-size: 3rem; }
.status-pill {
    position: absolute; top: 10px; right: 10px;
    font-size: .65rem; font-weight: 900; padding: 3px 8px; border-radius: 999px;
}
.status-active   { background: #dcfce7; color: #166534; }
.status-inactive { background: #fee2e2; color: #991b1b; }
.listing-info { padding: 14px 14px 10px; }
.listing-name { font-size: .92rem; font-weight: 900; color: #0f172a; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.listing-cat { font-size: .72rem; color: #94a3b8; margin-bottom: 10px; }
.listing-stats { display: flex; gap: 14px; }
.stat-item { display: flex; flex-direction: column; }
.stat-lbl { font-size: .68rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; letter-spacing: .04em; }
.stat-val { font-size: .9rem; font-weight: 900; color: #0f172a; }
.price-val { color: #0ea5e9; }
.low-stock { color: #ef4444; }
.listing-actions { padding: 0 14px 14px; display: flex; gap: 6px; flex-wrap: wrap; }
.action-btn {
    font-size: .72rem; font-weight: 800; padding: 5px 10px;
    border-radius: 10px; border: none; cursor: pointer; transition: all .15s;
}
.edit-btn   { background: rgba(14,165,233,.1); color: #0284c7; }
.edit-btn:hover { background: rgba(14,165,233,.2); }
.toggle-btn { background: #f1f5f9; color: #475569; }
.toggle-btn:hover { background: #e2e8f0; }
.del-btn    { background: rgba(239,68,68,.08); color: #dc2626; margin-left: auto; }
.del-btn:hover { background: rgba(239,68,68,.18); }
.del-btn:disabled { opacity: .5; cursor: not-allowed; }

.del-btn:disabled { opacity: .5; cursor: not-allowed; }
</style>
