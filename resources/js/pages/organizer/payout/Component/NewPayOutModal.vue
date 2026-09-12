<template>
    <Modal :show="showModal" @close="$emit('close')">
        <div class="fixed inset-0 flex items-center justify-center bg-[rgba(9,21,44,0.45)] p-5" role="dialog"
            aria-modal="true" aria-labelledby="payoutTitle">
            <div
                class="w-full max-w-[640px] bg-white rounded-2xl shadow-[0_24px_60px_rgba(10,32,90,0.35)] border border-[#cde0ff] overflow-hidden">
                <h3 id="payoutTitle" class="m-0 p-4 border-b border-[#eef2f8] bg-[#f8fbff] text-lg font-semibold">
                    Request payout
                </h3>

                <div class="p-4">
                    <!-- Steps UI -->
                    <div class="flex gap-2 items-center mb-2.5">
                        <!-- Step 1 -->
                        <div :class="[
                            'flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-xs font-bold border transition-all',
                            step === 1
                                ? 'bg-[#2f7de1] text-white border-[#2f7de1]'
                                : 'bg-[#eef4ff] text-[#2b4d86] border-[#cde0ff]'
                        ]">
                            <span :class="[
                                'w-4.5 h-4.5 rounded-full flex items-center justify-center font-extrabold text-[11px]',
                                step === 1
                                    ? 'bg-white text-[#2f7de1]'
                                    : 'bg-[#cfe0ff]'
                            ]">
                                1
                            </span>
                            Details
                        </div>

                        <!-- Step 2 -->
                        <div :class="[
                            'flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-xs font-bold border transition-all',
                            step === 2
                                ? 'bg-[#2f7de1] text-white border-[#2f7de1]'
                                : 'bg-[#eef4ff] text-[#2b4d86] border-[#cde0ff]'
                        ]">
                            <span :class="[
                                'w-4.5 h-4.5 rounded-full flex items-center justify-center font-extrabold text-[11px]',
                                step === 2
                                    ? 'bg-white text-[#2f7de1]'
                                    : 'bg-[#cfe0ff]'
                            ]">
                                2
                            </span>
                            Confirm
                        </div>

                        <!-- Step 3 -->
                        <div :class="[
                            'flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-xs font-bold border transition-all',
                            step === 3
                                ? 'bg-[#2f7de1] text-white border-[#2f7de1]'
                                : 'bg-[#eef4ff] text-[#2b4d86] border-[#cde0ff]'
                        ]">
                            <span :class="[
                                'w-4.5 h-4.5 rounded-full flex items-center justify-center font-extrabold text-[11px]',
                                step === 3
                                    ? 'bg-white text-[#2f7de1]'
                                    : 'bg-[#cfe0ff]'
                            ]">
                                3
                            </span>
                            Done
                        </div>
                    </div>

                    <!-- Step 1 -->
                    <div v-if="step === 1">
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Event select -->
                            <div class="border border-[#cde0ff] p-2.5 rounded-xl bg-white">
                                <label class="text-xs font-bold text-[#425a7b]">
                                    Event
                                    <span
                                        class="inline-flex items-center justify-center w-4.5 h-4.5 rounded-full bg-[#e8f1ff] text-[#2b4d86] font-extrabold cursor-help border border-[#cfe0ff]"
                                        title="Choose the event balance to withdraw from">
                                        ?
                                    </span>
                                </label>
                                <select v-model="form.event_id"
                                    class="w-full p-3 rounded-lg border border-[#cde0ff] bg-white focus:border-[#9fc0f3] focus:shadow-[0_0_0_4px_rgba(47,125,225,0.25)] outline-none">
                                    <option disabled value="">Select Event</option>
                                    <option v-for="event in events" :key="event.id" :value="event.id">{{ event.title }}
                                    </option>
                                </select>
                            </div>

                            <!-- Payout method -->
                            <div class="border border-[#cde0ff] p-2.5 rounded-xl bg-white">
                                <label class="text-xs font-bold text-[#425a7b]">
                                    Payout Method
                                    <span
                                        class="inline-flex items-center justify-center w-4.5 h-4.5 rounded-full bg-[#e8f1ff] text-[#2b4d86] font-extrabold cursor-help border border-[#cfe0ff]"
                                        title="Choose where to receive your funds">
                                        ?
                                    </span>
                                </label>
                                <select v-model="form.method"
                                    class="w-full p-3 rounded-lg border border-[#cde0ff] bg-white focus:border-[#9fc0f3] focus:shadow-[0_0_0_4px_rgba(47,125,225,0.25)] outline-none">
                                    <option selected disabled>Select Method</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="stripe">Stripe</option>
                                    <option value="bank">Bank Transfer</option>
                                </select>
                            </div>

                            <!-- Amount -->
                            <div class="border border-[#cde0ff] p-2.5 rounded-xl bg-white">
                                <label class="text-xs font-bold text-[#425a7b]">Amount available</label>
                                <div class="relative">
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</div>
                                    <input
                                        class="w-full p-3 pl-8 rounded-lg border border-[#cde0ff] bg-white focus:border-[#9fc0f3] focus:shadow-[0_0_0_4px_rgba(47,125,225,0.25)] outline-none"
                                        :value="formatCurrency(availableAmount)" readonly />
                                </div>
                            </div>

                            <div class="border border-[#cde0ff] p-2.5 rounded-xl bg-white">
                                <label class="text-xs font-bold text-[#425a7b]">
                                    Amount to payout
                                    <span
                                        class="inline-flex items-center justify-center w-4.5 h-4.5 rounded-full bg-[#e8f1ff] text-[#2b4d86] font-extrabold cursor-help border border-[#cfe0ff]"
                                        title="You can't request more than your available balance">
                                        ?
                                    </span>
                                </label>
                                <div class="flex gap-2 items-center">
                                    <input v-model="form.amount" @input="validateAmount"
                                        class="p-3 rounded-lg border border-[#cde0f6] bg-white focus:border-[#9fc0f3] focus:shadow-[0_0_0_4px_rgba(47,125,225,0.25)] outline-none w-30"
                                        type="number" min="0" :max="availableAmount" step="0.01" placeholder="0.00"
                                        :class="{ 'border-red-400': amountError }" />
                                    <button @click="setMaxAmount"
                                        class="flex gap-2 items-center border border-[#d6e2f5] bg-white text-[#264a86] px-3.5 py-2.5 rounded-lg font-semibold hover:border-[#aec8ef] hover:bg-[#f5f9ff] transition-all"
                                        type="button">
                                        Withdraw all
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Bank -->
                        <div class="grid grid-cols-2 gap-3 mt-2" v-if="form.method === 'bank'">
                            <div class="border border-[#cde0ff] p-2.5 rounded-xl bg-white">
                                <label class="text-xs font-bold text-[#425a7b]">Select bank account</label>
                                <select v-model="form.bank_account_id"
                                    class="w-full p-3 rounded-lg border border-[#cde0ff] bg-white focus:border-[#9fc0f3] focus:shadow-[0_0_0_4px_rgba(47,125,225,0.25)] outline-none">
                                    <option disabled value="">Select Bank Account</option>
                                    <option v-for="account in bankAccounts" :key="account.id" :value="account.id">
                                        {{ account.bank_name }} - {{ account.account_number }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="text-[#5b6b81] text-sm mt-2.5">
                            Fee: ${{ props.payout_processing }} · You'll receive: ${{
                                calculatePayout(form.amount) }}
                        </div>

                        <div class="h-px bg-[#eef2f8] my-3"></div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end gap-2.5">
                            <button @click="$emit('close')"
                                class="flex gap-2 items-center border border-[#d6e2f5] bg-white text-[#264a86] px-3.5 py-2.5 rounded-lg font-semibold hover:border-[#aec8ef] hover:bg-[#f5f9ff] transition-all"
                                aria-label="Cancel payout">
                                Cancel
                            </button>

                            <button @click="moveToConfirmationTab" :disabled="!canProceed"
                                class="flex gap-2 items-center bg-[#2f7de1] text-white px-3.5 py-2.5 rounded-lg font-semibold hover:bg-[#2563c9] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                aria-label="Continue to confirmation">
                                Continue
                            </button>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div v-else-if="step === 2">
                        <div
                            class="bg-[#f0f6ff] border border-dashed border-[#c7dcff] p-3 rounded-lg text-[#29569b] text-sm mb-2.5">
                            Review and confirm your payout details.
                        </div>
                        <div class="flex justify-between p-2 border-b border-dashed border-[#e5ecf9]">
                            <span>Event</span>
                            <strong>{{ selectedEvent?.title || 'Unknown Event' }}</strong>
                        </div>
                        <div class="flex justify-between p-2 border-b border-dashed border-[#e5ecf9]">
                            <span>Payout method</span>
                            <strong>{{ form.method === 'bank' ? 'Bank transfer' : form.method }}</strong>
                        </div>
                        <div class="flex justify-between p-2 border-b border-dashed border-[#e5ecf9]">
                            <span>Destination</span>
                            <strong v-if="form.method === 'bank'">{{ selectedBankAccount?.bank_name }} — Ending in {{
                                selectedBankAccount?.account_number?.slice(-4) }}</strong>
                            <strong v-else-if="form.method === 'paypal'">PayPal Account</strong>
                            <strong v-else-if="form.method === 'stripe'">Stripe Account</strong>
                            <strong v-else>Unknown</strong>
                        </div>
                        <div class="flex justify-between p-2 border-b border-dashed border-[#e5ecf9]">
                            <span>Amount requested</span>
                            <strong>${{ parseFloat(form.amount).toFixed(2) }}</strong>
                        </div>
                        <div class="flex justify-between p-2 border-b border-dashed border-[#e5ecf9]">
                            <span>Payout (Wire) Processing</span>
                            <strong>-{{ props.payout_processing }}%</strong>
                        </div>
                        <div class="flex justify-between p-2 border-b border-dashed border-[#e5ecf9]">
                            <span>Net you'll receive</span>
                            <strong>${{ (parseFloat(form.amount) - (parseFloat(form.amount) *
                                calculateFee(props.payout_processing))).toFixed(2)
                            }}</strong>
                        </div>
                        <div class="flex justify-between p-2">
                            <span>Remaining balance after this payout</span>
                            <strong>${{ (availableAmount - parseFloat(form.amount)).toFixed(2) }}</strong>
                        </div>
                        <div class="h-px bg-[#eef2f8] my-3"></div>
                        <div class="flex justify-end gap-2.5">
                            <button @click="moveToDetailTab"
                                class="flex gap-2 items-center border border-[#d6e2f5] bg-white text-[#264a86] px-3.5 py-2.5 rounded-lg font-semibold hover:border-[#aec8ef] hover:bg-[#f5f9ff] transition-all"
                                aria-label="Go back to edit">
                                Back
                            </button>
                            <button @click="moveToDoneTab" :disabled="loading"
                                class="flex gap-2 items-center bg-[#2f7de1] text-white px-3.5 py-2.5 rounded-lg font-semibold hover:bg-[#2563c9] transition-all disabled:opacity-50"
                                aria-label="Submit payout">
                                <span v-if="loading">Submitting...</span>
                                <span v-else>Submit Request</span>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div v-else-if="step === 3">
                        <div class="flex gap-3 items-center justify-center text-center p-5">
                            <div class="w-16 h-16 rounded-full bg-[#e7f7ef] border border-[#cfeede] grid place-items-center"
                                aria-hidden="true">
                                ✅
                            </div>
                            <div>
                                <h4 class="m-0 my-1.5 text-lg font-semibold">Request Submitted</h4>
                                <div class="text-[#5b6b81] text-sm">
                                    We’ll notify you when it’s processing. Expected arrival: 1–3 business days.
                                </div>
                                <div class="text-[#5b6b81] text-sm">
                                    Remaining balance for {{ selectedEvent?.title }}: ${{ (availableAmount -
                                        parseFloat(form.amount)).toFixed(2) }}
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2.5 mt-3">
                            <button @click="$emit('close')"
                                class="flex gap-2 items-center bg-[#2f7de1] text-white px-3.5 py-2.5 rounded-lg font-semibold hover:bg-[#2563c9] transition-all"
                                aria-label="Close dialog">
                                Done
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import Modal from '@/components/admin/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface Event {
    id: number;
    title: string;
    available_balance?: number;
}

interface BankAccount {
    id: number;
    bank_name: string;
    account_number: string;
}

const props = defineProps<{
    showModal: boolean;
    payouts: any[];
    eventId: number | null;
    events: Event[];
    settings: any;
    bankAccounts: BankAccount[];
    payout_processing: number
}>();

const step = ref(1);
const emit = defineEmits(['close']);

// Initialize form first
const form = useForm({
    event_id: props.eventId || '',
    method: '',
    amount: '',
    destination: '',
    bank_account_id: ''
});

// Format currency helper function
const formatCurrency = (value: number | string): string => {
    return parseFloat(value.toString()).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};

// Computed property to get available amount for selected event
const availableAmount = computed<number>(() => {
    if (!form.event_id) return 0;
    const event = props.events.find((e: Event) => e.id === form.event_id);
    return event?.available_balance || 0;
});

// Watch for event_id changes to update the form amount
watch(() => form.event_id, (newEventId) => {
    if (newEventId) {
        const event = props.events.find((e: Event) => e.id === newEventId);
        if (event?.available_balance && event.available_balance > 0) {
            form.amount = event.available_balance.toString();
        } else {
            // Clear amount if event has 0 or no available balance
            form.amount = '0';
        }
    } else {
        // Clear amount if no event is selected
        form.amount = '0';
    }
    // Clear any error state
    amountError.value = false;
});

const loading = ref(false);

// Computed properties for form validation and display
const selectedEvent = computed(() => {
    if (!form.event_id) return null;
    return props.events.find((e: Event) => e.id === form.event_id) || null;
});

const selectedBankAccount = computed(() => {
    return props.bankAccounts.find(account => account.id === form.bank_account_id);
});


const canProceed = computed(() => {
    if (step.value === 1) {
        const amount = parseFloat(form.amount) || 0;
        return form.event_id &&
            form.method &&
            amount > 0 &&
            amount <= availableAmount.value &&
            (form.method !== 'bank' || form.bank_account_id);
    }
    return true;
});

const moveToConfirmationTab = () => {
    if (canProceed.value) {
        step.value = 2;
    }
}

const moveToDetailTab = () => {
    step.value = 1;
}

const calculateFee = (amount: number): number => {
    if (amount <= 0) return 0;
    return parseFloat(((amount) / 100).toFixed(2));
};


const amountError = ref(false);

// Set amount to max available
const setMaxAmount = () => {
    form.amount = availableAmount.value.toString();
    amountError.value = false;
};

// Validate amount doesn't exceed available balance
const validateAmount = () => {
    const amountNum = parseFloat(form.amount) || 0;
    amountError.value = amountNum > availableAmount.value;

    // If amount exceeds available, cap it at max
    if (amountError.value) {
        form.amount = availableAmount.value.toString();
    }
};

const calculatePayout = (amount: string): string => {
    const amountNum = parseFloat(amount) || 0;
    if (amountNum <= 0) return '0.00';
    return (amountNum - (amountNum * calculateFee(props.payout_processing))).toFixed(2);
}

const submitPayout = () => {
    if (!canProceed.value) return;

    // Prepare destination based on method
    let destination = '';
    if (form.method === 'bank' && selectedBankAccount.value) {
        destination = `${selectedBankAccount.value.bank_name} - ${selectedBankAccount.value.account_number}`;
    } else if (form.method === 'paypal') {
        destination = 'PayPal Account';
    } else if (form.method === 'stripe') {
        destination = 'Stripe Account';
    }

    form.destination = destination;
    form.amount = parseFloat(form.amount);

    // Submit using Inertia
    form.post(route('organizer.payout.create'), {
        onSuccess: () => {
            step.value = 3;
        },
        onError: (errors) => {
            // Handle validation errors
            console.error('Form errors:', errors);
            alert('Please check your input and try again.');
        },
        onFinish: () => {
            loading.value = false;
        }
    });
}

const moveToDoneTab = () => {
    submitPayout();
}
</script>
