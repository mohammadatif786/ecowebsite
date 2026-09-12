<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import { ref, Ref, onMounted, computed } from "vue";
import { toast } from "vue-sonner";
import { Plus, Minus, Loader2 } from 'lucide-vue-next';

const page = usePage()

const props = defineProps<{
    organizer_media: {
        logo?: string | null;
        cover_photo?: string | null;
        profile_photo?: string | null;
    };
    appURL: string
}>();

const isOpen = ref(!!props.organizer_media);

// --- Previews ---
const logoPreview = ref<string | null>(null);
const coverPreview = ref<string | null>(null);
const organizerPhotoPreview = ref<string | null>(null);

// --- Form ---
const form = useForm({
    logo: props.organizer_media?.logo ?? "",
    cover_photo: props.organizer_media?.cover_photo ?? "",
    profile_photo: props.organizer_media?.profile_photo ?? "",
});

const previewFile = (file: File | null, target: Ref<string | null>) => {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
        target.value = reader.result as string;
    };
    reader.readAsDataURL(file);
};

// --- File Handlers ---
const onLogoChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    if (file) {
        previewFile(file, logoPreview);
        form.logo = file;
    }
};

const onCoverChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    if (file) {
        previewFile(file, coverPreview);
        form.cover_photo = file;
    }
};

const onOrganizerPhotoChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    if (file) {
        previewFile(file, organizerPhotoPreview);
        form.profile_photo = file;
    }
};

// --- Initial Previews ---
onMounted(() => {
    logoPreview.value = props.organizer_media?.logo ? props.appURL + '/' + props.organizer_media.logo : null;
    coverPreview.value = props.organizer_media?.cover_photo ? props.appURL + '/' + props.organizer_media.cover_photo : null;
    organizerPhotoPreview.value = props.organizer_media?.profile_photo ? props.appURL + '/' + props.organizer_media.profile_photo : null;
});

const handleSubmit = () => {
    form.post(route('organizer.profile.update', { organizer_profile_type: 'AdditionalDetails' }), {
        forceFormData: true,
        onSuccess: () => {
            toast.success("Media details updated successfully!");
        },
        onError: (errors) => {
            console.error("Form submission errors:", errors);
            toast.error("Failed to update media details.");
        },
    });
}
</script>

<template>
    <div class="card p-6 bg-white border border-slate-100 rounded-[20px] shadow-sm mb-4">
        <!-- Header matching orgCardHeader reference line 5007 -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h3 class="text-xl font-black text-slate-800">Additional Details</h3>
                <div class="h-[3px] rounded-full mt-1.5 max-w-full bg-gradient-to-r from-indigo-500 to-amber-400"></div>
            </div>
            <button @click="isOpen = !isOpen"
                class="h-9 w-9 rounded-full text-white grid place-items-center shrink-0 ml-3 bg-indigo-600 hover:bg-indigo-700 transition shadow-md shadow-indigo-500/20 active:scale-95">
                <Plus v-if="!isOpen" class="w-4 h-4" />
                <Minus v-else class="w-4 h-4" />
            </button>
        </div>

        <p class="text-slate-400 text-sm font-bold mb-4 uppercase tracking-tight" v-if="!isOpen">
            Logo, cover photo and profile images.
        </p>

        <form v-show="isOpen" @submit.prevent="handleSubmit" class="space-y-6 animate-in fade-in slide-in-from-top-2 duration-300">

            <!-- Organizer Logo -->
            <div class="mb-5">
                <label class="text-sm font-black text-slate-700">Organizer Logo <span class="text-rose-500">*</span></label>
                <div class="mt-2 flex flex-wrap items-end gap-4">
                    <label class="inline-flex items-center gap-2 rounded-full text-white px-6 py-2.5 font-black text-xs cursor-pointer bg-indigo-600 hover:bg-indigo-700 transition shadow-md shadow-indigo-500/20 active:scale-95">
                        <input type="file" accept="image/*" class="hidden" @change="onLogoChange" />
                        Browse
                    </label>
                    <div v-if="logoPreview" class="relative group">
                        <img :src="logoPreview" class="h-28 w-28 rounded-2xl object-cover border border-slate-100 shadow-sm" />
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition rounded-2xl grid place-items-center">
                            <span class="text-white text-[10px] font-black uppercase">Change</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cover Photo -->
            <div class="mb-5">
                <label class="text-sm font-black text-slate-700">Cover Photo</label>
                <p class="text-[11px] text-indigo-500 font-bold uppercase tracking-tight mt-0.5">Optionally add a cover photo to showcase your organizer activities</p>
                <div class="mt-3 flex flex-wrap items-end gap-4">
                    <label class="inline-flex items-center gap-2 rounded-full text-white px-6 py-2.5 font-black text-xs cursor-pointer bg-indigo-600 hover:bg-indigo-700 transition shadow-md shadow-indigo-500/20 active:scale-95">
                        <input type="file" accept="image/*" class="hidden" @change="onCoverChange" />
                        Browse
                    </label>
                    <div v-if="coverPreview" class="relative group w-full max-w-md">
                        <img :src="coverPreview" class="h-32 w-full rounded-2xl object-cover border border-slate-100 shadow-sm" />
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition rounded-2xl grid place-items-center">
                            <span class="text-white text-[10px] font-black uppercase">Change Cover</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Organizer Photo -->
            <div class="mb-5">
                <label class="text-sm font-black text-slate-700">Organizer Photo <span class="text-rose-500">*</span></label>
                <p class="text-[11px] text-indigo-500 font-bold uppercase tracking-tight mt-0.5">Upload an image of yourself</p>
                <div class="mt-3 flex flex-wrap items-end gap-4">
                    <label class="inline-flex items-center gap-2 rounded-full text-white px-6 py-2.5 font-black text-xs cursor-pointer bg-indigo-600 hover:bg-indigo-700 transition shadow-md shadow-indigo-500/20 active:scale-95">
                        <input type="file" accept="image/*" class="hidden" @change="onOrganizerPhotoChange" />
                        Browse
                    </label>
                    <div v-if="organizerPhotoPreview" class="relative group">
                        <img :src="organizerPhotoPreview" class="h-28 w-28 rounded-2xl object-cover border border-slate-100 shadow-sm" />
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition rounded-2xl grid place-items-center">
                            <span class="text-white text-[10px] font-black uppercase">Change Photo</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" :disabled="form.processing"
                    class="px-10 py-2.5 rounded-full font-black text-slate-900 transition hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 bg-amber-400">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    Update
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
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
