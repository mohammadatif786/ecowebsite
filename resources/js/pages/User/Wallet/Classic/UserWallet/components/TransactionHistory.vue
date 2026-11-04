<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import {
    Wallet, Download, Search, X, Inbox, Copy, Check
} from 'lucide-vue-next';

const props = defineProps<{
    transactions: any[];
    sentRequests: any[];
    subscriptions: any[];
    formatCurrency: (amount: number) => string;
    user?: any;
    bankWithdrawals?: any[];
}>();

const emit = defineEmits<{
    sendRequestMoney: [request: any];
    cancelWithdrawal: [withdrawalId: number];
}>();

// --- State ---
const state = reactive({
    tab: 'send',
    q: '',
    status: '',
    range: '30',
    minAmt: '',
    maxAmt: ''
});

const drawerOpen = ref(false);
const selectedTx = ref<any>(null);
const copiedId = ref(false);

// Money request functionality
const selectedRequest = ref<any>(null);

// --- Helpers ---
const formatDate = (d: string | Date) => {
    const date = new Date(d);
    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const shortId = (id: string) => {
    if (!id) return '';
    const s = String(id);
    return s.length <= 10 ? s : '…' + s.slice(-4);
};

// --- Data Normalization ---
const allTransactions = computed(() => {
    const list: any[] = [];

    //Send Money (from props.transactions)
    props.transactions.forEach(tx => {

        let fromUser = { name: 'Unknown', handle: '' };
        let toUser = { name: 'Unknown', handle: '' };

        if (tx.isPositive) {
            // Received
            fromUser = {
                name: tx.sender?.name || props.user?.name || 'Unknown Sender',
                handle: tx.sender?.linkup_id || ''
            };
            toUser = {
                name: props.user?.name || 'You',
                handle: props.user?.tag || '@you'
            };
        } else {
            // Sent
            fromUser = {
                name: props.user?.name || 'You',
                handle: props.user?.tag || '@you'
            };
            toUser = {
                name: tx.recipient?.name || props.user?.name || 'Unknown Recipient',
                handle: tx.recipient?.linkup_id || props.user?.tag || '@you'
            };
        }

        list.push({
            id: tx.id || tx.transaction || `TX-${Math.random()}`,
            kind: 'send',
            from: fromUser,
            to: toUser,
            amount: Number(tx.amount || 0),
            status: (tx.status || 'success').toLowerCase(),
            date: new Date(tx.createdAt || tx.date || Date.now()),
            note: tx.note || tx.type || '',
            original: tx,
            runningBalance: tx.runningBalance,
            isPositive: tx.isPositive
        });
    });

    //Requests (from props.sentRequests)
    props.sentRequests.forEach(req => {
        list.push({
            id: req.id || `REQ-${Math.random()}`,
            kind: 'request',
            from: {
                name: req.requester?.name || 'Unknown',
                handle: req.requester?.linkup_id || ''
            },
            to: {
                name: req.recipient?.name || 'Unknown',
                handle: req.recipient?.linkup_id || ''
            },
            amount: Number(req.amount || 0),
            status: (req.status || 'pending').toLowerCase(),
            date: new Date(req.created_at || Date.now()),
            note: req.note || '',
            original: req
        });
    });

    //Ledger (from props.subscriptions)
    props.subscriptions.forEach(sub => {
        list.push({
            id: sub.id || `LED-${Math.random()}`,
            kind: 'ledger',
            ledgerType: sub.type || 'wallet',
            from: { name: 'System', handle: '@system' },
            to: { name: props.user?.name || 'You', handle: props.user?.tag || '@you' },
            amount: Number(sub.stripe_price || sub.amount || 0),
            status: (sub.stripe_status || sub.status || 'complete').toLowerCase(),
            date: new Date(sub.created_at || Date.now()),
            note: sub.type || '',
            original: sub
        });
    });

    //Withdrawals (from props.bankWithdrawals)
    props.bankWithdrawals?.forEach(withdrawal => {
        list.push({
            id: withdrawal.id || `WD-${Math.random()}`,
            kind: 'withdrawal',
            from: { name: props.user?.name || 'You', handle: props.user?.tag || '@you' },
            to: { name: withdrawal.bank_name || 'Bank', handle: 'Bank Transfer' },
            amount: Number(withdrawal.amount || 0),
            status: (withdrawal.status || 'pending').toLowerCase(),
            date: new Date(withdrawal.created_at || Date.now()),
            note: `Bank Transfer to ${withdrawal.bank_name || 'Bank'}`,
            original: withdrawal
        });
    });

    return list.sort((a, b) => b.date.getTime() - a.date.getTime());
});

// --- Filtering ---
const filteredTransactions = computed(() => {
    let items = allTransactions.value;

    // Tab
    if (state.tab !== 'all') {
        if (state.tab === 'deposits' || state.tab === 'received') {
            items = items.filter(t => t.kind === 'send' && t.isPositive);
        } else if (state.tab === 'sent') {
            items = items.filter(t => t.kind === 'send' && !t.isPositive);
        } else if (state.tab === 'requested') {
            items = items.filter(t => t.kind === 'request');
        } else if (state.tab === 'wallet-coins') {
            items = items.filter(t => t.kind === 'ledger');
        } else if (state.tab === 'withdrawals') {
            items = items.filter(t => t.kind === 'withdrawal');
        }
    }

    // Range
    if (state.range !== 'all') {
        const days = Number(state.range);
        const cutoff = new Date();
        cutoff.setDate(cutoff.getDate() - days);
        items = items.filter(t => t.date >= cutoff);
    }

    // Status
    if (state.status) {
        items = items.filter(t => t.status === state.status);
    }

    // Amount
    if (state.minAmt) {
        items = items.filter(t => t.amount >= Number(state.minAmt));
    }
    if (state.maxAmt) {
        items = items.filter(t => t.amount <= Number(state.maxAmt));
    }

    // Query
    if (state.q) {
        const q = state.q.toLowerCase();
        items = items.filter(t => {
            const hay = [
                t.id,
                t.kind,
                t.status,
                t.ledgerType || '',
                t.from?.name, t.from?.handle,
                t.to?.name, t.to?.handle,
                String(t.amount),
                t.note || ''
            ].join(' ').toLowerCase();
            return hay.includes(q);
        });
    }

    return items;
});

const groupedTransactions = computed(() => {
    const groups: Record<string, any[]> = {};
    const items = filteredTransactions.value;

    items.forEach(tx => {
        const k = tx.date.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
        if (!groups[k]) groups[k] = [];
        groups[k].push(tx);
    });

    return groups;
});



// --- Summary Stats ---
const summary = computed(() => {
    const now = new Date();
    const cutoff30 = new Date();
    cutoff30.setDate(cutoff30.getDate() - 30);

    const sent30 = allTransactions.value
        .filter(t => t.kind === 'send' && t.status === 'success' && t.date >= cutoff30)
        .reduce((sum, t) => sum + t.amount, 0);

    const pendingReq = allTransactions.value
        .filter(t => t.kind === 'request' && t.status === 'pending')
        .length;

    const ledger30 = allTransactions.value
        .filter(t => t.kind === 'ledger' && t.date >= cutoff30)
        .reduce((sum, t) => sum + t.amount, 0);

    return { sent30, pendingReq, ledger30 };
});

const activeChips = computed(() => {
    const chips = [];
    if (state.tab !== 'all') chips.push({ key: 'tab', label: `Tab: ${state.tab === 'wallet-coins' ? 'Wallet & Coins' : (state.tab === 'requested' ? 'Requested' : state.tab.charAt(0).toUpperCase() + state.tab.slice(1))}` });
    if (state.q.trim()) chips.push({ key: 'q', label: `Search: ${state.q.trim()}` });
    if (state.status) chips.push({ key: 'status', label: `Status: ${state.status}` });
    if (state.range !== '30') chips.push({ key: 'range', label: `Range: ${state.range === 'all' ? 'All' : 'Last ' + state.range + 'd'}` });
    if (state.minAmt) chips.push({ key: 'minAmt', label: `Min: ${state.minAmt}` });
    if (state.maxAmt) chips.push({ key: 'maxAmt', label: `Max: ${state.maxAmt}` });
    return chips;
});

const removeChip = (key: string) => {
    if (key === 'tab') state.tab = 'all';
    if (key === 'q') state.q = '';
    if (key === 'status') state.status = '';
    if (key === 'range') state.range = '30';
    if (key === 'minAmt') state.minAmt = '';
    if (key === 'maxAmt') state.maxAmt = '';
};

const resetFilters = () => {
    state.tab = 'all';
    state.q = '';
    state.status = '';
    state.range = '30';
    state.minAmt = '';
    state.maxAmt = '';
};



const openDrawer = (tx: any) => {
    selectedTx.value = tx;
    drawerOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeDrawer = () => {
    drawerOpen.value = false;
    document.body.style.overflow = '';
    setTimeout(() => { selectedTx.value = null; }, 300);
};

const copyId = async () => {
    if (!selectedTx.value?.id) return;
    try {
        await navigator.clipboard.writeText(selectedTx.value.id);
        copiedId.value = true;
        setTimeout(() => copiedId.value = false, 1500);
    } catch (e) {
        console.error('Copy failed', e);
    }
};

const exportCSV = () => {
    const rows = filteredTransactions.value.map(tx => ({
        kind: tx.kind,
        ledger_type: tx.ledgerType || '',
        status: tx.status,
        date: formatDate(tx.date),
        amount: tx.amount,
        from_name: tx.from?.name || '',
        from_handle: tx.from?.handle || '',
        to_name: tx.to?.name || '',
        to_handle: tx.to?.handle || '',
        id: tx.id,
        note: tx.note || '',
        running_balance: tx.runningBalance || ''
    }));

    if (rows.length === 0) {
        alert('No transactions to export');
        return;
    }

    const header = Object.keys(rows[0]);
    const csv = [
        header.join(','),
        ...rows.map(r => header.map(k => {
            const v = String((r as any)[k] ?? '');
            return v.includes(',') || v.includes('"') || v.includes('\n')
                ? `"${v.replaceAll('"', '""')}"`
                : v;
        }).join(','))
    ].join('\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    const tabName = state.tab === 'wallet-coins' ? 'wallet-coins' : state.tab;
    a.download = `linkup-${tabName}-transactions-${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
};

// Money request functions
const sendModal = (request: any) => {
    selectedRequest.value = request;
    emit('sendRequestMoney', request);
};

const rejectRequest = async (requestId: number) => {
    try {
        await axios.get(route('frontend.user.reject.money.request', { id: requestId }));
        // Refresh the page or emit event to refresh data
        window.location.reload();
    } catch (error: any) {
        console.error('Reject money request error:', error);
        alert('Failed to reject money request');
    }
};

const cancelRequest = async (requestId: number) => {
    try {
        await axios.get(route('frontend.user.cancel.money.request', { id: requestId }));
        // Refresh the page or emit event to refresh data
        window.location.reload();
    } catch (error: any) {
        console.error('Cancel money request error:', error);
        alert('Failed to cancel money request');
    }
};

const cancelWithdrawal = async (withdrawalId: number) => {
    try {
        emit('cancelWithdrawal', withdrawalId);
    } catch (error: any) {
        console.error('Cancel withdrawal error:', error);
        alert('Failed to cancel withdrawal');
    }
};

// --- UI Helpers ---
const statusMeta = (s: string) => {
    if (s === 'success') return { label: 'Success', cls: 'border-emerald-200 text-emerald-700 bg-emerald-50', dot: 'bg-emerald-500' };
    if (s === 'pending') return { label: 'Pending', cls: 'border-amber-200 text-amber-700 bg-amber-50', dot: 'bg-amber-500' };
    if (s === 'processing') return { label: 'Processing', cls: 'border-blue-200 text-blue-700 bg-blue-50', dot: 'bg-blue-500' };
    if (s === 'completed') return { label: 'Completed', cls: 'border-emerald-200 text-emerald-700 bg-emerald-50', dot: 'bg-emerald-500' };
    if (s === 'failed') return { label: 'Failed', cls: 'border-rose-200 text-rose-700 bg-rose-50', dot: 'bg-rose-500' };
    if (s === 'rejected') return { label: 'Rejected', cls: 'border-orange-200 text-orange-700 bg-orange-50', dot: 'bg-orange-500' };
    if (s === 'canceled') return { label: 'Canceled', cls: 'border-slate-200 text-slate-700 bg-slate-50', dot: 'bg-slate-500' };
    if (s === 'cancelled') return { label: 'Cancelled', cls: 'border-slate-200 text-slate-700 bg-slate-50', dot: 'bg-slate-500' };
    if (s === 'complete') return { label: 'Complete', cls: 'border-sky-200 text-sky-700 bg-sky-50', dot: 'bg-sky-500' };
    return { label: s, cls: 'border-slate-200 text-slate-700 bg-slate-50', dot: 'bg-slate-500' };
};

const counterpartyLabel = (tx: any) => {
    if (tx.kind === 'ledger') {
        return { title: (tx.ledgerType || 'wallet').toString(), subtitle: 'Wallet and Coin History' };
    }
    if (tx.kind === 'withdrawal') {
        const subtitle = `Withdrawal to ${tx.to?.name || 'Bank'}`;
        const note = tx.original?.failure_reason ? ` - ${tx.original.failure_reason}` : '';
        return { title: tx.to?.name || 'Bank Transfer', subtitle: subtitle + note };
    }
    const whoLeft = tx.kind === 'send' ? tx.to : tx.from;
    const whoRight = tx.kind === 'send' ? 'Sent to' : (tx.from.handle === '@you' ? 'Requested from' : 'Requested by');
    return { title: whoLeft.name, subtitle: `${whoRight} • ${whoLeft.handle}` };
};

const signedAmount = (tx: any) => {
    if (tx.kind === 'request') return { sign: '+', tone: 'text-emerald-700' };
    if (tx.kind === 'withdrawal') return { sign: '−', tone: 'text-slate-900' };
    if (tx.isPositive) return { sign: '+', tone: 'text-emerald-700' };
    return { sign: '−', tone: 'text-slate-900' };
};

// --- Empty State Helper ---
const getEmptyStateMessage = () => {
    const hasFilters = state.q || state.status || state.range !== '30' || state.minAmt || state.maxAmt || state.tab !== 'all';

    if (!hasFilters) {
        switch (state.tab) {
            case 'sent':
                return { title: 'No sent transactions', subtitle: 'You haven\'t sent any money yet' };
            case 'deposits':
            case 'received':
                return { title: 'No received money', subtitle: 'You haven\'t received any money yet' };
            case 'requested':
                return { title: 'No money requests', subtitle: 'You don\'t have any money requests' };
            case 'withdrawals':
                return { title: 'No withdrawals', subtitle: 'You haven\'t made any bank withdrawals yet' };
            case 'wallet-coins':
                return { title: 'No wallet activity', subtitle: 'No wallet or coin transactions yet' };
            default:
                return { title: 'No transactions', subtitle: 'Start by sending money or adding funds to your wallet' };
        }
    }

    return { title: 'No results', subtitle: 'Try clearing filters or adjusting the date range' };
};
</script>

<template>
    <div class="transaction-history-container">
        <!-- Header -->
        <header class="mb-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-white"
                            style="background:linear-gradient(135deg,#0ea5e9,#38bdf8)">
                            <Wallet class="w-5 h-5" />
                        </div>
                        <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Wallet Activity</h1>
                    </div>
                    <p class="soft mt-1 text-sm">Send, Requests, and Wallet/Coin ledger history.</p>
                </div>

                <div class="hidden sm:flex items-center gap-2">
                    <button @click="exportCSV"
                        class="px-3 py-2 rounded-xl border text-sm bg-white hover:bg-slate-50 transition-colors"
                        style="border-color:rgba(148, 163, 184, .35)">
                        <span class="inline-flex items-center gap-2">
                            <Download class="w-4 h-4" /> Export CSV
                        </span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Summary strip -->
        <section class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="card p-4">
                <div class="soft text-xs">Sent (Last 30d)</div>
                <div class="text-lg font-semibold mt-1 text-slate-900">{{ props.formatCurrency(summary.sent30) }}</div>
            </div>
            <div class="card p-4">
                <div class="soft text-xs">Requests Pending</div>
                <div class="text-lg font-semibold mt-1 text-slate-900">{{ summary.pendingReq }}</div>
            </div>
            <div class="card p-4">
                <div class="soft text-xs">Ledger (Last 30d)</div>
                <div class="text-lg font-semibold mt-1 text-slate-900">{{ props.formatCurrency(summary.ledger30) }}
                </div>
            </div>
            <div class="card p-4">
                <div class="soft text-xs">Total Shown</div>
                <div class="text-lg font-semibold mt-1 text-slate-900">{{ filteredTransactions.length }}</div>
            </div>
        </section>

        <!-- Tabs + Filters + Results -->
        <section class="card p-4 sm:p-5">
            <!-- Tabs -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="inline-flex rounded-2xl bg-slate-100 p-1 w-full sm:w-auto">
                    <button v-for="t in ['deposits', 'sent', 'received', 'requested', 'withdrawals', 'wallet-coins', 'all']" :key="t" @click="state.tab = t"
                        class="tabBtn flex-1 sm:flex-none px-4 py-2 rounded-2xl text-sm font-medium capitalize transition-all"
                        :class="state.tab === t ? 'bg-white shadow-sm text-slate-900' : 'text-slate-600 hover:text-slate-900'">
                        {{ t === 'wallet-coins' ? 'Wallet & Coins' : (t === 'requested' ? 'Requested' : t) }}
                    </button>
                </div>

                <div class="flex items-center gap-2 sm:hidden">
                    <button @click="exportCSV"
                        class="px-3 py-2 rounded-xl border text-sm bg-white hover:bg-slate-50 w-full"
                        style="border-color:rgba(148, 163, 184, .35)">
                        <span class="inline-flex items-center justify-center gap-2 w-full">
                            <Download class="w-4 h-4" /> Export CSV
                        </span>
                    </button>
                </div>
            </div>

            <!-- Controls -->
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-12 gap-3">
                <!-- Search -->
                <div class="sm:col-span-5">
                    <label class="soft text-xs">Search</label>
                    <div class="mt-1 flex items-center gap-2 px-3 py-2 rounded-2xl bg-white border"
                        style="border-color:rgba(148, 163, 184, .35)">
                        <Search class="w-4 h-4 soft" />
                        <input v-model="state.q" class="w-full outline-none text-sm bg-transparent"
                            placeholder="e.g. coin payment, @user..." />
                        <button v-if="state.q" @click="state.q = ''" class="soft hover:text-slate-700">
                            <X class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- Status -->
                <div class="sm:col-span-3">
                    <label class="soft text-xs">Status</label>
                    <select v-model="state.status"
                        class="mt-1 w-full px-3 py-2 rounded-2xl bg-white border text-sm outline-none"
                        style="border-color:rgba(148, 163, 184, .35)">
                        <option value="">All</option>
                        <option value="success">Success</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="canceled">Canceled</option>
                        <option value="complete">Complete</option>
                    </select>
                </div>

                <!-- Range -->
                <div class="sm:col-span-2">
                    <label class="soft text-xs">Range</label>
                    <select v-model="state.range"
                        class="mt-1 w-full px-3 py-2 rounded-2xl bg-white border text-sm outline-none"
                        style="border-color:rgba(148, 163, 184, .35)">
                        <option value="7">Last 7 days</option>
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 90 days</option>
                        <option value="365">Last 365 days</option>
                        <option value="all">All time</option>
                    </select>
                </div>

                <!-- Amount -->
                <div class="sm:col-span-2 grid grid-cols-2 gap-2">
                    <div>
                        <label class="soft text-xs">Min</label>
                        <input v-model="state.minAmt" type="number"
                            class="mt-1 w-full px-3 py-2 rounded-2xl bg-white border text-sm outline-none"
                            style="border-color:rgba(148, 163, 184, .35)" placeholder="0" />
                    </div>
                    <div>
                        <label class="soft text-xs">Max</label>
                        <input v-model="state.maxAmt" type="number"
                            class="mt-1 w-full px-3 py-2 rounded-2xl bg-white border text-sm outline-none"
                            style="border-color:rgba(148, 163, 184, .35)" placeholder="∞" />
                    </div>
                </div>
            </div>

            <!-- Active chips -->
            <div class="mt-3 flex flex-wrap gap-2" v-if="activeChips.length">
                <span v-for="chip in activeChips" :key="chip.key" class="chip">
                    {{ chip.label }}
                    <button @click="removeChip(chip.key)">
                        <X class="w-3.5 h-3.5" />
                    </button>
                </span>
            </div>

            <!-- Results -->
            <div class="mt-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="soft text-xs">Showing <span class="font-medium text-slate-900">{{
                        filteredTransactions.length }}</span> items</div>
                    <button @click="resetFilters"
                        class="px-3 py-2 rounded-xl border text-sm bg-white hover:bg-slate-50 transition-colors"
                        style="border-color:rgba(148, 163, 184, .35)">
                        Reset
                    </button>
                </div>

                <div v-if="Object.keys(groupedTransactions).length === 0" class="mt-6 card p-6 text-center">
                    <div class="mx-auto w-12 h-12 rounded-2xl flex items-center justify-center mb-3"
                        style="background:linear-gradient(135deg,rgba(14,165,233,.12),rgba(34,197,94,.12))">
                        <Inbox class="w-6 h-6 text-linkup-blue" />
                    </div>
                    <div class="font-semibold text-slate-900">
                        {{ getEmptyStateMessage().title }}
                    </div>
                    <div class="soft text-sm mt-1">{{ getEmptyStateMessage().subtitle }}</div>
                </div>

                <div v-else class="mt-3 space-y-5">
                    <section v-for="(items, dateGroup) in groupedTransactions" :key="dateGroup">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-slate-800">{{ dateGroup }}</h3>
                            <div class="soft text-xs">{{ items.length }} item{{ items.length === 1 ? '' : 's' }}</div>
                        </div>

                        <div class="hidden sm:block overflow-hidden rounded-2xl border"
                            style="border-color:rgba(148, 163, 184, .35)">
                            <table class="w-full text-sm">
                                <thead class="bg-white border-b" style="border-color:rgba(148, 163, 184, .35)">
                                    <tr class="text-xs soft text-left">
                                        <th class="py-3 px-4 font-medium">Type / Counterparty</th>
                                        <th class="py-3 px-4 font-medium">Status</th>
                                        <th class="py-3 px-4 font-medium">Date</th>
                                        <th class="text-right py-3 px-4 font-medium">Amount</th>
                                        <th class="text-right py-3 px-4 font-medium">Ref</th>
                                        <th class="text-right py-3 px-4 font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    <tr v-for="tx in items" :key="tx.id" @click="openDrawer(tx)"
                                        class="border-b last:border-b-0 hover:bg-slate-50 cursor-pointer transition-colors"
                                        style="border-color:rgba(148, 163, 184, .35)">
                                        <td class="py-3 px-4">
                                            <div class="font-medium capitalize text-slate-900">{{
                                                counterpartyLabel(tx).title }}</div>
                                            <div class="soft text-xs">{{ counterpartyLabel(tx).subtitle }}</div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span
                                                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-medium border"
                                                :class="statusMeta(tx.status).cls">
                                                <span class="w-1.5 h-1.5 rounded-full"
                                                    :class="statusMeta(tx.status).dot"></span>
                                                {{ statusMeta(tx.status).label }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 soft text-sm">{{ formatDate(tx.date) }}</td>
                                        <td class="py-3 px-4 text-right font-semibold" :class="signedAmount(tx).tone">
                                            {{ signedAmount(tx).sign }}{{ props.formatCurrency(tx.amount) }}
                                        </td>
                                        <td class="py-3 px-4 text-right soft text-xs">{{ shortId(tx.id) }}</td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2" v-if="tx.kind === 'request' && tx.status === 'pending'">
                                                <template v-if="tx.original?.recipient_id === props.user?.id">
                                                    <!-- Received request - can accept/reject -->
                                                    <button @click.stop="sendModal(tx.original)"
                                                        class="px-2 py-1 bg-green-500 text-white text-xs font-medium rounded hover:bg-green-600 transition-colors">
                                                        Send
                                                    </button>
                                                    <button @click.stop="rejectRequest(tx.original.id)"
                                                        class="px-2 py-1 bg-red-500 text-white text-xs font-medium rounded hover:bg-red-600 transition-colors">
                                                        Reject
                                                    </button>
                                                </template>
                                                <template v-else>
                                                    <!-- Sent request - can cancel -->
                                                    <button @click.stop="cancelRequest(tx.original.id)"
                                                        class="px-2 py-1 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition-colors">
                                                        Cancel
                                                    </button>
                                                </template>
                                            </div>
                                            <div class="flex items-center justify-end gap-2" v-if="tx.kind === 'withdrawal' && tx.status === 'pending'">
                                                <!-- Pending withdrawal - can cancel -->
                                                <button @click.stop="cancelWithdrawal(tx.original.id)"
                                                    class="px-2 py-1 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition-colors">
                                                    Cancel
                                                </button>
                                            </div>
                                            <div v-if="tx.kind === 'withdrawal' && tx.original?.failure_reason" class="text-xs text-gray-400">
                                                {{ tx.original.failure_reason }}
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile List -->
                        <div class="sm:hidden space-y-3">
                            <button v-for="tx in items" :key="tx.id" @click="openDrawer(tx)"
                                class="w-full text-left card p-4 hover:bg-slate-50 transition-colors">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-semibold capitalize text-slate-900">{{
                                            counterpartyLabel(tx).title }}</div>
                                        <div class="soft text-xs">{{ counterpartyLabel(tx).subtitle }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold" :class="signedAmount(tx).tone">
                                            {{ signedAmount(tx).sign }}{{ props.formatCurrency(tx.amount) }}
                                        </div>
                                        <div class="soft text-xs mt-1">{{ formatDate(tx.date) }}</div>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center justify-between gap-2">
                                    <span
                                        class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-medium border"
                                        :class="statusMeta(tx.status).cls">
                                        <span class="w-1.5 h-1.5 rounded-full"
                                            :class="statusMeta(tx.status).dot"></span>
                                        {{ statusMeta(tx.status).label }}
                                    </span>
                                    <div class="flex items-center gap-2" v-if="tx.kind === 'request' && tx.status === 'pending'">
                                        <template v-if="tx.original?.recipient_id === props.user?.id">
                                            <!-- Received request - can accept/reject -->
                                            <button @click.stop="sendModal(tx.original)"
                                                class="px-2 py-1 bg-green-500 text-white text-xs font-medium rounded hover:bg-green-600 transition-colors">
                                                Send
                                            </button>
                                            <button @click.stop="rejectRequest(tx.original.id)"
                                                class="px-2 py-1 bg-red-500 text-white text-xs font-medium rounded hover:bg-red-600 transition-colors">
                                                Reject
                                            </button>
                                        </template>
                                        <template v-else>
                                            <!-- Sent request - can cancel -->
                                            <button @click.stop="cancelRequest(tx.original.id)"
                                                class="px-2 py-1 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition-colors">
                                                Cancel
                                            </button>
                                        </template>
                                    </div>
                                    <div class="flex items-center gap-2" v-if="tx.kind === 'withdrawal' && tx.status === 'pending'">
                                        <!-- Pending withdrawal - can cancel -->
                                        <button @click.stop="cancelWithdrawal(tx.original.id)"
                                            class="px-2 py-1 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition-colors">
                                            Cancel
                                        </button>
                                    </div>
                                    <div v-if="tx.kind === 'withdrawal' && tx.original?.failure_reason" class="text-xs text-gray-400">
                                        {{ tx.original.failure_reason }}
                                    </div>
                                    <span v-else class="soft text-xs">Ref {{ shortId(tx.id) }}</span>
                                </div>
                            </button>
                        </div>
                    </section>
                </div>


            </div>
        </section>

        <!-- Drawer -->
        <teleport to="body">
            <div v-if="drawerOpen" class="fixed inset-0 z-[100]">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeDrawer">
                </div>

                <div class="absolute right-0 top-0 h-full w-full sm:w-[420px] bg-white shadow-2xl flex flex-col transition-transform duration-300 transform translate-x-0"
                    style="border-left:1px solid rgba(148, 163, 184, .35)">

                    <div class="p-4 sm:p-5 border-b flex items-start justify-between gap-3"
                        style="border-color:rgba(148, 163, 184, .35)">
                        <div>
                            <div class="soft text-xs">Transaction</div>
                            <div class="text-base font-semibold mt-1 text-slate-900">
                                {{ selectedTx?.kind === 'send' ? 'Send Money' : (selectedTx?.kind === 'request' ?
                                    'Request Money' : 'Wallet & Coin History') }}
                            </div>
                            <div class="soft text-xs mt-1">
                                {{ selectedTx?.kind === 'ledger' ? (selectedTx?.ledgerType || 'wallet') :
                                    (counterpartyLabel(selectedTx).subtitle) }}
                            </div>
                        </div>
                        <button @click="closeDrawer" class="p-2 rounded-xl hover:bg-slate-100 transition-colors">
                            <X class="w-5 h-5 text-slate-500" />
                        </button>
                    </div>

                    <div class="p-4 sm:p-5 overflow-y-auto flex-1" v-if="selectedTx">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium border"
                            :class="statusMeta(selectedTx.status).cls">
                            <span class="w-1.5 h-1.5 rounded-full" :class="statusMeta(selectedTx.status).dot"></span>
                            {{ statusMeta(selectedTx.status).label }}
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="card p-3">
                                <div class="soft text-xs">Amount</div>
                                <div class="font-semibold mt-1 text-slate-900">
                                    {{ signedAmount(selectedTx).sign }}{{ props.formatCurrency(selectedTx.amount) }}
                                </div>
                            </div>
                            <div class="card p-3">
                                <div class="soft text-xs">Date</div>
                                <div class="font-semibold mt-1 text-slate-900">{{ formatDate(selectedTx.date) }}</div>
                            </div>
                        </div>

                        <div class="mt-4 card p-4">
                            <div class="text-sm font-semibold text-slate-900">Key Info</div>
                            <div class="mt-3 grid grid-cols-1 gap-3 text-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="soft">Type</div>
                                    <div class="text-right font-medium text-slate-900 capitalize">
                                        {{ selectedTx.kind === 'ledger' ? (selectedTx.ledgerType || 'ledger') :
                                            selectedTx.kind }}
                                    </div>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <div class="soft">From</div>
                                    <div class="text-right font-medium text-slate-900">
                                        {{ selectedTx.from?.name || '—' }} <span class="soft text-xs">({{
                                            selectedTx.from?.handle }})</span>
                                    </div>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <div class="soft">To</div>
                                    <div class="text-right font-medium text-slate-900">
                                        {{ selectedTx.to?.name || '—' }} <span class="soft text-xs">({{
                                            selectedTx.to?.handle }})</span>
                                    </div>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <div class="soft">Reference</div>
                                    <div class="text-right font-medium flex items-center gap-2">
                                        <span class="break-all text-slate-900">{{ selectedTx.id }}</span>
                                        <button @click="copyId"
                                            class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-lg border hover:bg-slate-50 transition-colors"
                                            style="border-color:rgba(148, 163, 184, .35)">
                                            <component :is="copiedId ? Check : Copy" class="w-3 h-3" />
                                            {{ copiedId ? 'Copied' : 'Copy' }}
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <div class="soft">Note</div>
                                    <div class="text-right font-medium text-slate-900">{{ selectedTx.note || '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <button
                                class="px-3 py-2 rounded-2xl text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:shadow-sky-500/30 transition-all"
                                style="background:linear-gradient(135deg,#0ea5e9,#38bdf8)">
                                {{ selectedTx.kind === 'send' ? 'Repeat Send' : (selectedTx.kind === 'request' ?
                                    (selectedTx.status === 'pending' ? 'Send Reminder' : 'Repeat Request') : 'View Wallet')
                                }}
                            </button>
                            <button @click="closeDrawer"
                                class="px-3 py-2 rounded-2xl text-sm font-medium border hover:bg-slate-50 transition-colors"
                                style="border-color:rgba(148, 163, 184, .35)">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </teleport>
    </div>
</template>

<style scoped>
.card {
    background: #ffffff;
    border: 1px solid rgba(148, 163, 184, .35);
    border-radius: 22px;
    box-shadow: 0 12px 26px rgba(2, 6, 23, .08);
}

.soft {
    color: #64748b;
}

.chip {
    border: 1px solid rgba(148, 163, 184, .35);
    background: #fff;
    border-radius: 999px;
    padding: .25rem .6rem;
    font-size: .75rem;
    display: inline-flex;
    gap: .35rem;
    align-items: center;
    color: #0f172a;
}

.chip button {
    opacity: .7;
    display: flex;
    align-items: center;
}

.chip button:hover {
    opacity: 1;
}

/* Custom scrollbar for table container if needed */
.overflow-auto::-webkit-scrollbar {
    height: 6px;
    width: 6px;
}

.overflow-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}
</style>
