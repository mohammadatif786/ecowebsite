<template>
    <div class="min-h-screen bg-surface text-slate-800 flex">

        <Head title="Wallet" />

        <!-- Flash Notification -->
        <transition name="slide-down">
            <div v-if="showNotification"
                class="fixed top-4 right-4 z-[100] px-6 py-4 rounded-2xl shadow-lg flex items-center gap-3"
                :class="notificationType === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'">
                <span class="text-lg">{{ notificationType === 'success' ? '✓' : '✕' }}</span>
                <span class="font-semibold">{{ notificationMessage }}</span>
                <button @click="showNotification = false" class="ml-2 hover:opacity-70">×</button>
            </div>
        </transition>

        <Sidebar :user-profile="userProfile" @open="openModal" />

        <!-- Main -->
        <main class="flex-1 flex flex-col min-w-0 bg-[#f8fafc] overflow-hidden relative">
            <HeaderBar :currency="currency" :flags="flags" @set-currency="setCurrency" @open="openModal"
                @toggle-menu="toggleMenu" />

            <div class="flex-1 overflow-y-auto px-4 sm:px-8 pb-8 mt-5">
                <div class="space-y-8">
                    <!-- Header -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-slate-900 mb-2">Money Requests</h1>
                        <p class="text-slate-600">Manage your money requests sent and received</p>
                    </div>

                    <!-- Money Requests List -->
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                            <h2 class="text-xl font-bold text-slate-800">All Money Requests</h2>
                            <p class="text-sm text-slate-600 mt-1">{{ moneyRequests.length }} total requests</p>
                        </div>

                        <div v-if="moneyRequests.length === 0" class="p-12 text-center">
                            <div
                                class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-800 mb-2">No money requests found</h3>
                            <p class="text-slate-600">You haven't sent or received any money requests yet.</p>
                        </div>

                        <div v-else class="divide-y divide-slate-100">
                            <div v-for="request in moneyRequests" :key="request.id"
                                class="p-6 hover:bg-slate-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                          <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full capitalize"
                                            :class="statusClasses[request.status] ?? 'bg-slate-100 text-slate-600 border border-slate-200'"
                                        >
                                            {{ request.status }}
                                        </span>

                                        </div>

                                        <div class="flex items-center gap-4 mb-3">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-8 h-8 bg-linkup-dark text-white rounded-full flex items-center justify-center text-sm font-bold">
                                                    {{ request.requester_id === (page.props.auth as any).user.id ? 'S' :
                                                    'R' }}
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-slate-900">
                                                        {{ request.requester_id === (page.props.auth as any).user.id ?
                                                        request.recipient.name : request.requester.name }}
                                                    </p>
                                                    <p class="text-sm text-slate-500">
                                                        {{ request.requester_id === (page.props.auth as any).user.id ?
                                                        'Requested To' : 'Requested by' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-6 text-sm">
                                            <div>
                                                <span class="text-slate-500">Amount:</span>
                                                <span class="ml-1 font-semibold text-slate-900">
                                                    {{ formatCurrency(request.amount) }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-slate-500">Date:</span>
                                                <span class="ml-1 text-slate-700">
                                                    {{ formatDate(request.created_at) }}
                                                </span>
                                            </div>
                                        </div>

                                        <div v-if="request.note" class="mt-3">
                                            <p class="text-sm text-slate-600">
                                                <span class="font-medium">Note:</span> {{ request.note }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-2 ml-4">
                                        <template v-if="request.status === 'pending'">
                                            <template v-if="request.recipient_id === (page.props.auth as any).user.id">
                                                <!-- Received request - can accept/reject -->
                                                <button @click="sendModal(request)"
                                                    class="px-3 py-1.5 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-600 transition-colors">
                                                    Send
                                                </button>
                                                <button @click="rejectRequest(request.id)"
                                                    class="px-3 py-1.5 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition-colors">
                                                    Reject
                                                </button>
                                            </template>
                                            <template v-else>
                                                <!-- Sent request - can cancel -->
                                                <button @click="cancelRequest(request.id)"
                                                    class="px-3 py-1.5 bg-gray-500 text-white text-sm font-medium rounded-lg hover:bg-gray-600 transition-colors">
                                                    Cancel
                                                </button>
                                            </template>
                                        </template>
                                        <span v-else class="text-sm text-slate-500">
                                            No actions available
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Simple modal layer -->
        <teleport to="body">
            <transition name="fade">
                <div v-if="activeModal"
                    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center px-4"
                    @click.self="closeModal">
                    <BillPayModal v-if="activeModal === 'bill'" :utilities="utilityBillers" :mobile="mobileBillers"
                        @close="closeModal" @select="closeModal" />
                    <WalletModal v-else-if="activeModal === 'wallet'" :initial-tab="walletInitialTab"
                        @close="closeModal" @submit="closeModal" @select-pack="closeModal" />
                    <TransferModal v-else-if="activeModal === 'transfer'" @close="closeModal" @submit="closeModal" />
                    <PosModal v-else-if="activeModal === 'pos'" :tag="userProfile.tag" @close="closeModal" />
                    <MerchantModal v-else-if="activeModal === 'merchant'" @close="closeModal" />
                    <CryptoModal v-else-if="activeModal === 'crypto'" @close="closeModal" @select="closeModal" />
                    <HelpModal v-else-if="activeModal === 'help'" @close="closeModal" />
                    <SendMoneyModal v-else-if="activeModal === 'send'" @close="closeModal" :users="props.users" />
                    <RequestMoneyModal v-else-if="activeModal === 'request'" @close="closeModal" :users="props.users" />
                    <AsueModal v-else-if="activeModal === 'asue'" @close="closeModal" :users="props.users" />
                    <AddContactModal v-else-if="activeModal === 'add-contact'" @close="closeModal"
                        @submit="handleAddContact" />
                    <SendRequestMoneyModal v-else-if="activeModal === 'sendRequestMoney'" @close="closeModal" :currentBalanceUsd="currentBalanceUsd" :request="selectedRequest" />
                    <div v-else class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                        <div class="p-6 flex items-center justify-between border-b border-slate-100">
                            <h3 class="text-xl font-bold text-slate-900">{{ modalTitle }}</h3>
                            <button class="p-2 rounded-full hover:bg-slate-100" @click="closeModal">
                                <X class="w-5 h-5" />
                            </button>
                        </div>
                        <div class="p-6 space-y-4">
                            <p class="text-sm text-slate-500">
                                This is a lightweight preview modal. Connect your real actions later.
                            </p>
                            <div class="grid grid-cols-1 gap-3">
                                <input v-if="activeModal !== 'help'" type="text" placeholder="Enter details"
                                    class="w-full p-3 bg-slate-50 rounded-xl border border-slate-200 outline-none focus:border-linkup-blue" />
                                <textarea v-if="activeModal === 'send' || activeModal === 'request'" rows="3"
                                    placeholder="Add a note"
                                    class="w-full p-3 bg-slate-50 rounded-xl border border-slate-200 outline-none focus:border-linkup-blue"></textarea>
                            </div>
                            <div class="flex justify-end gap-3">
                                <button
                                    class="px-4 py-2 border border-slate-200 rounded-xl font-semibold text-slate-600"
                                    @click="closeModal">Close</button>
                                <button
                                    class="px-4 py-2 bg-linkup-dark text-white rounded-xl font-semibold">Continue</button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </teleport>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, defineOptions, watch } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { X } from 'lucide-vue-next';
import Sidebar from '../components/Sidebar.vue';
import HeaderBar from '../components/HeaderBar.vue';
import BillPayModal from '../components/BillPayModal.vue';
import RequestMoneyModal from '../components/RequestMoneyModal.vue';
import SendMoneyModal from '../components/SendMoneyModal.vue';
import AddContactModal from '../components/AddContactModal.vue';
import AsueModal from '../components/AsueModal.vue';
import WalletModal from '../components/WalletModal.vue';
import TransferModal from '../components/TransferModal.vue';
import HelpModal from '../components/HelpModal.vue';
import CryptoModal from '../components/CryptoModal.vue';
import MerchantModal from '../components/MerchantModal.vue';
import PosModal from '../components/PosModal.vue';
import SendRequestMoneyModal from '../components/SendRequestMoneyModal.vue';
import './../style.css';

defineOptions({ name: 'UserWalletIndex' });

type CurrencyCode = 'USD' | 'BSD' | 'JMD' | 'TTD' | 'XCD';
type ModalName =
    | 'wallet'
    | 'transfer'
    | 'send'
    | 'request'
    | 'bill'
    | 'merchant'
    | 'coins'
    | 'help'
    | 'asue'
    | 'crypto'
    | 'pos'
    | 'scan'
    | 'add-contact'
    | null;

const props = defineProps<{
    currentBalance: number | string;
    transactions: any[];
    users: any[];
    contacts: any[];
    sentRequests: any[];
    receivedRequests: any[];
    coinBalance?: number;
    subscriptions?: any[];
    moneyRequests: any[];
}>();
const fxRates: Record<CurrencyCode, number> = { USD: 1, BSD: 1, JMD: 155, TTD: 6.8, XCD: 2.7 };
const flags: Record<CurrencyCode, string> = { USD: '🇺🇸', BSD: '🇧🇸', JMD: '🇯🇲', TTD: '🇹🇹', XCD: '🏳️' };
const currency = ref<CurrencyCode>('USD');
const selectedRequest = ref<any>(null)
const userProfile = computed(() => {
    const authUser = (usePage().props.auth as any).user;
    return {
        name: authUser?.name || 'New User',
        tag: authUser?.tag || '~NewUser',
        linkup_id: authUser?.linkup_id || 'newuser',
        avatar: authUser?.avatar || null,
    };
});
const currentBalanceUsd = computed(() => toNumber(props.currentBalance));
function toNumber(value: any): number {
  if (typeof value === 'number') return value;
  if (typeof value === 'string') return parseFloat(value) || 0;
  if (value && typeof value.amount === 'number') return value.amount;
  return 0;
}
const activeModal = ref<ModalName>(null);
const walletInitialTab = ref<'cash' | 'coins'>('cash');
const utilityBillers = [
    { name: 'BPL Power', icon: 'zap' },
    { name: 'WSC Water', icon: 'droplet' },
    { name: 'JPS Power', icon: 'zap' },
];
const mobileBillers = [
    { name: 'BTC', icon: 'smartphone' },
    { name: 'Aliv', icon: 'smartphone' },
    { name: 'Flow', icon: 'wifi' },
    { name: 'Digicel', icon: 'signal' },
];

const modalTitle = computed(() => {
    switch (activeModal.value) {
        case 'wallet':
            return 'Top Up Wallet';
        case 'transfer':
            return 'Transfer to Bank';
        case 'send':
            return 'Send Money';
        case 'request':
            return 'Request Money';
        case 'bill':
            return 'Pay Bills';
        case 'merchant':
            return 'Merchant Mode';
        case 'coins':
            return 'Buy Coins';
        case 'help':
            return 'Help & Guide';
        case 'asue':
            return 'Digital Asue';
        case 'crypto':
            return 'Crypto Trading';
        case 'pos':
            return 'POS / Receive';
        case 'scan':
            return 'Scan Simulation';
        case 'add-contact':
            return 'Add Contact';
        default:
            return 'Wallet';
    }
});


function formatCurrency(amountUsd: number) {
    const converted = amountUsd * fxRates[currency.value];
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency.value,
    }).format(converted);
}

function formatDate(value: string) {
    const date = new Date(value);
    return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

function setCurrency(code: CurrencyCode | string) {
    currency.value = code as CurrencyCode;
}

function toggleMenu() {
    // Placeholder for mobile menu toggle
}

function openModal(name: ModalName | 'wallet-coins' | string) {
    if (name === 'wallet-coins') {
        walletInitialTab.value = 'coins';
        activeModal.value = 'wallet';
        return;
    }
    if (name === 'wallet') {
        walletInitialTab.value = 'cash';
        activeModal.value = 'wallet';
        return;
    }
    activeModal.value = name as ModalName;
}

function closeModal() {
    activeModal.value = null;
}

// Flash message handling
const page = usePage();
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref<'success' | 'error'>('success');

watch(() => (page.props as any).flash, (flash: any) => {
    if (flash?.success) {
        notificationMessage.value = flash.success;
        notificationType.value = 'success';
        showNotification.value = true;
        setTimeout(() => { showNotification.value = false; }, 4000);
    }
    if (flash?.error) {
        notificationMessage.value = flash.error;
        notificationType.value = 'error';
        showNotification.value = true;
        setTimeout(() => { showNotification.value = false; }, 4000);
    }
}, { immediate: true, deep: true });

async function handleAddContact(data: { name: string; userId: number }) {

    try {
        const payload = {
            contact_user_id: data.userId,
            name: data.name,
        };

        const response = await axios.post('/add/contact', payload);

        if (response.data.success) {
            notificationMessage.value = response.data.message || 'Contact added successfully!';
            notificationType.value = 'success';
            showNotification.value = true;
            setTimeout(() => { showNotification.value = false; }, 4000);
            closeModal();
            // Optionally refresh the page or contacts list
            window.location.reload();
        } else {
            // Handle duplicate contact or other errors
            notificationMessage.value = response.data.message || response.data.error || 'Failed to add contact';
            notificationType.value = 'error';
            showNotification.value = true;
            setTimeout(() => { showNotification.value = false; }, 4000);
            closeModal();
        }
    } catch (error: any) {
        console.error('Add contact error:', error);
        const errorMessage = error.response?.data?.message || error.response?.data?.error || 'Failed to add contact';
        notificationMessage.value = errorMessage;
        notificationType.value = 'error';
        showNotification.value = true;
        setTimeout(() => { showNotification.value = false; }, 4000);
    }
}

const sendModal = (request: any) => {
    selectedRequest.value = request
    activeModal.value = 'sendRequestMoney';
};

const rejectRequest = async (requestId: number) => {
    try {
         await axios.get(route('frontend.user.reject.money.request', { id: requestId }));

        alert('Money request rejected successfully.');

    } catch (error: any) {
        console.error('Reject money request error:', error);
    }
};

const cancelRequest = async (requestId: number) => {
    try {
        await axios.get(route('frontend.user.cancel.money.request', { id: requestId }));

        alert('Money request canceled successfully.');

    } catch (error: any) {
        console.error('Cancel money request error:', error);
    }
};
const statusClasses: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700 border border-yellow-200',
    accepted: 'bg-green-100 text-green-700 border border-green-200',
    approved: 'bg-green-100 text-green-700 border border-green-200',
    rejected: 'bg-red-100 text-red-700 border border-red-200',
    cancelled: 'bg-gray-100 text-gray-700 border border-gray-200',
};

</script>
