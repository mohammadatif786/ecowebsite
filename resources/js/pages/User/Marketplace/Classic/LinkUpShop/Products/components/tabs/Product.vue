<script setup lang="ts">
import { ImageIcon, Upload, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DeleteModal from '../../../components/DeleteModal.vue';
import ProductDeleteWarningModal from '../../../components/ProductDeleteWarningModal.vue';

const props = defineProps<{
    activeTab: string;
    merchants?: any[];
    categories?: any[];
    userProducts?: any[];
}>();

const selectedProductId = ref<number | null>(null);
const isAutoFilling = ref(false);
const listingType = ref('Individual');
const sellerId = ref('individual');
const imagePreview = ref<string | null>(null);
const imagePreviews = ref<string[]>([]);
const existingImagePreview = ref<string | null>(null);

const uploadStatus = ref('No file selected yet.');
const fileInput = ref<HTMLInputElement | null>(null);
const allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];

const form = useForm({
    _method: 'POST',
    name: '',
    description: '',
    price: '',
    stock: '10',
    category_id: '',
    listing_type: 'Individual',
    seller_owner: null,
    image_url: '',
    cover_image: null,
    images: [] as File[],
    commMode: 'pct',
    commission: 10,
    commFlat: 0,
    collect_tax: false,
    trademark_status: 'I own this trademark / copyright',
    trademark_number: '',
    trademark_file: null,
    confirm_rights: false,
});

const tmFileInput = ref<HTMLInputElement | null>(null);

const handleTMFile = (event: any) => {
    const file = event.target.files[0];
    if (file) {
        form.trademark_file = file;
    }
};

const promoterEarns = computed(() => {
    const price = parseFloat(form.price) || 0;
    if (form.commMode === 'none') return '0.00';
    if (form.commMode === 'flat') return Number(form.commFlat || 0).toFixed(2);
    return (price * (Number(form.commission || 0) / 100)).toFixed(2);
});

const youKeep = computed(() => {
    const price = parseFloat(form.price) || 0;
    return Math.max(price - parseFloat(promoterEarns.value), 0).toFixed(2);
});

const commissionSummary = computed(() => {
    if (form.commMode === 'none') {
        return 'Creators can still tag this product — they just earn nothing.';
    }

    if (form.commMode === 'flat') {
        return `Promoters earn $${Number(form.commFlat || 0).toFixed(2)} per product sold.`;
    }

    return `Promoters earn ${Number(form.commission || 0).toFixed(2)}%.`;
});

const filteredMerchants = computed(() => {
    if (listingType.value === 'Store') {
        return props.merchants?.filter(m => m.merchant_type === 'store') || [];
    }
    if (listingType.value === 'Carnival Group') {
        return props.merchants?.filter(m => m.merchant_type === 'group') || [];
    }
    return [];
});

const merchantTypeLabel = (type: string) => {
    return type === 'store' ? 'Store' : 'Group';
};

const handleImageUpload = (event: Event) => {
    const files = Array.from((event.target as HTMLInputElement).files || []);
    if (!files.length) return;

    if (files.some((file) => !allowedImageTypes.includes(file.type))) {
        uploadStatus.value = 'Only JPEG, PNG, or GIF images are allowed.';
        form.setError('images', 'Upload JPEG, PNG, or GIF images only.');
        if (fileInput.value) fileInput.value.value = '';
        return;
    }

    form.clearErrors('cover_image');
    form.clearErrors('images');
    form.images = files;
    form.cover_image = files[0];
    form.image_url = '';
    imagePreviews.value.forEach((url) => URL.revokeObjectURL(url));
    imagePreviews.value = files.map((file) => URL.createObjectURL(file));
    imagePreview.value = imagePreviews.value[0] || null;
    uploadStatus.value = `${files.length} image${files.length === 1 ? '' : 's'} selected. The first image is the cover.`;
};

const clearUpload = () => {
    form.cover_image = null;
    form.images = [];
    imagePreviews.value.forEach((url) => URL.revokeObjectURL(url));
    imagePreviews.value = [];
    imagePreview.value = existingImagePreview.value;
    uploadStatus.value = 'No file selected yet.';
    form.clearErrors('cover_image');
    if (fileInput.value) fileInput.value.value = '';
};

const selectProduct = (product: any) => {
    isAutoFilling.value = true;
    selectedProductId.value = product.id;
    form.name = product.name;
    form.description = product.description;
    form.price = product.price;
    form.stock = product.qty;
    form.category_id = product.product_category_id;
    form.listing_type = product.listing_type;
    listingType.value = product.listing_type;
    sellerId.value = product.seller_owner || (product.listing_type === 'Individual' ? 'individual' : '');
    form.image_url = product.image_url;
    existingImagePreview.value = product.cover_image || product.image_url;
    imagePreview.value = existingImagePreview.value;
    uploadStatus.value = product.cover_image ? 'Existing image' : (product.image_url ? 'Using URL' : 'No image');
    form.cover_image = null;
    form.images = [];
    imagePreviews.value = Array.isArray(product.images) ? product.images.filter(Boolean) : [];
    form.commMode = product.commMode || (Number(product.commission || 0) > 0 ? 'pct' : 'none');
    form.commission = Number(product.commission ?? 10);
    form.commFlat = Number(product.commFlat ?? 0);
    form.collect_tax = Boolean(product.collect_tax);

    // Reset the flag after a short delay to allow the watch to be skipped
    setTimeout(() => {
        isAutoFilling.value = false;
    }, 100);
};

const clearSelection = () => {
    selectedProductId.value = null;
    form.reset();
    listingType.value = 'Individual';
    sellerId.value = 'individual';
    imagePreview.value = null;
    imagePreviews.value.forEach((url) => URL.revokeObjectURL(url));
    imagePreviews.value = [];
    existingImagePreview.value = null;
    uploadStatus.value = 'No file selected yet.';
    if (fileInput.value) fileInput.value.value = '';
};

// delete store logic
const deleteModalRef = ref();
const confirmDeleteProduct = ref<any>(null);

const deleteProduct = () => {
    if (!selectedProductId.value) return;
    const product = props.userProducts?.find(p => p.id === selectedProductId.value);

    if (product?.order_items_count > 0) {
        confirmDeleteProduct.value = product;
        return;
    }

    deleteModalRef.value.open(product?.name || 'this product', selectedProductId.value);
};

const confirmDelete = (productId: number) => {
    form.delete(route('frontend.products.destroy', productId), {
        onSuccess: () => {
            clearSelection();
            emit('back');
            emit('close');
            deleteModalRef.value.close();
            confirmDeleteProduct.value = null;
            if (window.toast) window.toast('Prodcut Deleted');

        }
    });
};
const validateProduct = () => {
    const errors: Record<string, string> = {};

    if (!form.name.trim()) errors.name = 'Product name is required.';
    if (form.price === '' || Number(form.price) < 0) errors.price = 'Enter a valid price.';
    if (form.stock === '' || !Number.isInteger(Number(form.stock)) || Number(form.stock) < 0) errors.stock = 'Enter a valid stock quantity.';
    if (!form.category_id) errors.category_id = 'Choose a category.';
    if (['Store', 'Carnival Group'].includes(listingType.value) && !sellerId.value) {
        errors.seller_owner = 'Choose the store or carnival group that owns this product.';
    }

    form.clearErrors();
    if (Object.keys(errors).length) {
        form.setError(errors);
        return false;
    }

    return true;
};
const storeProduct = () => {
    form.listing_type = listingType.value;
    form.seller_type = listingType.value;
    if (form.commMode === 'none') {
        form.commission = 0;
        form.commFlat = 0;
    } else if (form.commMode === 'pct') {
        form.commFlat = 0;
    } else if (form.commMode === 'flat') {
        form.commission = 0;
    }
    form.seller_owner =
        (listingType.value === 'Store' || listingType.value === 'Carnival Group')
            ? (sellerId.value ? Number(sellerId.value) : null)
            : null;

    if (!validateProduct()) return;

    if (selectedProductId.value) {
        form._method = 'PUT';
        form.post(route('frontend.products.update', selectedProductId.value), {
            forceFormData: true,
            onSuccess: () => {
                emit('back');
                emit('close');
                if (window.toast) window.toast(form.name + ' Prodcut Updated ');
            }
        });
    } else {
        form._method = 'POST';
        form.post(route('frontend.products.store'), {
            forceFormData: true,
            onSuccess: () => {
                emit('back');
                emit('close');
                if (window.toast) window.toast(form.name + ' Prodcut Created ');
            }
        });
    }
};

watch(listingType, (type) => {
    if (isAutoFilling.value) return;

    if (type === 'Individual') {
        sellerId.value = 'individual'
    } else if (type === 'Administrative') {
        sellerId.value = 'administrative'
    } else {
        sellerId.value = ''
    }
})

const emit = defineEmits(['back', 'close', 'refresh']);

</script>
<template>
    <div class="grid gap-3" v-if="props.activeTab === 'product'">
        <!-- My Products List -->
        <div v-if="props.userProducts && props.userProducts.length > 0 && !selectedProductId"
            class="mb-4 overflow-hidden">
            <div class="font-black text-xs uppercase tracking-wider text-slate-500 mb-2">My Products (Select to Edit)
            </div>
            <div class="flex gap-3 overflow-x-auto pb-3 custom-scrollbar">
                <div v-for="product in props.userProducts" :key="product.id"
                    class="flex-shrink-0 cursor-pointer transition-all duration-200 opacity-70 hover:opacity-100"
                    @click="selectProduct(product)">
                    <div class="w-16 h-16 rounded-xl overflow-hidden shadow-sm border border-slate-200">
                        <img :src="product.cover_image || product.image_url || 'https://images.unsplash.com/photo-1520975916090-3105956dac38?auto=format&fit=crop&w=200&q=60'"
                            class="w-full h-full object-cover" :alt="product.name" />
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider">Product / Costume
                    Name</label>
                <input class="input mt-1" v-model="form.name" placeholder="e.g. Frontline Costume — Fire" />
                <p v-if="form.errors.name" class="text-[10px] text-rose-500 mt-1 font-bold">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider">Price (USD)</label>
                <input class="input mt-1" v-model="form.price" type="number" step="0.01" placeholder="450.00" />
                <p v-if="form.errors.price" class="text-[10px] text-rose-500 mt-1 font-bold">{{ form.errors.price }}</p>
            </div>
        </div>

        <div>
            <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider">Stock / Quantity</label>
            <input class="input mt-1" v-model="form.stock" type="number" min="0" placeholder="10" />
            <p v-if="form.errors.stock" class="text-[10px] text-rose-500 mt-1 font-bold">{{ form.errors.stock }}</p>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider">Category</label>
                <select class="input mt-1 bg-white" v-model="form.category_id">
                    <option value="" disabled>Select category...</option>
                    <option v-for="category in props.categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>
                <p v-if="form.errors.category_id" class="text-[10px] text-rose-500 mt-1 font-bold">{{
                    form.errors.category_id }}</p>
            </div>
            <div>
                <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider">Listing Type</label>
                <select class="input mt-1 bg-white" v-model="listingType">
                    <option value="Individual">Individual</option>
                    <option value="Store">Store</option>
                    <option value="Carnival Group">Carnival Group</option>
                </select>
            </div>
        </div>

        <div>
            <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider">Seller / Owner</label>
            <select class="input mt-1 bg-white" v-model="sellerId" :disabled="listingType === 'Individual'">
                <option value="individual" v-if="listingType === 'Individual'">Individual Seller</option>
                <option v-for="merchant in filteredMerchants" :key="merchant.id" :value="merchant.id">
                    {{ merchant.name }} ({{ merchantTypeLabel(merchant.merchant_type) }})
                </option>
            </select>
            <p class="text-[10px] text-slate-400 mt-1 font-bold italic">Store/Group products inherit pickup at checkout.
            </p>
            <p v-if="form.errors.seller_owner" class="text-[10px] text-rose-500 mt-1 font-bold">{{
                form.errors.seller_owner }}</p>
        </div>

        <!-- Promoter Commission Section -->
        <div class="rounded-2xl bg-[#f0f7ff] border border-[#e0effe] p-4 space-y-3">
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2">
                    <span class="text-amber-500 text-lg">🤝</span>
                    <span class="text-xs font-black text-blue-900 uppercase tracking-wider">Promoter commission</span>
                </div>
            </div>
            <p class="text-[10px] text-blue-700 font-bold leading-tight mb-3">
                What creators earn when they tag this product on their Live or Vibes. Higher = more creators push your
                products.
            </p>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" @click="form.commMode = 'pct'"
                    :class="form.commMode === 'pct' ? 'bg-white border-2 border-[#3b82f6] text-[#2563eb] shadow-sm' : 'bg-[#f8fafc] border border-[#e2e8f0] text-[#64748b]'"
                    class="rounded-xl py-2.5 text-[12px] font-black transition-all">
                    Percentage
                </button>
                <button type="button" @click="form.commMode = 'flat'"
                    :class="form.commMode === 'flat' ? 'bg-white border-2 border-[#3b82f6] text-[#2563eb] shadow-sm' : 'bg-[#f8fafc] border border-[#e2e8f0] text-[#64748b]'"
                    class="rounded-xl py-2.5 text-[12px] font-black transition-all">
                    Flat rate
                </button>
                <button type="button" @click="form.commMode = 'none'"
                    :class="form.commMode === 'none' ? 'bg-white border-2 border-[#3b82f6] text-[#2563eb] shadow-sm' : 'bg-[#f8fafc] border border-[#e2e8f0] text-[#64748b]'"
                    class="rounded-xl py-2.5 text-[12px] font-black transition-all">
                    Exclude
                </button>
            </div>
            <div v-if="form.commMode !== 'none'" class="flex items-center gap-3">
                <input v-if="form.commMode === 'pct'" v-model="form.commission" type="number" min="0" max="50"
                    class="w-20 rounded-xl border border-[#cbd5e1] bg-white px-3 py-2 text-sm font-black text-center outline-none focus:border-[#3b82f6]" />
                <input v-else-if="form.commMode === 'flat'" v-model="form.commFlat" type="number" min="0"
                    class="w-20 rounded-xl border border-[#cbd5e1] bg-white px-3 py-2 text-sm font-black text-center outline-none focus:border-[#3b82f6]" />
                <span class="text-[12px] font-bold text-[#2563eb]">
                    {{ form.commMode === 'pct' ? '% of each product' : '$ per product sold' }}
                </span>
            </div>
            <p class="text-[11px] font-medium text-[#2563eb]">{{ commissionSummary }}</p>
            <div class="space-y-1.5 border-t border-blue-100 pt-3">
                <div class="flex justify-between text-[11px] font-bold">
                    <span class="text-slate-500">Promoter earns</span>
                    <span class="text-blue-600">${{ promoterEarns }}</span>
                </div>
                <div class="flex justify-between text-[11px] font-bold">
                    <span class="text-slate-500">You keep</span>
                    <span class="text-emerald-600">${{ youKeep }}</span>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 font-bold leading-tight mt-3 italic flex gap-1">
                <span>💰</span>
                <span>Commission is <b>Pending</b> when the sale happens, moves to <b>Released</b> once the order is
                    delivered & confirmed, then the promoter transfers it to their Wallet.</span>
            </p>
        </div>

        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <input v-model="form.collect_tax" type="checkbox" class="mt-0.5 h-4 w-4 accent-emerald-600" />
            <span>
                <span class="block text-sm font-black text-emerald-900">Collect applicable tax</span>
                <span class="mt-1 block text-[11px] font-medium text-emerald-700">LinkUp calculates tax from the buyer's
                    delivery address, or the seller's location for pickup.</span>
            </span>
        </label>

        <div>
            <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider">Product images</label>
            <div class="mt-1 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/70 p-3">
                <div class="flex items-center gap-3">
                    <div class="grid grid-cols-2 gap-1 shrink-0">
                        <div v-for="(preview, index) in imagePreviews.slice(0, 4)" :key="preview"
                            class="h-10 w-10 overflow-hidden rounded-lg border border-slate-200 bg-white">
                            <img :src="preview" :alt="`Selected product image ${index + 1}`"
                                class="h-full w-full object-cover" />
                        </div>
                        <div v-if="!imagePreviews.length"
                            class="h-20 w-20 col-span-2 rounded-2xl border border-slate-200 bg-white grid place-items-center">
                            <ImageIcon class="h-8 w-8 text-slate-300" />
                        </div>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-black text-slate-700">Upload JPEG, PNG, or GIF</p>
                        <p class="mt-1 text-[10px] font-bold text-slate-400">{{ uploadStatus }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button"
                                class="rounded-xl bg-blue-600 px-3 py-2 text-[11px] font-black text-white inline-flex items-center gap-2"
                                @click="fileInput?.click()">
                                <Upload class="h-4 w-4" />
                                Choose images
                            </button>
                            <button v-if="form.cover_image" type="button"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-black text-slate-500 inline-flex items-center gap-2"
                                @click="clearUpload">
                                <X class="h-4 w-4" />
                                Remove
                            </button>
                        </div>
                    </div>
                </div>

                <input ref="fileInput" type="file" multiple class="hidden"
                    accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif" @change="handleImageUpload" />
            </div>
            <p v-if="form.errors.cover_image" class="text-[10px] text-rose-500 mt-1 font-bold">{{
                form.errors.cover_image }}</p>
            <p v-if="form.errors.images" class="text-[10px] text-rose-500 mt-1 font-bold">{{ form.errors.images }}</p>
        </div>

        <div>
            <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider">Description</label>
            <textarea class="input mt-1" v-model="form.description" rows="3"
                placeholder="Short description..."></textarea>
        </div>

        <!-- Trademark Box -->
        <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-slate-700 text-sm">™</span>
                <span class="text-xs font-black text-amber-900 uppercase tracking-wider">Trademark / Copyright
                    Rights</span>
            </div>

            <select v-model="form.trademark_status" class="input bg-white mb-3">
                <option>I own this trademark / copyright</option>
                <option>I have resale rights / license</option>
                <option>Not applicable (Unbranded/Generic)</option>
            </select>

            <input v-model="form.trademark_number" class="input mb-3"
                placeholder="Trademark / copyright registration # (optional)" />

            <input type="file" ref="tmFileInput" class="hidden" @change="handleTMFile" />
            <button type="button" @click="tmFileInput?.click()"
                :class="['w-full py-2.5 px-4 rounded-xl border-2 border-dashed text-xs font-black flex items-center gap-3 transition', form.trademark_file ? 'border-emerald-200 bg-emerald-50 text-emerald-600' : 'border-amber-200 text-amber-700 hover:bg-amber-100/50']">
                <span class="h-8 w-8 rounded-lg bg-white grid place-items-center shrink-0 shadow-sm">
                    <i :data-lucide="form.trademark_file ? 'check' : 'upload'" class="w-4 h-4"></i>
                </span>
                <div class="text-left">
                    <p>Upload TM / Copyright document</p>
                    <p class="text-[10px] opacity-60">
                        {{ form.trademark_file ? form.trademark_file.name : 'No file selected · optional' }}
                    </p>
                </div>
            </button>

            <label class="flex items-start gap-2.5 mt-4 cursor-pointer group">
                <input type="checkbox" v-model="form.confirm_rights"
                    class="mt-1 w-4 h-4 rounded border-amber-300 text-amber-600 focus:ring-amber-500" />
                <span class="text-[10px] font-bold text-amber-800 leading-tight">
                    I confirm I have the legal right to list and sell this product, and it does not infringe any
                    trademark, copyright, or other IP right. False declarations may result in listing removal and
                    account suspension.
                </span>
            </label>
        </div>

        <div class="flex gap-2 pt-2">

            <button
                class="flex-[1] rounded-2xl py-4 font-black text-white shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2"
                style="background: linear-gradient(135deg, #3b82f6 0%, #10b981 100%)"
                :disabled="form.processing || !form.confirm_rights" @click="storeProduct">
                ✓ {{ selectedProductId ? 'Update Product' : 'Publish' }}
            </button>
            <button v-if="selectedProductId" type="button"
                class="flex-[1] rounded-2xl border-2 border-rose-200 bg-rose-50 px-4 py-4 font-black text-rose-600 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="form.processing" @click="deleteProduct">
                Delete
            </button>
            <button
                class="flex-[1] rounded-2xl py-4 px-5 font-black border-2 border-slate-200 hover:bg-slate-50 transition"
                @click="$emit('back')">
                ← Back
            </button>
        </div>

        <!-- Add DeleteModal at the end of template -->
        <DeleteModal ref="deleteModalRef" title="Delete Product"
            message="Are you sure you want to delete this product?." @confirm="confirmDelete" />

        <ProductDeleteWarningModal :is-open="!!confirmDeleteProduct" :product="confirmDeleteProduct"
            :deleting="form.processing" @confirm="confirmDelete" @close="confirmDeleteProduct = null" />
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
    display: flex;
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

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
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
