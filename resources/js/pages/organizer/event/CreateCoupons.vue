<template>
    <AppLayout>
        <Head :title="`${isEdit ? 'Edit' : 'Create'} Coupon`" />

        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 overflow-hidden">
            <!-- Modal Content matching orgCouponForm reference line 4478 -->
            <div
                class="bg-white rounded-[20px] w-full max-w-lg max-h-[88vh] overflow-hidden shadow-2xl transform transition-transform flex flex-col">

                <!-- Modal Header -->
                <div class="p-5 flex items-center justify-between border-b border-slate-100">
                    <h3 class="text-xl font-black text-slate-800">{{ isEdit ? 'Edit Coupon' : 'Create New Coupon' }}</h3>
                    <Link :href="route('organizer.event.coupon.index')"
                        class="h-8 w-8 rounded-lg hover:bg-slate-100 grid place-items-center transition">
                        <X class="w-5 h-5 text-slate-500" />
                    </Link>
                </div>

                <!-- Modal Body -->
                <div class="modal-scroll overflow-y-auto p-5 flex-1">
                    <form @submit.prevent="saveCoupon" class="space-y-3">

                        <!-- Media Upload -->
                        <label class="block rounded-2xl border-2 border-dashed border-blue-200 bg-blue-50/40 grid place-items-center py-6 cursor-pointer mb-3 hover:bg-blue-50 transition">
                            <input
                                type="file"
                                id="coupon_image"
                                @change="handleFileUpload"
                                accept="image/*"
                                class="hidden"
                            />
                            <div v-if="imagePreview" class="relative group">
                                <img :src="imagePreview" alt="Preview" class="h-20 w-20 object-cover rounded-xl shadow-sm" />
                            </div>
                            <span v-else class="text-sm font-bold text-slate-500">Upload image</span>
                        </label>
                        <span v-if="form.errors.image" class="text-xs text-rose-500 font-bold mt-1 block">
                            {{ form.errors.image }}
                        </span>

                        <!-- Form Fields matching reference -->
                        <input
                            type="text"
                            v-model="form.title"
                            placeholder="Coupon title"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 font-medium"
                            :class="{ 'border-rose-500': form.errors.title }"
                        />
                        <span v-if="form.errors.title" class="text-xs text-rose-500 font-bold mt-1 block ml-1">
                            {{ form.errors.title }}
                        </span>

                        <input
                            type="text"
                            v-model="form.code"
                            placeholder="CODE"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 font-bold uppercase"
                            :class="{ 'border-rose-500': form.errors.code }"
                        />
                        <span v-if="form.errors.code" class="text-xs text-rose-500 font-bold mt-1 block ml-1">
                            {{ form.errors.code }}
                        </span>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="space-y-1">
                                <select
                                    v-model="form.discount_type"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-3 outline-none focus:border-blue-400 bg-white font-medium text-sm transition"
                                >
                                    <option value="percentage">% Off</option>
                                    <option value="amount">$ Off</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <input
                                    type="number"
                                    step="0.01"
                                    v-model="form.discount"
                                    placeholder="Amount"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-3 outline-none focus:border-blue-400 font-black text-center"
                                    :class="{ 'border-rose-500': form.errors.discount }"
                                />
                            </div>
                        </div>
                        <span v-if="form.errors.discount" class="text-xs text-rose-500 font-bold mt-1 block ml-1">
                            {{ form.errors.discount }}
                        </span>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="space-y-1">
                                <input
                                    type="number"
                                    v-model="form.max_uses"
                                    placeholder="Max uses"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-3 outline-none focus:border-blue-400 font-medium text-center"
                                />
                            </div>
                            <div class="space-y-1">
                                <input
                                    type="date"
                                    v-model="form.expiry_date"
                                    :min="tomorrow"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-3 outline-none focus:border-blue-400 font-bold"
                                    :class="{ 'border-rose-500': form.errors.expiry_date }"
                                />
                            </div>
                        </div>
                        <span v-if="form.errors.expiry_date" class="text-xs text-rose-500 font-bold mt-1 block ml-1">
                            {{ form.errors.expiry_date }}
                        </span>

                        <select
                            v-model="form.link_up_event_id"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 bg-white font-medium text-sm transition"
                            :class="{ 'border-rose-500': form.errors.link_up_event_id }"
                        >
                            <option value="">Select Event *</option>
                            <option v-for="event in allEvent" :key="event.id" :value="Number(event.id)">
                                {{ event.title }}
                            </option>
                        </select>
                        <span v-if="form.errors.link_up_event_id" class="text-xs text-rose-500 font-bold mt-1 block ml-1">
                            {{ form.errors.link_up_event_id }}
                        </span>

                        <!-- Save Button -->
                        <button
                            type="submit"
                            class="btn btn-primary w-full py-3 rounded-xl font-black text-white transition hover:brightness-105 active:scale-[0.98] disabled:opacity-50 shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2 mt-4"
                            style="background: linear-gradient(135deg,#2f9bef,#2563eb)"
                            :disabled="form.processing"
                        >
                            <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                            {{ isEdit ? 'Save Changes' : 'Create Coupon' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import { X, Upload, Loader2 } from 'lucide-vue-next';

const props = defineProps<{
    allEvent: Array<{ id: number; title: string }>;
    coupon: any | null;
    appURL: string;
    edit: boolean | string;
}>();

const isEdit = computed(() => props.edit === true || props.edit === "true");

// Get tomorrow's date for min date validation
const tomorrow = computed(() => {
    const today = new Date();
    today.setDate(today.getDate() + 1);
    return today.toISOString().split('T')[0];
});

const getInitialData = () => {
    // In case props.coupon is passed as a collection (array) instead of a single object
    const couponData = Array.isArray(props.coupon) ? props.coupon[0] : props.coupon;

    return {
        link_up_event_id: couponData?.link_up_event_id ?? "",
        code: couponData?.code ?? "",
        title: couponData?.title ?? "",
        description: couponData?.description ?? "",
        discount_type: couponData?.discount_type ?? "amount",
        discount: couponData?.discount ?? "",
        expiry_date: couponData?.expiry_date
            ? new Date(couponData?.expiry_date).toISOString().split('T')[0]
            : "",
        status: couponData?.status ?? "draft",
        image: null as File | string | null,
    };
};

const form = useForm(getInitialData());

// Handle image selection
const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.image = target.files[0];
    }
};

// Image preview
const imagePreview = computed(() => {
    if (form.image instanceof File) {
        return URL.createObjectURL(form.image);
    } else if (props.coupon?.image_object) {
        return `${props.appURL}${props.coupon.image_object}`;
    }
    return null;
});

// Save or update coupon
const saveCoupon = () => {
    const routeName = isEdit.value
        ? route("organizer.event.coupon.update", props.coupon.id)
        : route("organizer.event.coupon.store");

    form.post(routeName, {
        forceFormData: true,
        onSuccess: () => {
            toast.success(isEdit.value ? 'Coupon updated successfully!' : 'Coupon created successfully!');
            router.visit(route('organizer.event.coupon.index'));
        },
        onError: (errors) => {
            console.error("Form submission errors:", errors);
            toast.error("Please check the form for errors.");
        },
    });
};
</script>

<style scoped>
.modal-scroll {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
.modal-scroll::-webkit-scrollbar {
    width: 8px;
}
.modal-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.modal-scroll::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
    border: 2px solid white;
    background-clip: padding-box;
}
.modal-scroll::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8;
}

/* Animations */
.animate-in {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}
.fade-in {
    animation-name: fadeIn;
}
.slide-in-from-top-2 {
    animation-name: slideInFromTop;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInFromTop {
    from { transform: translateY(-0.5rem); }
    to { transform: translateY(0); }
}
</style>
