<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';

const showReportsSection = ref(false);
const reportsTab = ref<'sales' | 'cash-out' | 'wallet-funding'>('sales');
const salesReport = ref<any[]>([]);
const cashOutReport = ref<any[]>([]);
const walletFundingReport = ref<any[]>([]);
const reportStartDate = ref('');
const reportEndDate = ref('');

// Auto-load reports when section is shown
const toggleReportsSection = () => {
    showReportsSection.value = !showReportsSection.value;
    if (showReportsSection.value) {
        loadReports();
    }
};

const fmt = (v: number) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 2 }).format(v);

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
        case 'shipped':
            return 'bg-purple-100 text-purple-800';
        case 'delivered':
            return 'bg-indigo-100 text-indigo-800';
        case 'received_buyer':
            return 'bg-teal-100 text-teal-800';
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

const loadSalesReport = async () => {
    try {
        const params: any = {};
        if (reportStartDate.value) params.start_date = reportStartDate.value;
        if (reportEndDate.value) params.end_date = reportEndDate.value;

        const response = await axios.get('/seller/reports/sales', { params });
        salesReport.value = response.data.sales_report;
    } catch (error) {
        console.error('Failed to load sales report:', error);
    }
};

const loadCashOutReport = async () => {
    try {
        const response = await axios.get('/seller/reports/cash-out');
        cashOutReport.value = response.data.cash_out_report;
    } catch (error) {
        console.error('Failed to load cash-out report:', error);
    }
};

const loadWalletFundingReport = async () => {
    try {
        const response = await axios.get('/seller/reports/wallet-funding');
        walletFundingReport.value = response.data.wallet_funding_report;
    } catch (error) {
        console.error('Failed to load wallet funding report:', error);
    }
};

const loadReports = () => {
    if (reportsTab.value === 'sales') {
        loadSalesReport();
    } else if (reportsTab.value === 'cash-out') {
        loadCashOutReport();
    } else if (reportsTab.value === 'wallet-funding') {
        loadWalletFundingReport();
    }
};

const viewInvoice = async (orderId: number) => {
    try {
        const response = await axios.get(`/seller/reports/invoice/${orderId}`, {
            responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `invoice-${orderId}.html`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        toast.success('Invoice downloaded successfully');
    } catch (error: any) {
        const message = error.response?.data?.error || 'Failed to download invoice';
        toast.error(message);
    }
};
</script>

<template>
    <div class="card section-card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold">Reports</h2>
            <button @click="toggleReportsSection"
                class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-200 transition-colors">
                {{ showReportsSection ? 'Hide' : 'Show' }}
            </button>
        </div>

        <div v-if="showReportsSection">
            <!-- Report Tabs -->
            <div class="tabs-row mb-4">
                <button class="tab-btn" :class="reportsTab === 'sales' ? 'tab-active' : ''"
                    @click="reportsTab = 'sales'; loadReports()">
                    Sales Report
                </button>
                <button class="tab-btn" :class="reportsTab === 'cash-out' ? 'tab-active' : ''"
                    @click="reportsTab = 'cash-out'; loadReports()">
                    Cash-Out Report
                </button>
                <button class="tab-btn" :class="reportsTab === 'wallet-funding' ? 'tab-active' : ''"
                    @click="reportsTab = 'wallet-funding'; loadReports()">
                    Wallet Funding
                </button>
            </div>

            <!-- Date Filter for Sales Report -->
            <div v-if="reportsTab === 'sales'" class="mb-4 flex gap-2">
                <input v-model="reportStartDate" type="date"
                    class="p-2 border border-gray-300 rounded-lg text-sm">
                <input v-model="reportEndDate" type="date"
                    class="p-2 border border-gray-300 rounded-lg text-sm">
                <button @click="loadSalesReport"
                    class="px-4 py-2 bg-blue-500 text-white text-sm font-bold rounded-lg hover:bg-blue-600 transition-colors">
                    Filter
                </button>
            </div>

            <!-- Sales Report Table -->
            <div v-if="reportsTab === 'sales'" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Order #</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Product</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Qty</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Price</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Total</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Buyer</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sale in salesReport" :key="sale.order_id" class="border-b">
                            <td class="px-4 py-3">{{ sale.order_number }}</td>
                            <td class="px-4 py-3">{{ formatDate(sale.purchase_date) }}</td>
                            <td class="px-4 py-3">{{ sale.product_name }}</td>
                            <td class="px-4 py-3">{{ sale.quantity }}</td>
                            <td class="px-4 py-3">{{ fmt(sale.sale_price) }}</td>
                            <td class="px-4 py-3">{{ fmt(sale.total_amount) }}</td>
                            <td class="px-4 py-3">
                                <div>{{ sale.buyer_name }}</div>
                                <div class="text-xs text-gray-500">{{ sale.buyer_email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="getStatusClass(sale.order_status)"
                                    class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ sale.order_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button @click="viewInvoice(sale.order_id)"
                                    class="text-blue-500 hover:text-blue-700 text-sm font-semibold">
                                    Download
                                </button>
                            </td>
                        </tr>
                        <tr v-if="salesReport.length === 0">
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                No sales data found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Cash-Out Report Table -->
            <div v-if="reportsTab === 'cash-out'" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Transaction ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Amount</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Date Requested</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Date Processed</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Destination</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="cashOut in cashOutReport" :key="cashOut.id" class="border-b">
                            <td class="px-4 py-3 font-mono text-xs">{{ cashOut.transaction_id }}</td>
                            <td class="px-4 py-3 font-semibold">{{ fmt(cashOut.amount) }}</td>
                            <td class="px-4 py-3">{{ formatDate(cashOut.requested_at) }}</td>
                            <td class="px-4 py-3">{{ cashOut.processed_at ? formatDate(cashOut.processed_at) : '-' }}</td>
                            <td class="px-4 py-3">{{ cashOut.destination }}</td>
                            <td class="px-4 py-3">
                                <span :class="getStatusClass(cashOut.status)"
                                    class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ cashOut.status }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="cashOutReport.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                No cash-out history found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Wallet Funding Report Table -->
            <div v-if="reportsTab === 'wallet-funding'" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Reference ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Amount</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Balance Before</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Balance After</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="tx in walletFundingReport" :key="tx.id" class="border-b">
                            <td class="px-4 py-3 font-mono text-xs">{{ tx.reference_id }}</td>
                            <td class="px-4 py-3 capitalize">{{ tx.type }}</td>
                            <td class="px-4 py-3 font-semibold" :class="tx.type === 'credit' ? 'text-green-600' : 'text-red-600'">
                                {{ tx.type === 'credit' ? '+' : '-' }}{{ fmt(tx.amount) }}
                            </td>
                            <td class="px-4 py-3">{{ fmt(tx.balance_before) }}</td>
                            <td class="px-4 py-3">{{ fmt(tx.balance_after) }}</td>
                            <td class="px-4 py-3">{{ formatDate(tx.created_at) }}</td>
                            <td class="px-4 py-3">
                                <span :class="getStatusClass(tx.status)"
                                    class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ tx.status }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="walletFundingReport.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                No wallet funding history found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<style scoped>
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
