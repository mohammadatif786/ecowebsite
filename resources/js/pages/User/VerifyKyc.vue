<template>
    <AuthenticatedLayout>

        <Head title="KYC Verification" />
        <div class="flex text-center items-center gap-2 mb-8">
            <Wallet color="black" />
            <h1 class="text-3xl font-bold text-primary dark:text-black">KYC Verification</h1>
        </div>
        <div v-if="!props.userKycSubmitted">
            <main class="mx-auto max-w-6xl flex gap-8 p-6">
                <!-- Sidebar Steps -->
                <aside class="bg-white border rounded-2xl p-4 shadow-glass h-fit">
                    <div class="font-semibold mb-3">Steps</div>
                    <ol class="space-y-2 text-sm" id="steps">
                        <li class="flex items-center gap-2">
                            <span class="step-dot w-2 h-2 rounded-full bg-linkup-blue" data-step="front"></span>
                            Passport / ID — Front
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="step-dot w-2 h-2 rounded-full bg-slate-300" data-step="back"></span>
                            Passport / ID — Back
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="step-dot w-2 h-2 rounded-full bg-slate-300" data-step="poa"></span>
                            Proof of Address
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="step-dot w-2 h-2 rounded-full bg-slate-300" data-step="selfie"></span>
                            (Optional) Selfie
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="step-dot w-2 h-2 rounded-full bg-slate-300" data-step="review"></span>
                            Review & Submit
                        </li>
                    </ol>
                    <hr class="my-4" />

                    <!-- Quality Tips -->
                    <div class="text-xs text-slate-500 space-y-1">
                        <div class="font-semibold text-slate-700 mb-1">Quality Tips</div>
                        <p>• Use bright, even lighting.</p>
                        <p>• Fill the frame with the document.</p>
                        <p>• Avoid glare and blur.</p>
                        <p>• Capture all corners.</p>
                    </div>

                    <button @click="showHelp = true" class="mt-4 w-full px-3 py-2 rounded-xl border text-sm">
                        Help
                    </button>
                </aside>

                <!-- Main Content -->
                <section class="flex-1 space-y-6">
                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Front -->
                        <div class="bg-white border rounded-2xl p-4 shadow-glass">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 rounded-xl bg-linkup-blue/10 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-linkup-blue" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M3 7h18M6 7v10m12-10v10M9 11h6" />
                                    </svg>
                                </div>
                                <div class="font-semibold">Passport / ID — Front</div>
                                <span id="badge-front"
                                    class="ml-auto text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Pending</span>
                            </div>
                            <div id="dz-front" class="dropzone rounded-xl p-4 text-center">
                                <input id="file-front" type="file" accept="image/*" class="hidden"
                                    @change="handleFileChange($event, 'front_side')" />
                                <button class="px-3 py-2 rounded-xl bg-slate-900 text-white text-sm"
                                    onclick="document.getElementById('file-front').click()">
                                    Choose Image
                                </button>
                                <div class="text-xs text-slate-500 mt-2">PNG, JPG. Max 5MB.</div>
                                <img v-if="imagePreviews.front_side" :src="imagePreviews.front_side"
                                    class="mt-3 max-h-40 mx-auto rounded-lg" alt="Preview front" />
                            </div>
                        </div>

                        <!-- Back -->
                        <div class="bg-white border rounded-2xl p-4 shadow-glass">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 rounded-xl bg-linkup-blue/10 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-linkup-blue" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M3 7h18M6 7v10m12-10v10M9 13h6" />
                                    </svg>
                                </div>
                                <div class="font-semibold">Passport / ID — Back</div>
                                <span id="badge-back"
                                    class="ml-auto text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Pending</span>
                            </div>
                            <div id="dz-back" class="dropzone rounded-xl p-4 text-center">
                                <input id="file-back" type="file" accept="image/*" class="hidden"
                                    @change="handleFileChange($event, 'back_side')" />
                                <button class="px-3 py-2 rounded-xl bg-slate-900 text-white text-sm"
                                    onclick="document.getElementById('file-back').click()">
                                    Choose Image
                                </button>
                                <div class="text-xs text-slate-500 mt-2">PNG, JPG. Max 5MB.</div>
                                <img v-if="imagePreviews.back_side" :src="imagePreviews.back_side"
                                    class="mt-3 max-h-40 mx-auto rounded-lg" alt="Preview back" />
                            </div>
                        </div>

                        <!-- Proof of Address -->
                        <div class="bg-white border rounded-2xl p-4 shadow-glass md:col-span-2">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 rounded-xl bg-linkup-lime/20 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-linkup-dark" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M7 8h10M7 12h8M7 16h6" />
                                    </svg>
                                </div>
                                <div class="font-semibold">Proof of Address</div>
                                <span id="badge-poa"
                                    class="ml-auto text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Pending</span>
                            </div>
                            <div id="dz-poa" class="dropzone rounded-xl p-4 text-center">
                                <input id="file-poa" type="file" accept="image/*,application/pdf" class="hidden"
                                    @change="handleFileChange($event, 'address_proof')" />
                                <button class="px-3 py-2 rounded-xl bg-slate-900 text-white text-sm"
                                    onclick="document.getElementById('file-poa').click()">
                                    Choose Image / PDF
                                </button>
                                <div class="text-xs text-slate-500 mt-2">
                                    Utility bill, bank statement (last 90 days)
                                </div>
                                <img v-if="imagePreviews.address_proof" :src="imagePreviews.address_proof"
                                    class="mt-3 max-h-40 mx-auto rounded-lg" alt="Preview proof" />
                            </div>
                        </div>

                        <!-- Selfie -->
                        <div class="bg-white border rounded-2xl p-4 shadow-glass md:col-span-2">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 rounded-xl bg-linkup-blue/10 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-linkup-blue" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                        <path d="M20.4 14.5C19.1 12 16.7 10.5 14 10.5h-4c-2.7 0-5.1 1.5-6.4 4" />
                                    </svg>
                                </div>
                                <div class="font-semibold">(Optional) Selfie for face match</div>
                                <span id="badge-selfie"
                                    class="ml-auto text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Optional</span>
                            </div>
                            <div id="dz-selfie" class="dropzone rounded-xl p-4 text-center">
                                <input id="file-selfie" type="file" accept="image/*" capture="user" class="hidden"
                                    @change="handleFileChange($event, 'selfie')" />
                                <button class="px-3 py-2 rounded-xl bg-slate-900 text-white text-sm"
                                    onclick="document.getElementById('file-selfie').click()">
                                    Capture / Upload Selfie
                                </button>
                                <div class="text-xs text-slate-500 mt-2">
                                    Neutral expression, no hat or sunglasses
                                </div>
                                <!-- ✅ preview -->
                                <img v-if="imagePreviews.selfie" :src="imagePreviews.selfie"
                                    class="mt-3 max-h-40 mx-auto rounded-lg" alt="Preview selfie" />
                            </div>
                        </div>
                    </div>
                    <!-- Submit Button -->
                    <form @submit.prevent="submitForm" class="flex gap-3 pt-4">
                        <button type="submit" id="submitBtn"
                            class="ml-auto px-4 py-2 rounded-xl bg-linkup-lime text-white font-semibold">
                            Submit for Review
                        </button>
                    </form>
                </section>
            </main>
        </div>
        <div v-else
            class="w-full text-center bg-yellow-100 text-yellow-800 py-6 px-4 rounded-lg border border-yellow-300">
            <p class="text-lg font-semibold">In administrative processing</p>
            <p class="text-sm">
                Please wait until you hear back from us. We are reviewing your KYC documents.
            </p>
        </div>

        <!-- Help Modal -->
        <Modal :show="showHelp" @close="showHelp = false">
            <div class="space-y-3 text-sm text-slate-700">
                <p>
                    <strong>What happens here?</strong> We run quality checks (blur,
                    brightness, resolution) and on-device OCR to extract key fields.
                    Nothing is uploaded in this demo.
                </p>

                <ul class="list-disc pl-5 space-y-1">
                    <li>
                        <strong>Documents required:</strong> Passport/ID (front & back) and
                        a recent Proof of Address (utility bill or statement within 90 days).
                    </li>
                    <li>
                        <strong>Selfie (optional):</strong> In production we recommend a
                        selfie + liveness check. This demo provides a placeholder only.
                    </li>
                    <li>
                        <strong>Pass criteria:</strong> Good quality images and either an MRZ
                        block or a document number detected, plus an address from the proof.
                    </li>
                    <li><strong>Privacy:</strong> All processing runs in your browser.</li>
                </ul>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import Modal from "@/components/front/Modal.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps<{ userKycSubmitted: boolean }>();

const showHelp = ref(false);

const form = useForm({
    front_side: null,
    back_side: null,
    address_proof: null,
    selfie: null,
});

const imagePreviews = ref<{
    front_side: string | null;
    back_side: string | null;
    address_proof: string | null;
    selfie: string | null;
}>({
    front_side: null,
    back_side: null,
    address_proof: null,
    selfie: null,
});
function handleFileChange(
    event: Event,
    field: "front_side" | "back_side" | "address_proof"
) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form[field] = file;

        const reader = new FileReader();
        reader.onload = () => {
            imagePreviews.value[field] = reader.result as string;
        };
        reader.readAsDataURL(file);
    }
}

function submitForm() {
    form.post(route("frontend.user.kyc.update"), { forceFormData: true });
}
</script>

<style scoped>
img:hover {
    transform: scale(1.02);
    transition: transform 0.2s ease-in-out;
}
</style>
