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

            <div class="flex-1 overflow-y-auto px-4 sm:px-8 pb-8">
                <div class="space-y-8">
                    <img src="/storage/avatars/mainwalletlog.png" alt="Main Wallet Logo" class="w-full h-auto mb-4" />
                    <WalletTopUp :balance-text="formatCurrency(currentBalanceUsd)" :coin-balance="coinBalance"
                        @open="openModal" />

                    <QuickActions @open="openModal" />

                    <QuickLinkUps :contacts="quickContacts" @add-contact="openModal('add-contact')"
                        @send="tag => openModal('send', tag)" @remove="handleRemove"/>

                    <!-- Cards row -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6 fade-in"
                        style="animation-delay: 0.2s">
                        <div class="h-56 card-gradient rounded-[2rem] p-8 text-white flex flex-col justify-between glow-border relative group cursor-pointer"
                            @click="toggleMaskCard">
                            <div class="card-mesh"></div>
                            <div class="flex justify-between items-start relative z-10">
                                <div class="flex items-center gap-2 select-none" aria-label="LinkUp Wallet">
                                    <span
                                        class="w-9 h-9 rounded-full bg-white/15 border border-white/20 flex items-center justify-center">
                                        <InfinityIcon class="text-linkup-blue" :size="18" />
                                    </span>
                                    <span class="font-brand font-bold text-lg tracking-wide">Link Up</span>
                                </div>
                                <Wifi class="opacity-50 rotate-90" />
                            </div>
                            <div class="relative z-10">
                                <div class="font-mono text-2xl tracking-widest mb-2">
                                    {{ cardMasked ? '•••• •••• •••• 9010' : '4000 1234 5678 9010' }}
                                </div>
                                <div
                                    class="flex justify-between items-end opacity-60 text-xs font-bold tracking-widest">
                                    <span>NEW
                                        USER</span><span>12/28</span></div>
                            </div>
                        </div>

                        <div id="asueCardContainer"
                            class="h-56 asue-gradient rounded-[2rem] p-6 text-white relative overflow-hidden glow-border flex flex-col justify-between cursor-pointer group hover:-translate-y-1 transition-all"
                            style="background-image: url('/storage/avatars/asuelogo.png'); background-size: 100% 100%; background-position: center;"
                            @click="openModal('asue')">
                            <div class="absolute inset-0 asue-pattern opacity-10"></div>
                            <div class="relative z-10 flex flex-col items-center justify-center h-full text-center">
                                <!-- <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-3">
                  <Users class="text-white w-6 h-6" />
                </div> -->
                                <!-- <h3 class="font-bold text-lg">{{ props.activeAsue ? 'View Your Asue' : 'Start Digital Asue' }}</h3> -->
                                <!-- <p class="text-xs opacity-70">{{ props.activeAsue ? props.activeAsue.name : 'Create a savings circle.' -->
                                <!-- }}</p> -->
                            </div>
                        </div>

                        <div
                            class="h-56 crypto-gradient rounded-[2rem] p-6 text-white relative overflow-hidden glow-border flex flex-col justify-between group">
                            <div class="absolute right-0 top-0 opacity-10">
                                <Bitcoin class="w-32 h-32 -mr-6 -mt-6" />
                            </div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-8 h-8 rounded-full bg-orange-500 flex items-center justify-center">
                                        <Bitcoin class="w-5 h-5 text-white" />
                                    </div>
                                    <span class="font-bold text-lg">Crypto</span>
                                </div>
                                <div class="space-y-3 font-mono text-sm">
                                    <div class="flex justify-between items-center"><span
                                            class="opacity-60">BTC</span><span class="font-bold text-green-400">$96,420
                                            <span class="text-[10px] text-green-300">▲
                                                2.1%</span></span></div>
                                    <div class="flex justify-between items-center"><span
                                            class="opacity-60">ETH</span><span class="font-bold text-green-400">$3,650
                                            <span class="text-[10px] text-green-300">▲
                                                1.4%</span></span></div>
                                </div>
                            </div>
                            <button
                                class="relative z-10 w-full py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-sm font-bold transition-colors"
                                @click="openModal('crypto')">
                                Trade Assets
                            </button>
                        </div>

                        <div class="h-56 bank-gradient rounded-[2rem] p-6 text-white relative overflow-hidden glow-border flex flex-col justify-between cursor-pointer group hover:-translate-y-1 transition-all"
                            @click="openModal('add-bank')">
                            <div class="absolute inset-0 bank-pattern opacity-10"></div>
                            <div class="relative z-10 flex flex-col items-center justify-center h-full text-center">
                                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-3">
                                    <Building class="text-white w-6 h-6" />
                                </div>
                                <h3 class="font-bold text-lg">Bank Accounts</h3>
                                <p class="text-xs opacity-70">Manage your bank details</p>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions -->
                    <TransactionHistory :subscriptions="props.subscriptions || []"
                        :sentRequests="props.sentRequests || []" :bank-withdrawals="props.bankWithdrawals || []"
                        :transactions="displayTransactions" :format-currency="formatCurrency" :user="userProfileForAsue"
                        @sendRequestMoney="handleSendRequestMoney" @cancel-withdrawal="handleCancelWithdrawal" />
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
                    <TransferModal v-else-if="activeModal === 'transfer'" :available-balance="currentBalanceUsd"
                        :format-currency="formatCurrency" :bank-details="localBankDetails" @close="closeModal"
                        @submit="handleTransferToBank" />
                    <AddBankModal v-else-if="activeModal === 'add-bank'" :existing-bank-details="localBankDetails"
                        @close="closeModal" @submit="handleAddBank" />
                    <PosModal v-else-if="activeModal === 'pos'" :tag="userProfile.tag" @close="closeModal" />
                    <MerchantModal v-else-if="activeModal === 'merchant'" @close="closeModal" />
                    <CryptoModal v-else-if="activeModal === 'crypto'" @close="closeModal" @select="closeModal" />
                    <HelpModal v-else-if="activeModal === 'help'" @close="closeModal" />
                    <SendMoneyModal v-else-if="activeModal === 'send'" @close="closeModal" :users="props.users"
                        :initial-recipient="selectedRecipient" />
                    <RequestMoneyModal v-else-if="activeModal === 'request'" @close="closeModal" :users="props.users" />
                    <AsueDetailModal v-else-if="activeModal === 'asue' && props.activeAsue" @close="closeModal"
                        :asue="props.activeAsue" :currentUser="userProfileForAsue" @refresh="handleAsueRefresh" />
                    <AsueModal v-else-if="activeModal === 'asue'" @close="closeModal" :users="props.users" />
                    <AddContactModal v-else-if="activeModal === 'add-contact'" @close="closeModal"
                        @submit="handleAddContact" />
                    <SendRequestMoneyModal v-else-if="activeModal === 'sendRequestMoney'" @close="closeModal"
                        :currentBalanceUsd="currentBalanceUsd" :request="selectedRequest" />
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

        <!-- KYC Overlay -->
        <div v-if="kycStatus !== 'approved'"
            class="fixed inset-0 z-[100] bg-slate-900/90 backdrop-blur-md flex items-center justify-center overflow-y-auto py-10">
            <div class="w-full max-w-4xl px-4">
                <WalletKyc :status="kycStatus" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ArrowRightLeft,
    Wifi,
    Infinity as InfinityIcon,
    Bitcoin,
    Send,
    Coins,
    X,
    Users,
    Building,
} from 'lucide-vue-next';
import Sidebar from './components/Sidebar.vue';
import HeaderBar from './components/HeaderBar.vue';
import WalletTopUp from './components/WalletTopUp.vue';
import QuickActions from './components/QuickActions.vue';
import QuickLinkUps from './components/QuickLinkUps.vue';
import BillPayModal from './components/BillPayModal.vue';
import RequestMoneyModal from './components/RequestMoneyModal.vue';
import SendMoneyModal from './components/SendMoneyModal.vue';
import AddContactModal from './components/AddContactModal.vue';
import AsueModal from './components/AsueModal.vue';
import AsueDetailModal from './components/AsueDetailModal.vue';
import WalletModal from './components/WalletModal.vue';
import TransferModal from './components/TransferModal.vue';
import AddBankModal from './components/AddBankModal.vue';
import HelpModal from './components/HelpModal.vue';
import CryptoModal from './components/CryptoModal.vue';
import MerchantModal from './components/MerchantModal.vue';
import PosModal from './components/PosModal.vue';
import TransactionHistory from './components/TransactionHistory.vue';
import SendRequestMoneyModal from './components/SendRequestMoneyModal.vue';
import WalletKyc from './WalletKyc.vue';
import './style.css';
import { toast } from 'vue-sonner';

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
    | 'sendRequestMoney'
    | 'add-bank'
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
    activeAsue?: any;
    kycStatus: string;
    bankDetails?: {
        paypal_id?: string | null;
        banks?: any[];
    };
    bankWithdrawals?: any[];
}>();

const fxRates: Record<CurrencyCode, number> = { USD: 1, BSD: 1, JMD: 155, TTD: 6.8, XCD: 2.7 };
const flags: Record<CurrencyCode, string> = { USD: '🇺🇸', BSD: '🇧🇸', JMD: '🇯🇲', TTD: '🇹🇹', XCD: '🏳️' };
const currency = ref<CurrencyCode>('USD');

const coinBalance = computed(() => Number(props.coinBalance ?? 0));
const currentBalanceUsd = computed(() => toNumber(props.currentBalance));

// Local reactive state for bank details that can be updated after submission
const localBankDetails = ref({
    paypal_id: props.bankDetails?.paypal_id || null,
    banks: props.bankDetails?.banks || []
});

const userProfile = computed(() => {
    const authUser = (usePage().props.auth as any).user;
    return {
        name: authUser?.name || 'New User',
        tag: authUser?.tag || '~NewUser',
        linkup_id: authUser?.linkup_id || 'newuser',
        avatar: authUser?.avatar || null,
    };
});

const userProfileForAsue = computed(() => ({
    id: (usePage().props.auth as any).user.id,
    linkup_id: (usePage().props.auth as any).user.linkup_id,
    name: (usePage().props.auth as any).user.name,
    tag: (usePage().props.auth as any).user.tag,
}));

const activeModal = ref<ModalName>(null);
const selectedRecipient = ref<string | null>(null);
const cardMasked = ref(true);
const walletInitialTab = ref<'cash' | 'coins'>('cash');
const selectedRequest = ref<any>(null);
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

const localContacts = ref([...(props.contacts || [])]);

const quickContacts = computed(() =>
    (localContacts.value || []).map((contact) => {

        const contactUser = (contact as any).contactUser || (contact as any).contact_user;
        const displayName = contact.name || contactUser?.name || 'Contact';
        const tag = contactUser?.linkup_id || contactUser?.tag || contactUser?.username || 'user';
        const avatar = contactUser?.avatar
        const contactId = contactUser?.id

        return {
            name: displayName,
            tag,
            initials: displayName
                .split(' ')
                .map((p: string) => p[0])
                .join('')
                .slice(0, 2)
                .toUpperCase(),
            avatar: avatar,
            id: contactId
        }
    }),

);

const userMap = computed(() => {
    const map = new Map();
    (props.users || []).forEach(u => map.set(u.id, u));
    return map;
});

const displayTransactions = computed(() =>
    (props.transactions || []).map((tx: any, idx: number) => {
        const amountRaw = tx?.amount ?? tx?.value ?? tx?.pivot?.amount ?? 0;
        const amount = toNumber(amountRaw);
        const status = tx?.status ?? 'pending';
        const note = tx?.note ?? tx?.description ?? '';
        const rawDate = tx?.created_at ?? tx?.createdAt ?? tx?.timestamp ?? new Date().toISOString();
        const date = formatDate(rawDate);
        const type = (tx?.type ?? tx?.category ?? 'transaction').toString().replace(/_/g, ' ');

        // Use is_positive from backend if available
        const isPositive = tx?.is_positive ?? ((tx?.direction ?? tx?.is_credit ?? tx?.credit ?? false) ? true : ['topup', 'deposit', 'refund'].includes(type));

        const transaction = tx?.uuid ?? '--';
        const runningBalance = tx?.running_balance ?? 0;

        // Resolve sender/recipient if objects are missing but IDs exist
        let sender = tx?.sender;
        if (!sender && tx?.sender_id) {
            sender = userMap.value.get(tx.sender_id);
        }
        // Normalize sender fields
        if (sender) {
            sender = {
                name: sender.name,
                linkup_id: sender.linkup_id || sender.tag || sender.link_tag || ''
            };
        }

        let recipient = tx?.recipient;
        if (!recipient && tx?.recipient_id) {
            recipient = userMap.value.get(tx.recipient_id);
        }
        // Normalize recipient fields
        if (recipient) {
            recipient = {
                name: recipient.name,
                linkup_id: recipient.linkup_id || recipient.tag || recipient.link_tag || ''
            };
        }

        return {
            id: tx?.id ?? idx,
            type,
            status,
            note,
            date,
            createdAt: rawDate,
            amount,
            isPositive,
            transaction,
            prefix: isPositive ? '+' : '-',
            icon: txIcon(isPositive, tx?.asset),
            sender,
            recipient,
            runningBalance,
        };
    }),
);

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

function txIcon(isPositive: boolean, asset?: string) {
    if (asset === 'coins') return Coins;
    if (isPositive) return ArrowRightLeft;
    return Send;
}

function toNumber(value: any): number {
    if (typeof value === 'number') return value;
    if (typeof value === 'string') return parseFloat(value) || 0;
    if (value && typeof value.amount === 'number') return value.amount;
    return 0;
}

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

function openModal(name: ModalName | 'wallet-coins' | string, recipient: string | null = null) {
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
    if (name === 'requested-money') {
        router.visit(route('frontend.user.wallet.money.request.list'));
        return;
    }
    selectedRecipient.value = recipient;
    activeModal.value = name as ModalName;
}

function closeModal() {
    activeModal.value = null;
    selectedRecipient.value = null;
    selectedRequest.value = null;
}

function toggleMaskCard() {
    cardMasked.value = !cardMasked.value;
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

watch(() => (page.props as any).errors, (errors: any) => {
    if (errors && Object.keys(errors).length > 0) {
        const firstErrorKey = Object.keys(errors)[0];
        const errorMessage = errors[firstErrorKey];

        if (errorMessage) {
            notificationMessage.value = errorMessage;
            notificationType.value = 'error';
            showNotification.value = true;
            setTimeout(() => { showNotification.value = false; }, 5000);
        }
    }
}, { immediate: true, deep: true });

const handleAsueRefresh = () => {
    router.reload();
};

function handleSendRequestMoney(request: any) {
    // Set the selected request for the SendRequestMoneyModal
    selectedRequest.value = request;
    // Open the send request money modal
    activeModal.value = 'sendRequestMoney';
}

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

async function handleAddBank(data: { paypalId: string; banks: any[] }) {
    try {
        const payload = {
            paypal_id: data.paypalId,
            banks: data.banks
        };

        const response = await axios.post('/user/bank-details', payload);

        if (response.data.success) {
            // Update local bank details state with the newly submitted data
            localBankDetails.value = {
                paypal_id: data.paypalId,
                banks: data.banks.map((bank: any) => ({
                    bank_name: bank.name,
                    account_number: bank.accountNumber,
                    routing_number: bank.routingNumber,
                    account_type: bank.accountType,
                    is_default: bank.isDefault || false
                }))
            };

            notificationMessage.value = response.data.message || 'Bank details added successfully!';
            notificationType.value = 'success';
            showNotification.value = true;
            setTimeout(() => { showNotification.value = false; }, 4000);
            closeModal();
        } else {
            notificationMessage.value = response.data.message || response.data.error || 'Failed to add bank details';
            notificationType.value = 'error';
            showNotification.value = true;
            setTimeout(() => { showNotification.value = false; }, 4000);
            closeModal();
        }
    } catch (error: any) {
        console.error('Add bank error:', error);
        const errorMessage = error.response?.data?.message || error.response?.data?.error || 'Failed to add bank details';
        notificationMessage.value = errorMessage;
        notificationType.value = 'error';
        showNotification.value = true;
        setTimeout(() => { showNotification.value = false; }, 4000);
    }
}

async function handleTransferToBank(data: { amount: number; bankIndex: number }) {
    try {
        const response = await axios.post('/user/bank-withdrawal', {
            amount: data.amount,
            bankIndex: data.bankIndex
        });

        if (response.data.success) {
            notificationMessage.value = response.data.message || 'Withdrawal request submitted successfully!';
            notificationType.value = 'success';
            showNotification.value = true;
            setTimeout(() => { showNotification.value = false; }, 4000);
            closeModal();
            // Reload page to show updated balance and transaction history
            router.reload();
        } else {
            notificationMessage.value = response.data.message || response.data.error || 'Failed to submit withdrawal';
            notificationType.value = 'error';
            showNotification.value = true;
            setTimeout(() => { showNotification.value = false; }, 4000);
        }
    } catch (error: any) {
        console.error('Transfer to bank error:', error);
        const errorMessage = error.response?.data?.message || error.response?.data?.error || 'Failed to submit withdrawal';
        notificationMessage.value = errorMessage;
        notificationType.value = 'error';
        showNotification.value = true;
        setTimeout(() => { showNotification.value = false; }, 4000);
    }
}

async function handleCancelWithdrawal(withdrawalId: number) {
    try {
        const response = await axios.post(`/user/bank-withdrawal/cancel/${withdrawalId}`);

        if (response.data.success) {
            notificationMessage.value = response.data.message || 'Withdrawal cancelled successfully!';
            notificationType.value = 'success';
            showNotification.value = true;
            setTimeout(() => { showNotification.value = false; }, 4000);
            // Reload page to show updated balance and transaction history
            router.reload();
        } else {
            notificationMessage.value = response.data.message || response.data.error || 'Failed to cancel withdrawal';
            notificationType.value = 'error';
            showNotification.value = true;
            setTimeout(() => { showNotification.value = false; }, 4000);
        }
    } catch (error: any) {
        console.error('Cancel withdrawal error:', error);
        const errorMessage = error.response?.data?.message || error.response?.data?.error || 'Failed to cancel withdrawal';
        notificationMessage.value = errorMessage;
        notificationType.value = 'error';
        showNotification.value = true;
        setTimeout(() => { showNotification.value = false; }, 4000);
    }
}

const handleRemove = (id: number) => {
    localContacts.value = localContacts.value.filter(contact => {
        const contactUser = contact.contactUser || contact.contact_user;
        return contactUser?.id !== id;
    });

    toast.success('Contact removed successfully');
};
</script>
