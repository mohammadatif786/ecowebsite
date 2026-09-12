<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const props = defineProps<{ product: any | null; categories: any[] }>();
const emit = defineEmits<{ (e: 'close'): void }>();

const form = ref({ price: 0, qty: 0, status: true });
const saving = ref(false);

watch(() => props.product, (p) => {
    if (p) { form.value = { price: p.price, qty: p.qty, status: !!p.status }; }
}, { immediate: true });

const save = () => {
    if (!props.product) return;
    saving.value = true;
    router.patch(`/seller/store/products/${props.product.id}`, form.value, {
        preserveScroll: true,
        onSuccess: () => { toast.success('Listing updated!'); emit('close'); },
        onError: (errs) => {
            const first = Object.values(errs)[0] as string;
            toast.error(first ?? 'Failed to save.');
        },
        onFinish: () => { saving.value = false; },
    });
};
</script>

<template>
    <Teleport to="body">
        <div v-if="product" class="modal-backdrop" @click.self="emit('close')">
            <div class="modal card">
                <!-- Head -->
                <div class="modal-head">
                    <div>
                        <div class="modal-title">Edit Listing</div>
                        <div class="modal-sub">{{ product.name }}</div>
                    </div>
                    <button class="close-btn" @click="emit('close')">✕</button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <!-- Preview -->
                    <div class="preview-row">
                        <div class="preview-img">
                            <img v-if="product.cover_image" :src="product.cover_image" alt="" />
                            <div v-else class="preview-placeholder">📦</div>
                        </div>
                        <div>
                            <div class="preview-name">{{ product.name }}</div>
                            <div class="preview-cat">{{ product.category?.name ?? 'Uncategorized' }}</div>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="field-group">
                        <label class="field-label">Price (USD)</label>
                        <div class="input-prefix-wrap">
                            <span class="prefix">$</span>
                            <input class="input pl-prefix" type="number" min="0" step="0.01" v-model="form.price" placeholder="0.00" />
                        </div>
                    </div>

                    <!-- Stock -->
                    <div class="field-group">
                        <label class="field-label">Stock Quantity</label>
                        <input class="input" type="number" min="0" v-model="form.qty" placeholder="0" />
                        <p v-if="form.qty <= 5 && form.qty >= 0" class="field-hint warn">⚠ Low stock warning</p>
                    </div>

                    <!-- Status -->
                    <div class="field-group">
                        <label class="field-label">Listing Status</label>
                        <div class="toggle-row">
                            <button class="toggle-opt" :class="form.status ? 'toggle-on' : ''" @click="form.status = true">🟢 Active</button>
                            <button class="toggle-opt" :class="!form.status ? 'toggle-off' : ''" @click="form.status = false">🔴 Inactive</button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="modal-footer">
                        <button class="btn2" @click="emit('close')">Cancel</button>
                        <button class="btn" @click="save" :disabled="saving">
                            {{ saving ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.modal-backdrop { position: fixed; inset: 0; background: rgba(2,6,23,.55); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 16px; }
.modal { width: min(480px, 100%); background: #fff; border-radius: 22px; box-shadow: 0 24px 60px rgba(2,6,23,.18); animation: slideIn .2s ease; }
@keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: none; opacity: 1; } }
.modal-head { display: flex; align-items: flex-start; justify-content: space-between; padding: 20px 24px 14px; border-bottom: 1px solid rgba(148,163,184,.2); }
.modal-title { font-size: 1.1rem; font-weight: 900; color: #0f172a; }
.modal-sub { font-size: .78rem; color: #94a3b8; margin-top: 2px; }
.close-btn { background: #f1f5f9; border: none; border-radius: 10px; width: 32px; height: 32px; cursor: pointer; font-size: 1rem; color: #64748b; display: flex; align-items: center; justify-content: center; }
.close-btn:hover { background: #e2e8f0; }
.modal-body { padding: 20px 24px 24px; display: flex; flex-direction: column; gap: 16px; }
.preview-row { display: flex; align-items: center; gap: 14px; background: #f8fafc; border-radius: 14px; padding: 12px; border: 1px solid rgba(148,163,184,.18); }
.preview-img { width: 52px; height: 52px; border-radius: 12px; overflow: hidden; flex-shrink: 0; }
.preview-img img { width: 100%; height: 100%; object-fit: cover; }
.preview-placeholder { width: 52px; height: 52px; background: #e2e8f0; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.preview-name { font-weight: 900; color: #0f172a; font-size: .9rem; }
.preview-cat { font-size: .75rem; color: #94a3b8; }
.field-group { display: flex; flex-direction: column; gap: 6px; }
.field-label { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: #64748b; }
.field-hint { font-size: .72rem; }
.field-hint.warn { color: #d97706; }
.input-prefix-wrap { position: relative; display: flex; align-items: center; }
.prefix { position: absolute; left: 14px; color: #64748b; font-weight: 700; font-size: .9rem; }
.pl-prefix { padding-left: 2rem !important; }
.input { width: 100%; border: 1px solid rgba(148,163,184,.45); border-radius: 16px; padding: .75rem .9rem; outline: none; background: #fff; font-size: .9rem; }
.input:focus { border-color: rgba(14,165,233,.7); box-shadow: 0 0 0 4px rgba(14,165,233,.12); }
.toggle-row { display: flex; gap: 10px; }
.toggle-opt { flex: 1; padding: .6rem; border-radius: 14px; border: 1.5px solid rgba(148,163,184,.3); background: #f8fafc; cursor: pointer; font-weight: 800; font-size: .82rem; color: #64748b; transition: all .15s; }
.toggle-on  { border-color: #22c55e; background: #dcfce7; color: #166534; }
.toggle-off { border-color: #ef4444; background: #fee2e2; color: #991b1b; }
.modal-footer { display: flex; gap: 10px; justify-content: flex-end; padding-top: 4px; }
.btn  { background: linear-gradient(135deg,#0ea5e9,#22c55e); color: #fff; border: none; border-radius: 16px; font-weight: 900; padding: .75rem 1.4rem; cursor: pointer; box-shadow: 0 8px 24px rgba(14,165,233,.25); transition: opacity .15s; }
.btn:disabled { opacity: .5; cursor: not-allowed; }
.btn2 { border: 1px solid rgba(148,163,184,.35); border-radius: 16px; font-weight: 900; padding: .75rem 1.2rem; background: #fff; cursor: pointer; color: #475569; }
</style>
