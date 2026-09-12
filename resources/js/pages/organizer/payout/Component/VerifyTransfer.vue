<script setup>
import { ref } from 'vue';
import moment from 'moment';
import axios from 'axios';

const verifyModal = ref(true);
const successModal = ref(false);
const viewBreakdown = ref(false);
const toggleDetails = ref(false);

const payout = defineProps({
    payout: Object
});
const confirmReceipt = () => {

    axios.get(route('organizer.payout.verified.transfer', payout.payout.id))
        .then(function (response) {
            verifyModal.value = false;
            successModal.value = true;
        })
        .catch(function (error) {
            console.error(error);
        });

};

const redirectNow = () => {
    window.location.href = route('organizer.payout.request');
};

const downloadReceipt = () => {
    axios.get(route('organizer.payout.download.pdf', payout.payout.id), {
        responseType: 'blob'
    })
        .then((response) => {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'payout.pdf');
            document.body.appendChild(link);
            link.click();
        })
        .catch((error) => {
            console.error(error);
        });
};


const printConfirmation = () => {
    window.print();
};
</script>

<template>
    <!-- Main Container -->
    <div
        class="linkup-verification-container font-sans text-white min-h-screen flex items-center justify-center px-4 overflow-hidden bg-[#0F172A]">

        <!-- Animated Background Blobs -->
        <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
            <div
                class="absolute -top-40 -left-40 w-96 h-96 bg-[#0066FF] rounded-full mix-blend-screen filter blur-3xl opacity-50 animate-pulse-slow">
            </div>
            <div
                class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-400 rounded-full mix-blend-screen filter blur-3xl opacity-50 animate-pulse-slow delay-1000">
            </div>
            <div
                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-[#00D4FF] rounded-full mix-blend-screen filter blur-3xl opacity-40 animate-float">
            </div>
        </div>

        <div class="w-full max-w-2xl">
            <!-- VERIFICATION MODAL -->
            <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="verifyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <!-- Backdrop -->
                    <div @click="verifyModal = false"
                        class="absolute inset-0 bg-black/70 backdrop-blur-md transition-opacity"></div>

                    <!-- Modal Card -->
                    <div
                        class="relative glass rounded-3xl shadow-2xl overflow-hidden w-full max-w-lg transform transition-all duration-300 scale-100">
                        <!-- Top Accent Bar -->
                        <div class="gradient-bg h-1.5 w-full"></div>

                        <div class="p-8 md:p-10 text-center">
                            <!-- Icon -->
                            <div
                                class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-red-500/10 text-red-400 text-6xl mb-6 animate-pulse">
                                ⚠️
                            </div>

                            <!-- Title -->
                            <h2 class="text-3xl md:text-4xl font-bold font-display mb-3">
                                Secure Verification
                            </h2>

                            <!-- Main Text -->
                            <p class="text-base md:text-lg text-slate-200 leading-relaxed">
                                Please confirm that you have received your payout of
                                <span class="font-bold text-[#00D4FF]">{{ payout.payout.net_amount }}</span>.
                            </p>
                            <p class="text-red-300 font-semibold mt-3 text-xs md:text-sm uppercase tracking-[0.18em]">
                                This action is irreversible
                            </p>

                            <!-- Toggle Details -->
                            <button @click="toggleDetails = !toggleDetails"
                                class="mt-7 w-full py-3.5 rounded-2xl bg-white/8 backdrop-blur-md border border-white/20 text-slate-100 font-semibold hover:bg-white/15 transition-all duration-300 flex items-center justify-center gap-2">
                                <span>View Payout Details</span>
                                <svg v-show="!toggleDetails" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                                <svg v-show="toggleDetails" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 15l7-7 7 7" />
                                </svg>
                            </button>

                            <!-- Details Panel -->
                            <Transition enter-active-class="transition ease-out duration-300"
                                enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition ease-in duration-200"
                                leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                                <div v-show="toggleDetails"
                                    class="mt-5 p-5 bg-slate-900/70 rounded-2xl border border-slate-500/40 text-left text-sm space-y-2 text-slate-100">
                                    <div><span class="font-semibold text-slate-300">Event:</span> {{
                                        payout.payout.event.title }}</div>
                                    <div><span class="font-semibold text-slate-300">Date:</span> {{
                                        moment(payout.payout.created_at).format("DD MMM YYYY : HH:mm")
                                    }}</div>
                                    <div><span class="font-semibold text-slate-300">Gross:</span> ${{
                                        payout.payout.amount }}</div>
                                    <div><span class="font-semibold text-slate-300">Fees:</span> ${{
                                        payout.payout.fee_amount
                                    }}</div>
                                    <div class="pt-2 border-t border-slate-500/60 font-bold text-[#00D4FF] text-base">
                                        NET: ${{ payout.payout.net_amount }}
                                    </div>
                                    <div class="text-[11px] opacity-80 mt-2">Ref: {{ payout.payout.reference }}</div>
                                </div>
                            </Transition>

                            <!-- Buttons -->
                            <div class="mt-9 flex flex-col gap-3">
                                <button @click="verifyModal = false"
                                    class="py-3.5 rounded-2xl bg-slate-700/70 border border-slate-500/70 text-slate-100 font-semibold hover:bg-slate-600 transition">
                                    Cancel
                                </button>
                                <button @click="confirmReceipt"
                                    class="py-3.5 rounded-2xl bg-gradient-to-r from-[#0066FF] via-[#00D4FF] to-emerald-400 text-white font-bold text-lg shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300">
                                    ✓ Confirm Receipt
                                </button>
                            </div>

                            <!-- Footer Note -->
                            <p class="text-[11px] text-slate-300 mt-6 opacity-80">
                                🔒 LinkUp Secure • Transaction Hash Verified
                            </p>
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- SUCCESS MODAL -->
            <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                <div v-if="successModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div @click="successModal = false"
                        class="absolute inset-0 bg-[#0F172A]/85 backdrop-blur-xl transition-opacity"></div>

                    <div
                        class="relative bg-white rounded-3xl shadow-2xl overflow-hidden w-full max-w-lg transform transition-all duration-300 scale-100">
                        <div class="gradient-bg h-1.5"></div>
                        <div class="p-9 md:p-10 text-center text-[#0F172A]">
                            <div
                                class="inline-flex items-center justify-center w-24 h-24 md:w-28 md:h-28 rounded-full bg-emerald-50 text-emerald-500 text-6xl md:text-7xl mb-6 animate-bounce">
                                ✓
                            </div>
                            <h2 class="text-3xl md:text-4xl font-bold font-display mb-2">
                                Payment Verified
                            </h2>
                            <p class="text-lg text-slate-600">
                                ${{ payout.payout.net_amount }} successfully confirmed.
                            </p>
                            <p class="text-xs md:text-sm text-slate-500 mt-2">
                                You’ll be redirected to your payout dashboard shortly.
                            </p>

                            <div class="mt-9 grid grid-cols-2 gap-3 md:gap-4 text-sm md:text-base">
                                <button @click="downloadReceipt"
                                    class="py-3 md:py-3.5 rounded-2xl bg-slate-100 hover:bg-slate-200 font-semibold text-slate-700 transition">
                                    Download PDF
                                </button>
                                <button @click="viewBreakdown = true"
                                    class="py-3 md:py-3.5 rounded-2xl bg-slate-100 hover:bg-slate-200 font-semibold text-slate-700 transition">
                                    View Breakdown
                                </button>
                                <button @click="printConfirmation"
                                    class="py-3 md:py-3.5 rounded-2xl bg-slate-100 hover:bg-slate-200 font-semibold text-slate-700 transition">
                                    Print
                                </button>
                                <button @click="redirectNow"
                                    class="py-3 md:py-3.5 rounded-2xl bg-gradient-to-r from-[#0066FF] via-[#00D4FF] to-emerald-400 text-white font-bold shadow-md hover:shadow-lg transform hover:scale-[1.02] transition">
                                    Go to Dashboard →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- BREAKDOWN MODAL -->
            <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="viewBreakdown" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div @click="viewBreakdown = false" class="absolute inset-0 bg-black/75 backdrop-blur-md"></div>
                    <div
                        class="relative bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl p-7 md:p-8 max-w-md w-full border border-slate-200 text-[#0F172A]">
                        <h3 class="text-2xl md:text-3xl font-bold font-display mb-5">
                            Payout Breakdown
                        </h3>
                        <div class="space-y-3 md:space-y-4 text-base md:text-lg">
                            <div class="flex justify-between">
                                <span>💵 Gross Sales</span>
                                <strong>{{ payout.payout.amount }}</strong>
                            </div>
                            <!-- <div class="flex justify-between">
                                <span>⚙ Platform Fee</span>
                                <strong class="text-red-600">-$ {{ payout.payout.fee_amount }}</strong>
                            </div> -->
                            <div class="flex justify-between">
                                <span>💳 Processing Fee</span>
                                <strong class="text-red-600">-$ {{ payout.payout.fee_amount }}</strong>
                            </div>
                            <!-- <div class="flex justify-between">
                                <span>📦 Add-Ons</span>
                                <strong class="text-emerald-600">+$ {{ payout.payout.add_on }}</strong>
                            </div> -->
                            <div
                                class="pt-4 border-t-2 border-[#0066FF] flex justify-between text-xl md:text-2xl font-bold">
                                <span>NET PAYOUT</span>
                                <span class="text-[#0066FF]">$ {{ payout.payout.net_amount }}</span>
                            </div>
                        </div>
                        <button @click="viewBreakdown = false"
                            class="mt-7 w-full py-3.5 bg-[#0F172A] text-white rounded-2xl font-bold hover:bg-[#0066FF]/90 transition">
                            Close
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </div>
</template>

<style scoped>
/* Fonts */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap');

.font-sans {
    font-family: 'Inter', sans-serif;
}

.font-display {
    font-family: 'Space Grotesk', sans-serif;
}

/* Custom Animations */
@keyframes float {

    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-10px);
    }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-pulse-slow {
    animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Custom Classes */
.gradient-bg {
    background: linear-gradient(135deg, #0066FF 0%, #00D4FF 40%, #22c55e 80%);
}

.glass {
    background: rgba(15, 23, 42, 0.85);
    /* deep navy glass */
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border: 1px solid rgba(148, 163, 184, 0.4);
}
</style>
