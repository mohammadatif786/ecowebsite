<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';
import Header from './Components/Header.vue';
import EarningsBreakdownTable from './Components/EarningsBreakdownTable.vue';
import ReportsSection from './Components/ReportsSection.vue';
import axios from 'axios';

const props = defineProps<{
    running_total: number;
    period: string;
    start_date: string;
    end_date: string;
    period_revenue: number;
    period_orders: number;
    period_units: number;
    earnings_by_period: any[];
    earnings_by_product: any[];
    walletBalance: any;
}>();
const activePeriod = ref(props.period);
const activeTab = ref<'period' | 'product'>('period');
const showCashOutForm = ref(false);
const cashOutAmount = ref('');
const cashOutRequests = ref<any[]>([]);
const availableEarnings = ref(0);
const pendingEarnings = ref(0);
const bankAccounts = ref<any[]>([]);
const selectedBankAccountId = ref('');
const currentWalletBalance = ref(props.walletBalance);

const PERIOD_OPTS = [
    { key: 'today', label: 'Today' },
    { key: '7d', label: '7 Days' },
    { key: '30d', label: '30 Days' },
    { key: '90d', label: '90 Days' },
];

const switchPeriod = (p: string) => {
    activePeriod.value = p;
    router.get('/seller/earnings', { period: p }, { preserveScroll: true });
};

const fmt = (v: number) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 2 }).format(v);
const fmtShort = (v: number) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(v);

const loadCashOutRequests = async () => {
    try {
        const response = await axios.get('/seller/cash-out');
        cashOutRequests.value = response.data.requests;
    } catch (error) {
        console.error('Failed to load cash out requests:', error);
    }
};

const loadAvailableEarnings = async () => {
    try {
        const response = await axios.get('/seller/cash-out/available');
        availableEarnings.value = response.data.available;
        pendingEarnings.value = response.data.pending;
    } catch (error) {
        console.error('Failed to load available earnings:', error);
    }
};

const loadWalletBalance = async () => {
    try {
        const response = await axios.get('/seller/wallet-balance');
        currentWalletBalance.value = response.data.walletBalance;
    } catch (error) {
        console.error('Failed to load wallet balance:', error);
    }
};

const loadBankAccounts = async () => {
    try {
        const response = await axios.get('/user/bank-details');
        bankAccounts.value = response.data.bank_accounts || [];
        // Select default bank account if available
        const defaultAccount = bankAccounts.value.find((acc: any) => acc.is_default);
        if (defaultAccount) {
            selectedBankAccountId.value = defaultAccount.id;
        }
    } catch (error) {
        console.error('Failed to load bank accounts:', error);
    }
};

const submitCashOut = async () => {
    const amount = parseFloat(cashOutAmount.value);
    if (!amount || amount < 10 || amount > 10000) {
        toast.error('Amount must be between $10 and $10,000');
        return;
    }
    if (amount > availableEarnings.value) {
        toast.error(`Insufficient earnings. Available: ${fmt(availableEarnings.value)}`);
        return;
    }
    // if (!selectedBankAccountId.value) {
    //     toast.error('Please select a bank account');
    //     return;
    // }

    try {
        await axios.post('/seller/cash-out', {
            amount,
            // bank_account_id: selectedBankAccountId.value,
        });
        toast.success('Cash out request submitted successfully');
        showCashOutForm.value = false;
        cashOutAmount.value = '';
        loadCashOutRequests();
        loadAvailableEarnings();
        loadWalletBalance();
    } catch (error: any) {
        const message = error.response?.data?.message || error.response?.data?.error || 'Failed to submit cash out request';
        toast.error(message);
    }
};

const cancelCashOut = async (id: number) => {
    if (!confirm('Are you sure you want to cancel this cash out request?')) {
        return;
    }

    try {
        await axios.post(`/seller/cash-out/${id}/cancel`);
        toast.success('Cash out request cancelled successfully');
        loadCashOutRequests();
        loadAvailableEarnings();
    } catch (error: any) {
        const message = error.response?.data?.message || error.response?.data?.error || 'Failed to cancel request';
        toast.error(message);
    }
};

// Load data on mount
loadCashOutRequests();
loadAvailableEarnings();
loadBankAccounts();

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'processing':
            return 'bg-blue-100 text-blue-800';
        case 'completed':
            return 'bg-green-100 text-green-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        case 'cancelled':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

// Load data on component mount
onMounted(() => {
    loadCashOutRequests();
    loadAvailableEarnings();
    loadWalletBalance();
});
</script>

<template>
    <div class="page-wrap">
        <Header />
        <main class="main-content">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title"> Earnings</h1>
                    <p class="page-sub">{{ start_date }} → {{ end_date }}</p>
                </div>
                <div class="period-switcher">
                    <button v-for="opt in PERIOD_OPTS" :key="opt.key" class="period-btn"
                        :class="activePeriod === opt.key ? 'period-active' : ''" @click="switchPeriod(opt.key)">{{
                        opt.label }}</button>
                </div>
            </div>

            <!-- Running Total Hero -->
            <div class="hero-card card">
                <div class="hero-label">All-Time Revenue</div>
                <div class="hero-value">{{ fmt(running_total) }}</div>
                <div class="hero-sub">Lifetime earnings from all your products.</div>
            </div>

            <!-- Period Stats -->
            <div class="period-stats">
                <div class="pstat-card card">
                    <!-- <div class="pstat-icon" style="background:rgba(14,165,233,.1)"></div> -->
                    <div class="pstat-info">
                        <div class="pstat-label">Period Revenue</div>
                        <div class="pstat-value">{{ fmtShort(period_revenue) }}</div>
                    </div>
                </div>
                <div class="pstat-card card">
                    <!-- <div class="pstat-icon" style="background:rgba(34,197,94,.1)"></div> -->
                    <div class="pstat-info">
                        <div class="pstat-label">Orders</div>
                        <div class="pstat-value">{{ period_orders }}</div>
                    </div>
                </div>
                <div class="pstat-card card">
                    <!-- <div class="pstat-icon" style="background:rgba(168,85,247,.1)"></div> -->
                    <div class="pstat-info">
                        <div class="pstat-label">Units Sold</div>
                        <div class="pstat-value">{{ period_units }}</div>
                    </div>
                </div>
                <div class="pstat-card card">
                    <!-- <div class="pstat-icon" style="background:rgba(168,85,247,.1)"></div> -->
                    <div class="pstat-info">
                        <div class="pstat-label">Credit</div>
                        <div class="pstat-value">{{ currentWalletBalance }}</div>
                    </div>
                </div>
            </div>

            <!-- Cash Out Section -->
            <div class="card section-card">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold">Cash Out</h2>
                    <button v-if="!showCashOutForm" @click="showCashOutForm = true"
                        class="px-4 py-2 bg-gradient-to-r from-blue-500 to-green-500 text-white text-sm font-bold rounded-lg hover:opacity-90 transition-opacity">
                        Cash Out
                    </button>
                </div>

                <!-- Display earnings when form is closed -->
                <div v-if="!showCashOutForm" class="p-4 bg-gray-50 rounded-lg">
                    <!-- Available Earnings -->
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-gray-500 mb-2">Available for Cash Out</label>
                        <div class="text-lg font-bold text-gray-900">{{ fmt(availableEarnings) }}</div>
                        <div class="text-xs text-gray-500 mt-1">From delivered orders</div>
                    </div>

                    <!-- Pending Earnings -->
                    <div v-if="pendingEarnings > 0" class="p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                        <label class="block text-xs font-bold text-yellow-800 mb-2">Pending Cash Out</label>
                        <div class="text-lg font-bold text-yellow-900">{{ fmt(pendingEarnings) }}</div>
                        <div class="text-xs text-yellow-700 mt-1">From orders not yet delivered. Funds become available once buyer takes the goods.</div>
                    </div>
                </div>

                <div v-if="showCashOutForm" class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <!-- Available Earnings -->
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-gray-500 mb-2">Available for Cash Out</label>
                        <div class="text-lg font-bold text-gray-900">{{ fmt(availableEarnings) }}</div>
                        <div class="text-xs text-gray-500 mt-1">From delivered orders</div>
                    </div>

                    <!-- Pending Earnings -->
                    <div v-if="pendingEarnings > 0" class="mb-3 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                        <label class="block text-xs font-bold text-yellow-800 mb-2">Pending Cash Out</label>
                        <div class="text-lg font-bold text-yellow-900">{{ fmt(pendingEarnings) }}</div>
                        <div class="text-xs text-yellow-700 mt-1">From orders not yet delivered. Funds become available once buyer takes the goods.</div>
                    </div>
                    <!-- <div class="mb-3">
                        <label class="block text-xs font-bold text-gray-500 mb-2">Bank Account</label>
                        <select v-model="selectedBankAccountId"
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="">Select a bank account</option>
                            <option v-for="account in bankAccounts" :key="account.id" :value="account.id">
                                {{ account.bank_name }} - •••• {{ account.account_number.slice(-4) }}
                            </option>
                        </select>
                    </div> -->
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-gray-500 mb-2">Amount (USD)</label>
                        <input v-model="cashOutAmount" type="number" min="10" max="10000" step="0.01"
                            placeholder="Enter amount"
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" />
                    </div>
                    <div class="flex gap-2">
                        <button @click="submitCashOut"
                            class="flex-1 px-4 py-2 bg-blue-500 text-white text-sm font-bold rounded-lg hover:bg-blue-600 transition-colors">
                            Cash Out
                        </button>
                        <button @click="showCashOutForm = false; cashOutAmount = ''"
                            class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-100 transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>

                <div v-if="cashOutRequests.length > 0" class="mt-4">
                    <h3 class="text-sm font-bold text-gray-500 mb-3">Recent Requests</h3>
                    <div class="space-y-2">
                        <div v-for="request in cashOutRequests" :key="request.id"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="font-semibold text-gray-900">{{ fmt(request.amount) }}</div>
                                <div class="text-xs text-gray-500">{{ formatDate(request.created_at) }}</div>
                                <div v-if="request.rejection_reason" class="text-xs text-red-500 mt-1">
                                    {{ request.rejection_reason }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span :class="getStatusClass(request.status)"
                                    class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
                                </span>
                                <button v-if="request.status === 'pending'" @click="cancelCashOut(request.id)"
                                    class="px-2 py-1 bg-gray-500 text-white text-xs font-semibold rounded hover:bg-gray-600 transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Breakdown Table -->
            <div class="card section-card">
                <!-- Tabs -->
                <div class="tabs-row">
                    <button class="tab-btn" :class="activeTab === 'period' ? 'tab-active' : ''"
                        @click="activeTab = 'period'">
                        By Period
                    </button>
                    <button class="tab-btn" :class="activeTab === 'product' ? 'tab-active' : ''"
                        @click="activeTab = 'product'">
                        By Product
                    </button>
                </div>
                <EarningsBreakdownTable :by-period="earnings_by_period" :by-product="earnings_by_product"
                    :active-tab="activeTab" />
            </div>

            <!-- Reports Section -->
            <ReportsSection />

        </main>
        <Toaster position="top-center" />
    </div>
</template>

<style scoped>
.page-wrap {
    min-height: 100vh;
    background: #f5f7fb;
    color: #0f172a;
}

.main-content {
    max-width: 1100px;
    margin: 0 auto;
    padding: 1.5rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 900;
    color: #0f172a;
    margin: 0;
}

.page-sub {
    font-size: .83rem;
    color: #64748b;
    margin: 3px 0 0;
}

.period-switcher {
    display: flex;
    gap: 6px;
    background: #fff;
    border-radius: 14px;
    padding: 5px;
    border: 1px solid rgba(148, 163, 184, .25);
    box-shadow: 0 2px 12px rgba(2, 6, 23, .06);
    flex-wrap: wrap;
}

.period-btn {
    font-size: .78rem;
    font-weight: 800;
    padding: 5px 14px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    background: transparent;
    color: #64748b;
    transition: all .15s;
}

.period-active {
    background: linear-gradient(135deg, #0ea5e9, #22c55e);
    color: #fff;
    box-shadow: 0 4px 14px rgba(14, 165, 233, .3);
}

.hero-card {
    background: linear-gradient(135deg, rgba(14, 165, 233, .08), rgba(34, 197, 94, .05));
    border: 1px solid rgba(14, 165, 233, .2);
    border-radius: 22px;
    padding: 28px 24px;
    text-align: center;
}

.hero-label {
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #0ea5e9;
    margin-bottom: 8px;
}

.hero-value {
    font-size: 3rem;
    font-weight: 900;
    color: #0f172a;
    line-height: 1;
    margin-bottom: 6px;
}

.hero-sub {
    font-size: .82rem;
    color: #64748b;
}

.period-stats {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
}

.pstat-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, .2);
    background: #fff;
    box-shadow: 0 4px 18px rgba(2, 6, 23, .07);
}

.pstat-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.pstat-label {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #94a3b8;
}

.pstat-value {
    font-size: 1.4rem;
    font-weight: 900;
    color: #0f172a;
    line-height: 1.1;
}

.card {
    background: #fff;
    border-radius: 22px;
    box-shadow: 0 8px 32px rgba(2, 6, 23, .08);
    border: 1px solid rgba(148, 163, 184, .2);
}

.section-card {
    padding: 20px 22px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.tabs-row {
    display: flex;
    gap: 8px;
    border-bottom: 1px solid rgba(148, 163, 184, .15);
    padding-bottom: 12px;
}

.tab-btn {
    font-size: .8rem;
    font-weight: 800;
    padding: 6px 16px;
    border-radius: 12px;
    border: 1.5px solid transparent;
    cursor: pointer;
    background: transparent;
    color: #64748b;
    transition: all .15s;
}

.tab-active {
    background: rgba(14, 165, 233, .08);
    border-color: rgba(14, 165, 233, .25);
    color: #0284c7;
}
</style>
