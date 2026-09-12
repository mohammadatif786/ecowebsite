<template>
    <div class="rounded-2xl border border-slate-100 p-4 mb-4">
        <!-- Section Header -->
        <p class="font-black text-blue-700 mb-3 pb-2"
            style="border-bottom: 2px solid; border-image: linear-gradient(90deg,#2f9bef,#f59e0b) 1">
            Coupons
        </p>

        <label class="text-xs font-black text-slate-500">Selected Event *</label>
        <select v-model="form.link_up_event_id"
            class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-3">
            <option value="">Select Event</option>
            <option v-for="event in allEvent" :key="event?.id" :value="Number(event?.id)">
                {{ event?.title }}
            </option>
        </select>

        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="text-xs font-black text-slate-500">Code *</label>
                <input type="text" v-model="form.code"
                    class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1" />
            </div>
            <div>
                <label class="text-xs font-black text-slate-500">Title *</label>
                <input type="text" v-model="form.title"
                    class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1" />
            </div>
        </div>

        <div class="mb-3">
            <label class="text-xs font-black text-slate-500">Description</label>
            <textarea rows="3" v-model="form.description"
                class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1"></textarea>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="text-xs font-black text-slate-500">Discount Type *</label>
                <select v-model="form.discount_type"
                    class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1">
                    <option value="amount">Amount</option>
                    <option value="percentage">Percentage</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-black text-slate-500">Discount *</label>
                <input type="number" step="0.01" v-model="form.discount"
                    class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1" />
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="text-xs font-black text-slate-500">Expiry Date *</label>
                <input type="date" v-model="form.expiry_date"
                    class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1" />
            </div>
            <div>
                <label class="text-xs font-black text-slate-500">Status *</label>
                <select v-model="form.status"
                    class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1">
                    <option value="live">Live</option>
                    <option value="draft">Draft</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="text-xs font-black text-slate-500">Coupon Image</label>
            <input type="file" @change="handleFileUpload" accept="image/*" class="w-full mt-1 mb-2 text-sm" />
            <div v-if="imagePreview">
                <img :src="`${imagePreview}`" alt="Preview" class="h-32 w-auto rounded-xl border border-slate-200 object-cover" />
            </div>
        </div>

        <div v-if="form.errors" class="text-red-500 text-sm mt-2 mb-3">
            <ul class="list-disc pl-4">
                <li v-for="(err, idx) in form.errors" :key="idx">{{ err }}</li>
            </ul>
        </div>

        <button type="button" @click="saveCoupon"
            class="px-6 py-2 rounded-2xl font-black text-white text-sm transition hover:scale-[1.02]"
            style="background: linear-gradient(90deg,#2f9bef,#f59e0b)">
            {{ props.coupon == null ? 'Create' : 'Update' }}
        </button>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import { toast } from "vue-sonner";

const props = defineProps<{
    events: Array<{ id: number; title: string }>;
    coupon: {
        link_up_event_id?: number;
        code?: string;
        title?: string;
        description?: string;
        discount_type?: string;
        discount?: string;
        expiry_date?: string;
        status?: string;
        image_object?: string;
    } | null;
    appURL: string;
    allEvent: Array<{ id: number; title: string }>;
    edit: string | boolean;
}>();
const isEdit = computed(() => props.edit === true || props.edit === "true");

const showFields = ref<{ [k: number]: boolean }>({ 0: !!props.coupon });
const toggleSection = (index: number) => {
    showFields.value[index] = !showFields.value[index];
};

const form = useForm({
    link_up_event_id: props.coupon?.link_up_event_id ? Number(props.coupon?.link_up_event_id) : "",
    code: props.coupon?.code ?? "",
    title: props.coupon?.title ?? "",
    description: props.coupon?.description ?? "",
    discount_type: props.coupon?.discount_type ?? "",
    discount: props.coupon?.discount ?? "",
    expiry_date: props.coupon?.expiry_date
        ? new Date(props.coupon?.expiry_date).toISOString().split('T')[0]
        : "",
    status: props.coupon?.status ?? "expired",
    image: props.coupon?.image_object ?? null as File | null,
});

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.image = target.files[0];
    }
};
const saveCoupon = () => {
    if (isEdit.value == true && props.coupon) {
        form.post(route("organizer.event.coupon.update", props.coupon?.id), {
            forceFormData: true,
            onSuccess: () => {
                toast.success("Coupon updated successfully!");
            },
            onError: () => {
                toast.error("Failed to update coupon.");
            },
        });
    } else {

        form.post(route("organizer.event.coupon.store"), {
            forceFormData: true,
            onSuccess: () => {
                toast.success("Coupon saved successfully!");
            },
            onError: () => {
                toast.error("Failed to save coupon.");
            },
        });
    }

};
const imagePreview = computed(() => {
    if (form.image instanceof File) {
        return URL.createObjectURL(form.image);
    } else if (props.coupon?.image_object) {
        return `${props.appURL}${props.coupon.image_object}`;
    }
    return null;
});
</script>



