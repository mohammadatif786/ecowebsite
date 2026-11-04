<script setup lang="ts">
import { formatPrice } from '@/composables/formatPrice';
import { Eye, Plus } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    product: any;
}>();

const emit = defineEmits<{
    (e: 'view', productId: number): void;
    (e: 'add', productId: number): void;
}>();

const badge = computed(() => {
    const type = props.product?.listing_type || "Individual";
    if (type === "Administrative") return { text: "Admin", cls: "tag badge-admin" };
    if (type === "Store") return { text: "Store", cls: "tag badge-store" };
    if (type === "Carnival Group") return { text: "Carnival Group", cls: "tag badge-group" };
    return { text: "Individual", cls: "tag badge-individual" };
});

const sellerName = computed(() => {
    if (props.product?.listing_type === "Store") {
        return props.product?.merchant?.name || "Store";
    }
    if (props.product?.listing_type === "Carnival Group") {
        return props.product?.merchant?.name || "Carnival Group";
    }
    if (props.product?.listing_type === "Administrative") {
        return "LinkUp Admin";
    }
    return "Individual Seller";
});

const sellerLocation = computed(() => {
    if (props.product?.listing_type === "Store") {
        const merchant = props.product?.merchant;
        return merchant ? `${merchant.city}, ${merchant.state}, ${merchant.country}` : "";
    }
    if (props.product?.listing_type === "Carnival Group") {
        const merchant = props.product?.merchant;
        return merchant ? `${merchant.city}, ${merchant.state}, ${merchant.country}` : "";
    }
    if (props.product?.listing_type === "Administrative") {
        return "LinkUp Network";
    }
    return "";
});

const pickupHint = computed(() => {
    const merchantType = props.product?.listing_type;
    return (merchantType === "Store" || merchantType === "Carnival Group") ? "Pickup available" : "Delivery only";
});

const truncatedDescription = computed(() => {
    const desc = props.product?.description || "";
    return desc.length > 120 ? desc.slice(0, 120) + "…" : desc;
});

const handleViewClick = () => {
    emit('view', props.product?.id);
};
</script>

<template>
    <div class="card overflow-hidden group">
        <div class="relative h-[300px] bg-gray-50/50 flex items-center justify-center overflow-hidden">
            <img
                :src="product.cover_image || ''"
                :alt="product.name || 'Product image'"
                class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105"
            />

            <div class="absolute top-3 left-3 flex items-center gap-2">
                <span :class="badge.cls">{{ badge.text }}</span>
                <span
                    class="tag"
                    style="background:rgba(255,255,255,.92); color:#0f172a; border:1px solid rgba(148,163,184,.4);"
                >
                    {{ product.category?.name || "Other" }}
                </span>
            </div>

            <div class="absolute bottom-3 left-3">
                <span
                    class="tag mx-3"
                    style="background:rgba(15,23,42,.85); color:white;"
                >
                    {{ pickupHint }}
                </span>
                
                <span
                    class="tag"
                    :style="{
                        background: product.qty <= 0 ? '#ef4444' : 'rgba(255,255,255,.92)',
                        color: product.qty <= 0 ? 'white' : '#0f172a',
                        border: product.qty <= 0 ? '1px solid #dc2626' : '1px solid rgba(148,163,184,.4)',
                    }"
                >
                    {{ product.qty > 0 ? `${product.qty} In Stock` : 'Sold Out' }}
                </span>
            </div>
        </div>

        <div class="p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="font-black text-lg leading-tight">
                        {{ product.name || "Untitled" }}
                    </div>
                    <div class="text-sm mt-1" style="color:#64748b;">
                        by <span class="font-black">{{ sellerName }}</span>
                        <span v-if="sellerLocation"> • {{ sellerLocation }}</span>
                    </div>
                </div>

                <div class="text-right">
                    <div class="font-black text-lg">
                        ${{ formatPrice(product.price || 0) }}
                    </div>
                    <div class="tiny" style="color:#64748b;">
                        USD
                    </div>
                </div>
            </div>

            <div class="mt-3 text-sm" style="color:#64748b; line-height:1.35;">
                {{ truncatedDescription }}
            </div>

            <div class="mt-4 flex gap-2">
                <button
                    class="btn2 flex-1"
                    @click="handleViewClick"
                >
                    <span class="inline-flex items-center gap-2">
                        <Eye class="w-4 h-4" />
                        View
                    </span>
                </button>

                <button
                    class="btn flex-1"
                    @click="$emit('add', product.id)"
                    :disabled="product.qty <= 0"
                >
                    <span class="inline-flex items-center gap-2">
                        <Plus class="w-4 h-4" />
                        {{ product.qty <= 0 ? 'Sold Out' : 'Add' }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>
<style scoped>
body {
    background: #f5f7fb;
    color: #0f172a;
}

.card {
    background: #ffffff;
    border-radius: 22px;
    box-shadow: 0 18px 40px rgba(2, 6, 23, .10);
    border: 1px solid rgba(148, 163, 184, .35);
}

.chip {
    border: 1px solid rgba(148, 163, 184, .35);
    border-radius: 999px;
    padding: .35rem .7rem;
    font-weight: 800;
    font-size: .75rem;
}

.btn {
    background: linear-gradient(135deg,
    rgba(14, 165, 233, 1),
    rgba(34, 197, 94, 1));
    color: #ffffff;
    border-radius: 16px;
    font-weight: 900;
    padding: .75rem 1rem;
    box-shadow: 0 16px 35px rgba(14, 165, 233, .22);
}

.btn:disabled {
    opacity: .5;
    filter: grayscale(.2);
    cursor: not-allowed;
}

.btn2 {
    border: 1px solid rgba(148, 163, 184, .35);
    border-radius: 16px;
    font-weight: 900;
    padding: .75rem 1rem;
    background: #ffffff;
}

.input {
    width: 100%;
    border: 1px solid rgba(148, 163, 184, .45);
    border-radius: 16px;
    padding: .75rem .9rem;
    outline: none;
    background: #ffffff;
}

.input:focus {
    border-color: rgba(14, 165, 233, .7);
    box-shadow: 0 0 0 4px rgba(14, 165, 233, .12);
}

.soft {
    background: linear-gradient(180deg,
            rgba(14, 165, 233, .10),
            rgba(34, 197, 94, .06));
    border: 1px solid rgba(14, 165, 233, .18);
}

.glow {
    box-shadow:
        0 0 0 4px rgba(14, 165, 233, .08),
        0 20px 40px rgba(2, 6, 23, .10);
}

.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(2, 6, 23, .55);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 18px;
}

.modal {
    width: min(920px, 100%);
    max-height: 90vh;
    overflow: auto;
}

.tiny {
    font-size: .75rem;
}

.tag {
    font-size: .72rem;
    font-weight: 900;
    padding: .30rem .55rem;
    border-radius: 999px;
}

.badge-admin {
    background: #0f172a;
    color: #ffffff;
}

.badge-store {
    background: #0284c7;
    color: #ffffff;
}

.badge-group {
    background: #a855f7;
    color: #ffffff;
}

.badge-individual {
    background: #e2e8f0;
    color: #0f172a;
}

/* Network-safe utility fallbacks */

.min-h-screen {
    min-height: 100vh;
}

.sticky {
    position: sticky;
}

.top-0 {
    top: 0;
}

.z-50 {
    z-index: 50;
}

.mx-auto {
    margin-left: auto;
    margin-right: auto;
}

.px-4 {
    padding-left: 1rem;
    padding-right: 1rem;
}

.py-3 {
    padding-top: .75rem;
    padding-bottom: .75rem;
}

.py-6 {
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
}

.border-b {
    border-bottom: 1px solid rgba(148, 163, 184, .35);
}

.flex {
    display: flex;
}

.grid {
    display: grid;
}

.gap-2 {
    gap: .5rem;
}

.gap-3 {
    gap: .75rem;
}

.gap-4 {
    gap: 1rem;
}

.gap-6 {
    gap: 1.5rem;
}

.items-center {
    align-items: center;
}

.items-start {
    align-items: flex-start;
}

.justify-between {
    justify-content: space-between;
}

.ml-auto {
    margin-left: auto;
}

.text-sm {
    font-size: .875rem;
}

.text-lg {
    font-size: 1.125rem;
}

.text-xl {
    font-size: 1.25rem;
}

.font-black {
    font-weight: 900;
}

.overflow-hidden {
    overflow: hidden;
}

.w-full {
    width: 100%;
}

.text-center {
    text-align: center;
}

.col-span-full {
    grid-column: 1 / -1;
}

@media (min-width: 640px) {
    .sm\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 768px) {
    .md\:p-5 {
        padding: 1.25rem;
    }

    .md\:p-6 {
        padding: 1.5rem;
    }

    .md\:flex-row {
        flex-direction: row;
    }

    .md\:items-end {
        align-items: flex-end;
    }

    .md\:w-56 {
        width: 14rem;
    }

    .md\:w-auto {
        width: auto;
    }

    .md\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .md\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .lg\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
</style>
