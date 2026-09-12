<script setup lang="ts">
const props = defineProps<{
    isOpen: boolean;
    product: any;
    deleting: boolean;
}>();

const emit = defineEmits<{
    (e: 'confirm', id: number): void;
    (e: 'close'): void;
}>();

const close = () => emit('close');
const confirm = () => {
    if (props.product) {
        emit('confirm', props.product.id);
    }
};
</script>

<template>
    <Teleport to="body">
        <div v-if="isOpen && product" class="modal-backdrop" @click.self="close">
            <div class="modal card del-modal">
                <div class="modal-head">
                    <div>
                        <div class="modal-title">Confirm Deletion</div>
                        <div class="modal-sub">Warning: This product has active/past orders</div>
                    </div>
                    <button class="close-btn" @click="close">✕</button>
                </div>

                <div class="modal-body">
                    <div class="alert-box">
                        <span class="alert-icon">⚠️</span>
                        <div class="alert-msg">
                            <strong>"{{ product.name }}"</strong> has <strong>{{ product.order_items_count }}</strong> associated order(s).
                            Removing this product will delete it from your store, but it may affect your historical order data.
                        </div>
                    </div>
                    <p class="confirm-txt">Are you absolutely sure you want to proceed?</p>

                    <div class="modal-footer">
                        <button class="btn-cancel" @click="close">Cancel</button>
                        <button class="btn-delete" @click="confirm" :disabled="deleting">
                            {{ deleting ? 'Deleting…' : 'Yes, Delete Anyway' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.modal-backdrop { position: fixed; inset: 0; background: rgba(2,6,23,.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 16px; backdrop-filter: blur(4px); }
.modal { width: min(440px, 100%); background: #fff; border-radius: 20px; box-shadow: 0 20px 50px rgba(2,6,23,.15); animation: zoomIn .2s ease-out; overflow: hidden; }
@keyframes zoomIn { from { transform: scale(.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.modal-head { display: flex; align-items: flex-start; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid #f1f5f9; }
.modal-title { font-size: 1rem; font-weight: 900; color: #0f172a; }
.modal-sub { font-size: .75rem; color: #ef4444; margin-top: 2px; font-weight: 600; }
.close-btn { background: #f8fafc; border: none; border-radius: 8px; width: 28px; height: 28px; cursor: pointer; color: #64748b; display: flex; align-items: center; justify-content: center; }
.modal-body { padding: 22px; }
.alert-box { display: flex; gap: 12px; background: #fff7ed; border: 1px solid #ffedd5; border-radius: 12px; padding: 14px; margin-bottom: 18px; }
.alert-icon { font-size: 1.2rem; }
.alert-msg { font-size: .85rem; color: #9a3412; line-height: 1.5; }
.confirm-txt { font-size: .9rem; color: #475569; margin-bottom: 22px; }
.modal-footer { display: flex; gap: 10px; justify-content: flex-end; }
.btn-cancel { border: 1px solid #e2e8f0; border-radius: 12px; padding: 8px 16px; background: #fff; cursor: pointer; font-weight: 700; color: #64748b; font-size: .85rem; }
.btn-delete { background: #ef4444; color: #fff; border: none; border-radius: 12px; padding: 8px 18px; cursor: pointer; font-weight: 700; font-size: .85rem; box-shadow: 0 4px 12px rgba(239,68,68,.2); }
.btn-delete:disabled { opacity: .6; cursor: not-allowed; }
</style>
