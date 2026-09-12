<template>
    <AppLayout>
        <Head :title="`${isEdit ? 'Edit' : 'Create'} Sponsor`" />

        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 overflow-hidden">
            <!-- Modal Content matching orgSponsorForm reference line 4394 -->
            <div
                class="bg-white rounded-[20px] w-full max-w-lg max-h-[88vh] overflow-hidden shadow-2xl transform transition-transform flex flex-col">

                <!-- Modal Header -->
                <div class="p-5 flex items-center justify-between border-b border-slate-100">
                    <h3 class="text-xl font-black text-slate-800">{{ isEdit ? 'Edit Sponsor' : 'Create New Sponsor' }}</h3>
                    <Link :href="route('organizer.event.sponsor.index')"
                        class="h-8 w-8 rounded-lg hover:bg-slate-100 grid place-items-center transition">
                        <X class="w-5 h-5 text-slate-500" />
                    </Link>
                </div>

                <!-- Modal Body -->
                <div class="modal-scroll overflow-y-auto p-5 flex-1">
                    <form @submit.prevent="saveSponsor" class="space-y-3">

                        <!-- Media Upload matching dashed style -->
                        <label class="block rounded-2xl border-2 border-dashed border-blue-200 bg-blue-50/40 grid place-items-center py-6 cursor-pointer mb-3 hover:bg-blue-50 transition">
                            <input
                                type="file"
                                id="sponsor_image"
                                @change="handleImageUpload"
                                accept="image/*,video/*"
                                class="hidden"
                            />
                            <div v-if="existingImage" class="relative group">
                                <video v-if="isVideoPreview" :src="existingImage" class="h-20 w-20 object-cover rounded-xl shadow-sm" muted></video>
                                <img v-else :src="existingImage" alt="Preview" class="h-20 w-20 object-cover rounded-xl shadow-sm" />
                            </div>
                            <span v-else class="text-sm font-bold text-slate-500">Upload image</span>
                        </label>
                        <span v-if="form.errors.sponsor_image_file" class="text-xs text-rose-500 font-bold mt-1 block">
                            {{ form.errors.sponsor_image_file }}
                        </span>

                        <!-- Form Fields -->
                        <input
                            type="text"
                            v-model="form.name"
                            placeholder="Sponsor name"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 font-medium"
                            :class="{ 'border-rose-500': form.errors.name }"
                        />
                        <span v-if="form.errors.name" class="text-xs text-rose-500 font-bold mt-1 block ml-1">
                            {{ form.errors.name }}
                        </span>

                        <textarea
                            rows="3"
                            v-model="form.description"
                            placeholder="Description"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 font-medium text-sm"
                        ></textarea>

                        <select
                            v-model="form.event_id"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 bg-white font-medium text-sm transition"
                            :class="{ 'border-rose-500': form.errors.event_id }"
                        >
                            <option value="">Select Event *</option>
                            <option v-for="event in props.allEvent" :key="event.id" :value="event.id.toString()">
                                {{ event.title }}
                            </option>
                        </select>
                        <span v-if="form.errors.event_id" class="text-xs text-rose-500 font-bold mt-1 block ml-1">
                            {{ form.errors.event_id }}
                        </span>

                        <!-- Status Radio Buttons matching line 4404 -->
                        <div class="flex gap-4 mb-4 py-2">
                            <label class="flex items-center gap-2 font-bold text-sm cursor-pointer">
                                <input type="radio" value="active" v-model="form.status" class="accent-blue-600" />
                                Active
                            </label>
                            <label class="flex items-center gap-2 font-bold text-sm cursor-pointer">
                                <input type="radio" value="inactive" v-model="form.status" class="accent-blue-600" />
                                Inactive
                            </label>
                        </div>

                        <!-- Save Button matching btn-primary style -->
                        <button
                            type="submit"
                            class="btn btn-primary w-full py-3 rounded-xl font-black text-white transition hover:brightness-105 active:scale-[0.98] disabled:opacity-50 flex items-center justify-center gap-2"
                            style="background: linear-gradient(135deg,#2f9bef,#2563eb)"
                            :disabled="form.processing"
                        >
                            <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                            {{ isEdit ? 'Save Changes' : 'Create Sponsor' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import { X, Upload, Loader2 } from 'lucide-vue-next';
import { toast } from "vue-sonner";

const props = defineProps<{
    allEvent: Array<any>;
    sponsor?: any | null;
    appURL: string;
    edit?: boolean | string;
}>();

const isEdit = computed(() => props.edit === true || props.edit === "true");

const getInitialData = () => {
    // Handle case where sponsor might be passed as an array
    const sponsorData = Array.isArray(props.sponsor) ? props.sponsor[0] : props.sponsor;

    return {
        event_id: sponsorData?.link_up_event_id?.toString() ?? "",
        name: sponsorData?.name ?? "",
        description: sponsorData?.description ?? "",
        status: sponsorData?.status ? "active" : "inactive",
        sponsor_image_file: null as File | null,
    };
};

const form = useForm(getInitialData());

const detectVideoFromUrl = (url: string | null) => {
    if (!url) return false;
    return /\.(mp4|webm|ogg)$/i.test(url.split('?')[0]);
};

const getInitialImage = () => {
    const sponsorData = Array.isArray(props.sponsor) ? props.sponsor[0] : props.sponsor;
    if (sponsorData?.image_url) return sponsorData.image_url;
    if (sponsorData?.image_object) return `${props.appURL}${sponsorData.image_object}`;
    return null;
};

const existingImage = ref<string | null>(getInitialImage());

const isVideoPreview = ref<boolean>(detectVideoFromUrl(existingImage.value));

// Handle media selection
const handleImageUpload = (event: Event) => {
    const fileInput = event.target as HTMLInputElement;
    if (fileInput.files && fileInput.files.length > 0) {
        const file = fileInput.files[0];
        form.sponsor_image_file = file;
        existingImage.value = URL.createObjectURL(file);
        isVideoPreview.value = file.type.startsWith('video/');
    }
};

// Remove image
const removeImage = () => {
    form.sponsor_image_file = null;
    existingImage.value = null;
    isVideoPreview.value = false;
};

// Reset form
const resetForm = () => {
    form.reset();
    form.clearErrors();
    existingImage.value = null;
    isVideoPreview.value = false;
};

// Save sponsor
const saveSponsor = () => {
    const routeName = isEdit.value && props.sponsor
        ? route('organizer.event.sponsor.update', props.sponsor.id)
        : route('organizer.event.sponsor.store');

    form.post(routeName, {
        forceFormData: true,
        onSuccess: () => {
            toast.success(isEdit.value ? "Sponsor updated successfully!" : "Sponsor created successfully!");
            router.visit(route('organizer.event.sponsor.index'));
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
