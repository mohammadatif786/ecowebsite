<script setup lang="ts">
import { X, CreditCard, CheckCircle, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    show: boolean;
    payout: any;
}>();

const emit = defineEmits(['close', 'approve', 'reject']);

const fmt = (n: number) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n || 0);

const getStatusBadgeClass = (status: string) => {
    const s = status?.toLowerCase();
    if (s === 'approved' || s === 'completed') return 'bg-green-50 text-green-700 border-green-100';
    if (s === 'pending') return 'bg-amber-50 text-amber-700 border-amber-100';
    if (s === 'processing') return 'bg-blue-50 text-blue-700 border-blue-100';
    if (s === 'rejected' || s === 'failed') return 'bg-red-50 text-red-700 border-red-100';
    return 'bg-gray-50 text-gray-700 border-gray-100';
};

const steps = [
    { id: 'pending', label: 'Pending', sub: 'Request submitted' },
    { id: 'processing', label: 'Processing', sub: 'Bank/provider processing' },
    { id: 'approved', label: 'Completed', sub: 'Funds transferred' },
    { id: 'rejected', label: 'Failed', sub: 'Rejected or failed' }
];

const currentStatus = computed(() => props.payout?.status?.toLowerCase() || 'pending');

const isStepActive = (stepId: string) => {
    if (currentStatus.value === 'approved' && stepId === 'approved') return true;
    if (currentStatus.value === 'rejected' && stepId === 'rejected') return true;
    return stepId === currentStatus.value;
};

const canProcess = computed(() => {
    const s = currentStatus.value;
    return s === 'pending' || s === 'processing';
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-150"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                    @click="emit('close')"
                ></div>

                <!-- Modal -->
                <Transition
                    enter-active-class="transition-all duration-200"
                    leave-active-class="transition-all duration-150"
                    enter-from-class="opacity-0 scale-95"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div
                        v-if="show"
                        class="relative w-full max-w-3xl bg-white rounded-[32px] shadow-2xl overflow-hidden"
                    >
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-[#B026FF] to-[#D600F5] px-7 py-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center">
                                        <CreditCard class="w-7 h-7 text-white" />
                                    </div>
                                    <div>
                                        <h3 class="text-3xl font-black text-white leading-none">Process Payout</h3>
                                        <p v-if="payout" class="text-sm text-white/80 font-bold mt-1.5">{{ payout.reference }}</p>
                                    </div>
                                </div>
                                <button
                                    @click="emit('close')"
                                    class="w-10 h-10 rounded-2xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors"
                                >
                                    <X class="w-5 h-5 text-white" />
                                </button>
                            </div>
                        </div>

                        <div class="p-8 space-y-8">
                            <!-- Financial Summary -->
                            <div class="grid grid-cols-3">
                                <div class="text-center px-4">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Amount</p>
                                    <p class="text-3xl font-black text-slate-900">{{ fmt(payout?.amount) }}</p>
                                </div>
                                <div class="text-center px-4 border-x border-slate-100">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Fee</p>
                                    <p class="text-3xl font-black text-[#F43F5E]">-{{ fmt(payout?.fee_amount) }}</p>
                                </div>
                                <div class="text-center px-4">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Net Payout</p>
                                    <p class="text-3xl font-black text-[#10B981]">{{ fmt(payout?.net_amount) }}</p>
                                </div>
                            </div>

                            <!-- Info Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Organizer -->
                                <div class="border border-slate-100 rounded-2xl p-4">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-1">Organizer</p>
                                    <p class="text-xl font-black text-slate-900">{{ payout?.organizer?.name }}</p>
                                </div>
                                <!-- Event -->
                                <div class="border border-slate-100 rounded-2xl p-4">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-1">Event</p>
                                    <p class="text-xl font-black text-slate-900">{{ payout?.event?.title }}</p>
                                </div>
                                <!-- Bank -->
                                <div class="border border-slate-100 rounded-2xl p-4">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-1">Bank</p>
                                    <p class="text-xl font-black text-slate-900">{{ payout?.organizer_bank?.name || 'LinkUp Wallet' }}</p>
                                </div>
                                <!-- Account -->
                                <div class="border border-slate-100 rounded-2xl p-4">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-1">Account</p>
                                    <p class="text-xl font-black text-slate-900">{{ payout?.organizer_bank?.account_number || 'Wallet' }}</p>
                                </div>
                                <!-- Method -->
                                <div class="border border-slate-100 rounded-2xl p-4">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-1">Method</p>
                                    <p class="text-xl font-black text-slate-900">{{ payout?.method }}</p>
                                </div>
                                <!-- Status -->
                                <div class="border border-slate-100 rounded-2xl p-4">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2">Status</p>
                                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase border" :class="getStatusBadgeClass(payout?.status)">
                                        {{ payout?.status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Payout Flow -->
                            <div class="space-y-4">
                                <h4 class="text-xl font-black text-slate-900">Payout Flow</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <div v-for="step in steps" :key="step.id"
                                        class="p-4 rounded-2xl border transition-all duration-300"
                                        :class="isStepActive(step.id)
                                            ? 'bg-[#FFFBEB] border-[#FEF3C7] shadow-sm'
                                            : 'bg-slate-50 border-slate-100 opacity-60'">
                                        <p class="text-sm font-black" :class="isStepActive(step.id) ? 'text-slate-900' : 'text-slate-600'">{{ step.label }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 mt-0.5">{{ step.sub }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Admin Note -->
                            <div class="bg-[#FFFBEB] border border-[#FEF3C7] rounded-2xl p-5 space-y-1.5">
                                <h5 class="text-sm font-black text-[#92400E]">Admin Note</h5>
                                <p class="text-xs font-bold text-[#D97706]/80 leading-relaxed">Review organizer, event, bank, amount, fee, and net payout before approval.</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-3 pt-4">
                                <button
                                    @click="emit('close')"
                                    class="px-8 py-3.5 rounded-2xl bg-[#F1F5F9] text-[#475569] font-black text-sm hover:bg-[#E2E8F0] transition active:scale-95"
                                >
                                    Cancel
                                </button>
                                <button
                                    v-if="canProcess"
                                    @click="emit('reject', '')"
                                    class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-[#F43F5E] text-white font-black text-sm hover:opacity-90 transition active:scale-95 shadow-lg shadow-[#F43F5E]/10"
                                >
                                    <XCircle class="w-4 h-4" /> Reject
                                </button>
                                <button
                                    v-if="canProcess"
                                    @click="emit('approve', '')"
                                    class="inline-flex items-center gap-2 px-10 py-3.5 rounded-2xl bg-[#10B981] text-white font-black text-sm hover:opacity-90 transition active:scale-95 shadow-lg shadow-[#10B981]/10"
                                >
                                    <CheckCircle class="w-4 h-4" /> Approve
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
