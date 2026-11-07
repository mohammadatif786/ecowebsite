<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, reactive, ref, watch } from 'vue';

// Full wallet ledger from the server, 50 rows a page (was: only the latest 300 movements).
const props = defineProps<{
    filteredMovements?: any[];
    walletFilter?: any;
    fmt: (n: number) => string;
    getScale?: () => number;
    summary?: Record<string, number>;
}>();

const rows = ref<any[]>([]);
const types = ref<string[]>([]);
const meta = reactive({ current_page: 1, last_page: 1, total: 0 });
const loading = ref(false);
const error = ref('');
const page = ref(1);
const f = reactive({ search: '', type: '', status: '', from: '', to: '' });

const params = computed(() => {
    const p: Record<string, any> = { page: page.value };
    Object.entries(f).forEach(([k, v]) => { if (v) p[k] = v; });
    return p;
});

const load = async () => {
    loading.value = true; error.value = '';
    try {
        const { data } = await axios.get(route('admin.e-wallet.ledger'), { params: params.value });
        rows.value = data.data; types.value = data.types; Object.assign(meta, data.meta);
    } catch (e: any) {
        error.value = e?.response?.data?.message || 'Could not load the ledger.';
    } finally { loading.value = false; }
};

let t: ReturnType<typeof setTimeout> | undefined;
watch(() => ({ ...f }), () => { page.value = 1; clearTimeout(t); t = setTimeout(load, 300); }, { deep: true });
watch(page, load);
onMounted(load);

const s = computed(() => props.summary || {});
const n = (k: string) => Number(s.value[k] || 0);

const exportCsv = () => {
    const cell = (x: any) => { let v = String(x ?? ''); if (/^[=+\-@\t\r]/.test(v)) v = "'" + v; return '"' + v.replace(/"/g, '""') + '"'; };
    const head = ['ID', 'Date', 'Country', 'Member', 'Other member', 'Type', 'Direction', 'Channel', 'Amount', 'Fee', 'Status'];
    const body = rows.value.map((w) => [w.id, w.date, w.country, w.user, w.counterparty || '', w.type, w.direction, w.channel, w.amount, w.fee, w.status]);
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([[head, ...body].map((r) => r.map(cell).join(',')).join('\n')], { type: 'text/csv;charset=utf-8' }));
    a.download = `wallet-ledger-page-${meta.current_page}.csv`;
    a.click();
    URL.revokeObjectURL(a.href);
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-3xl font-black text-slate-950">Wallet Transactions Ledger</h3>
                <p class="text-slate-500">Every wallet movement: loads, member transfers, payments, coin and live credits, cash-outs and refunds.</p>
            </div>
            <button @click="exportCsv" class="rounded-2xl bg-slate-950 px-5 py-2 font-black text-white">Export This Page (CSV)</button>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Ledger Entries</p><h3 class="text-3xl font-black">{{ meta.total.toLocaleString() }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Wallet Loads (30d)</p><h3 class="text-3xl font-black">{{ fmt(n('loads30d')) }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Member Transfers (30d)</p><h3 class="text-3xl font-black">{{ fmt(n('p2p30d')) }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Payments (30d)</p><h3 class="text-3xl font-black">{{ fmt(n('payments30d')) }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Cash-outs Paid</p><h3 class="text-3xl font-black">{{ fmt(n('cashoutsCompleted')) }}</h3></div>
        </div>

        <div class="card rounded-3xl p-6">
            <div class="mb-5 grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                <input v-model="f.search" class="xl:col-span-2 rounded-2xl border border-slate-200 px-4 py-2" placeholder="Member name, email, LinkUp ID or transaction ID" />
                <select v-model="f.type" class="rounded-2xl border border-slate-200 px-3 py-2 bg-white">
                    <option value="">All types</option>
                    <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
                </select>
                <select v-model="f.status" class="rounded-2xl border border-slate-200 px-3 py-2 bg-white">
                    <option value="">Any status</option><option value="success">Completed</option><option value="pending">Pending</option><option value="failed">Failed</option>
                </select>
                <input v-model="f.from" type="date" class="rounded-2xl border border-slate-200 px-3 py-2" title="From" />
                <input v-model="f.to" type="date" class="rounded-2xl border border-slate-200 px-3 py-2" title="To" />
            </div>

            <p v-if="error" class="mb-3 rounded-2xl bg-rose-50 p-3 font-bold text-rose-700">{{ error }}</p>

            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs font-black text-slate-500 uppercase">
                        <tr><th class="py-3">Ref</th><th>Date</th><th>Member</th><th>Other member</th><th>Country</th><th>Type</th><th>Channel</th><th>Amount</th><th>Fee</th><th>Status</th></tr>
                    </thead>
                    <tbody :class="{ 'opacity-50': loading }">
                        <tr v-if="!rows.length && !loading"><td colspan="10" class="py-6 text-center text-slate-400 font-bold">No ledger entries match.</td></tr>
                        <tr v-for="w in rows" :key="w.id" class="border-t hover:bg-slate-50">
                            <td class="py-3 font-black text-purple-600 text-xs" :title="w.id">{{ String(w.id).slice(0, 8) }}</td>
                            <td class="whitespace-nowrap text-sm">{{ w.date }}</td>
                            <td class="font-bold">{{ w.user }}</td>
                            <td class="text-sm text-slate-500">{{ w.counterparty || '—' }}</td>
                            <td class="text-sm">{{ w.country }}</td>
                            <td>{{ w.type }}</td>
                            <td class="text-sm text-slate-500">{{ w.channel }}</td>
                            <td class="font-black" :class="w.direction === 'In' ? 'text-green-700' : ''">{{ w.direction === 'In' ? '+' : '−' }}{{ fmt(w.amount) }}</td>
                            <td class="font-bold text-sky-600">{{ fmt(w.fee) }}</td>
                            <td>
                                <span :class="{ 'bg-green-50 text-green-700': w.status === 'Completed', 'bg-amber-50 text-amber-700': w.status === 'Pending' || w.status === 'Review', 'bg-rose-50 text-rose-700': w.status === 'Failed' }" class="rounded-full px-3 py-1 text-xs font-black">{{ w.status }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <button :disabled="page <= 1 || loading" class="rounded-2xl border px-4 py-2 font-bold disabled:opacity-40" @click="page--">Previous</button>
                <span class="font-bold text-slate-500">Page {{ meta.current_page }} of {{ meta.last_page }}</span>
                <button :disabled="page >= meta.last_page || loading" class="rounded-2xl border px-4 py-2 font-bold disabled:opacity-40" @click="page++">Next</button>
            </div>
        </div>
    </div>
</template>
