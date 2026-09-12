<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { PackagePlus, Upload, X, Plus, Info } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    product?: any;
    categories: any[];
    sellers: any[];
}>();

const form = useForm({
    _method: props.product?.id ? 'PUT' : 'POST',
    name: props.product?.name || '',
    product_category_id: props.product?.product_category_id || '',
    user_id: props.product?.user_id || '',
    description: props.product?.description || '',
    qty: props.product?.qty || 0,
    price: props.product?.price || 0,
    status: props.product?.status ?? true,
    listing_type: props.product?.listing_type || 'Product',
    cover_image_file: null as File | null,
    images: [] as File[],
});

const coverPreview = ref(props.product?.cover_image || null);
const galleryPreviews = ref<string[]>(props.product?.images || []);

const onCoverChange = (e: any) => {
    const file = e.target.files[0];
    if (file) {
        form.cover_image_file = file;
        const reader = new FileReader();
        reader.onload = (e: any) => coverPreview.value = e.target.result;
        reader.readAsDataURL(file);
    }
};

const onGalleryChange = (e: any) => {
    const files = Array.from(e.target.files) as File[];
    if (files.length) {
        form.images = [...form.images, ...files];
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (e: any) => galleryPreviews.value.push(e.target.result);
            reader.readAsDataURL(file);
        });
    }
};

const removeGalleryItem = (index: number) => {
    galleryPreviews.value.splice(index, 1);
    // Note: in a real app, you might want to handle removing existing images vs new ones
    if (index < (props.product?.images?.length || 0)) {
        // Handle existing image removal logic if needed
    } else {
        form.images.splice(index - (props.product?.images?.length || 0), 1);
    }
};

const submit = () => {
    const url = props.product?.id
        ? route('admin.commerce.marketplace.products.update', props.product.id)
        : route('admin.commerce.marketplace.products.store');

    form.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Success handling
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Details -->
            <div class="lg:col-span-2 space-y-6">
                <div class="card rounded-3xl bg-white border-slate-100 shadow-sm p-6 lg:p-8 space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-50 pb-4">
                        <div class="h-10 w-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                            <Info class="w-5 h-5" />
                        </div>
                        <h4 class="text-xl font-black text-slate-900">Basic Information</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Product Name</label>
                            <input v-model="form.name" type="text" placeholder="Enter product name..."
                                class="w-full rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-purple-500 font-bold text-sm py-3.5 px-4 transition-all" />
                            <div v-if="form.errors.name" class="text-rose-500 text-[10px] font-black uppercase ml-1">{{ form.errors.name }}</div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Category</label>
                            <select v-model="form.product_category_id" class="w-full rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-purple-500 font-bold text-sm py-3.5 px-4 outline-none transition-all">
                                <option value="">Select Category</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <div v-if="form.errors.product_category_id" class="text-rose-500 text-[10px] font-black uppercase ml-1">{{ form.errors.product_category_id }}</div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Seller</label>
                            <select v-model="form.user_id" class="w-full rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-purple-500 font-bold text-sm py-3.5 px-4 outline-none transition-all">
                                <option value="">Select Seller</option>
                                <option v-for="seller in sellers" :key="seller.id" :value="seller.id">{{ seller.name }}</option>
                            </select>
                            <div v-if="form.errors.user_id" class="text-rose-500 text-[10px] font-black uppercase ml-1">{{ form.errors.user_id }}</div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Listing Type</label>
                            <select v-model="form.listing_type" class="w-full rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-purple-500 font-bold text-sm py-3.5 px-4 outline-none transition-all">
                                <option value="Product">Product</option>
                                <option value="Store">Store</option>
                                <option value="Administrative">Administrative</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Description</label>
                        <textarea v-model="form.description" rows="5" placeholder="Describe the product..."
                            class="w-full rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-purple-500 font-bold text-sm py-3.5 px-4 transition-all resize-none"></textarea>
                        <div v-if="form.errors.description" class="text-rose-500 text-[10px] font-black uppercase ml-1">{{ form.errors.description }}</div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Price (USD)</label>
                            <input v-model="form.price" type="number" step="0.01"
                                class="w-full rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-purple-500 font-bold text-sm py-3.5 px-4 transition-all" />
                            <div v-if="form.errors.price" class="text-rose-500 text-[10px] font-black uppercase ml-1">{{ form.errors.price }}</div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Stock Quantity</label>
                            <input v-model="form.qty" type="number"
                                class="w-full rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-purple-500 font-bold text-sm py-3.5 px-4 transition-all" />
                            <div v-if="form.errors.qty" class="text-rose-500 text-[10px] font-black uppercase ml-1">{{ form.errors.qty }}</div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Status</label>
                            <div class="flex items-center gap-4 py-2">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" :value="true" v-model="form.status" class="hidden" />
                                    <div class="w-5 h-5 rounded-full border-2 border-slate-200 group-hover:border-purple-500 flex items-center justify-center transition" :class="{ 'border-purple-600 bg-purple-600': form.status === true }">
                                        <div class="w-2 h-2 rounded-full bg-white"></div>
                                    </div>
                                    <span class="text-sm font-bold" :class="form.status === true ? 'text-slate-900' : 'text-slate-500'">Active</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" :value="false" v-model="form.status" class="hidden" />
                                    <div class="w-5 h-5 rounded-full border-2 border-slate-200 group-hover:border-purple-500 flex items-center justify-center transition" :class="{ 'border-purple-600 bg-purple-600': form.status === false }">
                                        <div class="w-2 h-2 rounded-full bg-white"></div>
                                    </div>
                                    <span class="text-sm font-bold" :class="form.status === false ? 'text-slate-900' : 'text-slate-500'">Inactive</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media -->
            <div class="space-y-6">
                <div class="card rounded-3xl bg-white border-slate-100 shadow-sm p-6 space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-50 pb-4">
                        <div class="h-10 w-10 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600">
                            <Upload class="w-5 h-5" />
                        </div>
                        <h4 class="text-xl font-black text-slate-900">Media</h4>
                    </div>

                    <!-- Cover Image -->
                    <div class="space-y-3">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Cover Image</label>
                        <div class="relative group">
                            <div v-if="coverPreview" class="relative rounded-2xl overflow-hidden aspect-video border-4 border-slate-50 shadow-sm">
                                <img :src="coverPreview" class="w-full h-full object-cover" />
                                <button type="button" @click="coverPreview = null; form.cover_image_file = null"
                                    class="absolute top-2 right-2 h-8 w-8 rounded-full bg-rose-500 text-white grid place-items-center shadow-lg hover:scale-110 transition opacity-0 group-hover:opacity-100">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                            <label v-else class="flex flex-col items-center justify-center aspect-video rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 hover:bg-white hover:border-purple-300 transition-all cursor-pointer group/upload">
                                <div class="h-12 w-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover/upload:text-purple-600 group-hover/upload:scale-110 transition">
                                    <Upload class="w-6 h-6" />
                                </div>
                                <span class="mt-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Click to upload cover</span>
                                <input type="file" @change="onCoverChange" class="hidden" accept="image/*" />
                            </label>
                        </div>
                        <div v-if="form.errors.cover_image_file" class="text-rose-500 text-[10px] font-black uppercase ml-1">{{ form.errors.cover_image_file }}</div>
                    </div>

                    <!-- Gallery -->
                    <div class="space-y-3">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Product Gallery</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="(img, idx) in galleryPreviews" :key="idx" class="relative group aspect-square rounded-2xl overflow-hidden border-2 border-slate-50">
                                <img :src="img" class="w-full h-full object-cover" />
                                <button type="button" @click="removeGalleryItem(idx)"
                                    class="absolute top-2 right-2 h-7 w-7 rounded-full bg-rose-500 text-white grid place-items-center shadow-lg hover:scale-110 transition opacity-0 group-hover:opacity-100">
                                    <X class="w-4 h-4" />
                                </button>
                                <!-- Error for individual image -->
                                <div v-if="form.errors[`images.${idx}`]" class="absolute inset-x-0 bottom-0 bg-rose-500/90 text-white text-[8px] font-black uppercase py-1 px-2 backdrop-blur-sm">
                                    {{ form.errors[`images.${idx}`] }}
                                </div>
                            </div>
                            <label class="flex flex-col items-center justify-center aspect-square rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 hover:bg-white hover:border-purple-300 transition-all cursor-pointer group/gal">
                                <Plus class="w-6 h-6 text-slate-400 group-hover/gal:text-purple-600 transition" />
                                <input type="file" multiple @change="onGalleryChange" class="hidden" accept="image/*" />
                            </label>
                        </div>
                        <!-- General gallery error -->
                        <div v-if="form.errors.images" class="text-rose-500 text-[10px] font-black uppercase ml-1">{{ form.errors.images }}</div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card rounded-3xl bg-white border-slate-100 shadow-sm p-6 flex flex-col gap-3">
                    <button type="submit" :disabled="form.processing"
                        class="w-full rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-4 font-black flex items-center justify-center gap-2 shadow-lg shadow-purple-600/20 hover:scale-[1.02] active:scale-95 transition disabled:opacity-50">
                        <span v-if="form.processing" class="animate-spin h-5 w-5 border-2 border-white/30 border-t-white rounded-full"></span>
                        {{ product?.id ? 'Update Product' : 'Create Product' }}
                    </button>
                    <button type="button" @click="router.visit(route('admin.commerce.marketplace.products'))"
                        class="w-full rounded-2xl bg-slate-100 text-slate-600 py-4 font-black hover:bg-slate-200 transition">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </form>
</template>
