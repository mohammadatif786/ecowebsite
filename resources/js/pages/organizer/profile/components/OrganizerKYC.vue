<script setup lang="ts">
import { ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { Plus, Minus, Loader2, FileText, CheckCircle2, Info } from 'lucide-vue-next';

const page = usePage() as any;

const props = defineProps<{
    organizer_kyc: Record<string, string | null>;
    appURL: string;
}>();

// Form
const form = useForm<{
    passport_front: File | string | null;
    passport_back: File | string | null;
    proof_of_address: File | string | null;
}>({
    passport_front: props.organizer_kyc?.passport_front ?? null,
    passport_back: props.organizer_kyc?.passport_back ?? null,
    proof_of_address: props.organizer_kyc?.proof_of_address ?? null,
});

const isOpen = ref(!!props.organizer_kyc);

// Previews
const passportFrontPreview = ref<string | null>(
    props.organizer_kyc?.passport_front ? `${props.appURL}/${props.organizer_kyc.passport_front}` : null
);
const passportBackPreview = ref<string | null>(
    props.organizer_kyc?.passport_back ? `${props.appURL}/${props.organizer_kyc.passport_back}` : null
);
const proofAddressPreview = ref<string | null>(
    props.organizer_kyc?.proof_of_address ? `${props.appURL}/${props.organizer_kyc.proof_of_address}` : null
);

function handleFileChange(e: Event, field: "passport_front" | "passport_back" | "proof_of_address") {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;

    form[field] = file;

    const reader = new FileReader();
    reader.onload = (event) => {
        const result = event.target?.result as string;
        if (field === "passport_front") passportFrontPreview.value = result;
        if (field === "passport_back") passportBackPreview.value = result;
        if (field === "proof_of_address") proofAddressPreview.value = result;
    };
    reader.readAsDataURL(file);
}

const handleSubmit = () => {
    form.post(route("organizer.profile.update", { organizer_profile_type: "profileKYC" }), {
        forceFormData: true,
        onSuccess: () => {
            toast.success("✅ KYC submitted — usually reviewed within 24–48 hours");
        },
        onError: (errors) => {
            console.error("Form submission errors:", errors);
            toast.error("Failed to submit KYC documents.");
        },
    });
};
</script>

<template>
    <div class="card p-6 bg-white border border-slate-100 rounded-[20px] shadow-sm mb-4">
        <!-- Header matching orgCardHeader reference line 5007 -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h3 class="text-xl font-black text-slate-800">Organiser KYC</h3>
                <div class="h-[3px] rounded-full mt-1.5 max-w-full bg-gradient-to-r from-indigo-500 to-amber-400"></div>
            </div>
            <button @click="isOpen = !isOpen"
                class="h-9 w-9 rounded-full text-white grid place-items-center shrink-0 ml-3 bg-indigo-600 hover:bg-indigo-700 transition shadow-md shadow-indigo-500/20 active:scale-95">
                <Plus v-if="!isOpen" class="w-4 h-4" />
                <Minus v-else class="w-4 h-4" />
            </button>
        </div>

        <p class="text-slate-400 text-sm font-bold mb-4 uppercase tracking-tight" v-if="!isOpen">
            Complete the KYC process to ensure the safety and security of our community.
        </p>

        <form v-show="isOpen" @submit.prevent="handleSubmit" class="space-y-6 animate-in fade-in slide-in-from-top-2 duration-300">

            <div>
                <h4 class="font-black text-lg text-slate-800 mb-1">KYC Verification</h4>
                <p class="text-slate-500 text-sm font-medium mb-5">Know Your Customer process ensures community safety and security.</p>

                <!-- Step 1 -->
                <div class="mb-8">
                    <p class="font-black text-slate-800 text-sm mb-3">1. Passport Verification: <span class="text-slate-400 font-medium">Upload clear photos of the front and back of your valid passport.</span></p>

                    <div class="flex flex-wrap gap-4 ml-4">
                        <label class="h-28 w-28 rounded-2xl border-2 border-dashed border-blue-200 grid place-items-center cursor-pointer overflow-hidden bg-blue-50/40 hover:bg-blue-50 transition shrink-0 group relative">
                            <input type="file" accept="image/*,application/pdf" class="hidden" @change="handleFileChange($event, 'passport_front')" />
                            <img v-if="passportFrontPreview" :src="passportFrontPreview" class="h-full w-full object-cover" />
                            <div v-else class="text-center">
                                <Plus class="w-6 h-6 text-indigo-400 mx-auto" />
                                <span class="text-[10px] font-black text-indigo-400 uppercase mt-1 block">Front</span>
                            </div>
                            <div v-if="passportFrontPreview" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition grid place-items-center">
                                <span class="text-white text-[10px] font-black uppercase">Change</span>
                            </div>
                        </label>

                        <label class="h-28 w-28 rounded-2xl border-2 border-dashed border-blue-200 grid place-items-center cursor-pointer overflow-hidden bg-blue-50/40 hover:bg-blue-50 transition shrink-0 group relative">
                            <input type="file" accept="image/*,application/pdf" class="hidden" @change="handleFileChange($event, 'passport_back')" />
                            <img v-if="passportBackPreview" :src="passportBackPreview" class="h-full w-full object-cover" />
                            <div v-else class="text-center">
                                <Plus class="w-6 h-6 text-indigo-400 mx-auto" />
                                <span class="text-[10px] font-black text-indigo-400 uppercase mt-1 block">Back</span>
                            </div>
                            <div v-if="passportBackPreview" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition grid place-items-center">
                                <span class="text-white text-[10px] font-black uppercase">Change</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="mb-6">
                    <p class="font-black text-slate-800 text-sm mb-3">2. Proof of Address: <span class="text-slate-400 font-medium">Upload a document confirming your current residential address.</span></p>

                    <div class="flex flex-wrap gap-4 ml-4">
                        <label class="h-28 w-28 rounded-2xl border-2 border-dashed border-blue-200 grid place-items-center cursor-pointer overflow-hidden bg-blue-50/40 hover:bg-blue-50 transition shrink-0 group relative">
                            <input type="file" accept="image/*,application/pdf" class="hidden" @change="handleFileChange($event, 'proof_of_address')" />
                            <img v-if="proofAddressPreview" :src="proofAddressPreview" class="h-full w-full object-cover" />
                            <div v-else class="text-center">
                                <Plus class="w-6 h-6 text-indigo-400 mx-auto" />
                                <span class="text-[10px] font-black text-indigo-400 uppercase mt-1 block">Upload</span>
                            </div>
                            <div v-if="proofAddressPreview" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition grid place-items-center">
                                <span class="text-white text-[10px] font-black uppercase">Change</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="rounded-2xl bg-blue-50 p-4 border border-blue-100 flex gap-3 items-start">
                    <Info class="w-5 h-5 text-indigo-500 shrink-0" />
                    <p class="text-[12px] text-slate-500 font-bold leading-relaxed">
                        Rest assured that all provided information is handled with utmost confidentiality. Contact our support team if you need any assistance.
                    </p>
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
