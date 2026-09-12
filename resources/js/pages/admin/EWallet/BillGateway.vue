<script setup lang="ts">
import { X, Zap } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

const props = defineProps<{
    billProviders: any[];
    fmt: (n: number) => string;
}>();

const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

// Local copy so add/edit/remove stays fully client-side (no backend/persistence yet) —
// changes here don't mutate the shared composable's providers list.
const providers = ref([...props.billProviders]);

// Static display data matching the source dashboard's Bill Gateway summary/ledger —
// there's no real bill-payment backend wired up yet, so this stays local/mock.
const billStats = {
    billsPaid: 1284,
    billVolume: 386220,
    gatewayFees: 7724,
    failedBills: 9,
};
const billLedger = [
    { id: 'BILL-1001', user: 'Aaliyah Clarke', biller: 'BPL', category: 'Electricity', country: 'Bahamas', amount: 185.0, fee: 2.5, status: 'Paid' },
    { id: 'BILL-1002', user: 'Jason Rolle', biller: 'BTC', category: 'Mobile', country: 'Bahamas', amount: 65.0, fee: 1.5, status: 'Paid' },
    { id: 'BILL-1003', user: 'Tanya Baptiste', biller: 'T&TEC', category: 'Electricity', country: 'Trinidad & Tobago', amount: 142.0, fee: 2.25, status: 'Failed' },
];

const statusDot = (status: string) => (status === 'Active' ? '#16a34a' : '#f59e0b');

const emptyProviderForm = () => ({
    name: '',
    apiBase: '',
    apiKey: '',
    billerId: '',
    status: 'Active',
    services: '',
    rails: '',
    settlement: '',
    pci: false,
    color: '#6366F1',
});

const configModalOpen = ref(false);
const editingIndex = ref<number | null>(null);
const providerForm = reactive(emptyProviderForm());

const openAddProvider = () => {
    editingIndex.value = null;
    Object.assign(providerForm, emptyProviderForm());
    configModalOpen.value = true;
};

const openConfigProvider = (index: number) => {
    editingIndex.value = index;
    Object.assign(providerForm, emptyProviderForm(), providers.value[index]);
    configModalOpen.value = true;
};

const closeConfigModal = () => {
    configModalOpen.value = false;
};

const testConnection = () => {
    if (!providerForm.apiBase) {
        alert('Enter an API Base URL first.');
        return;
    }
    alert(`Test connection to ${providerForm.apiBase} succeeded (simulated — no live gateway connected yet).`);
};

const saveProvider = () => {
    if (!providerForm.name.trim()) {
        alert('Provider name is required.');
        return;
    }
    const payload = { ...providerForm };
    if (editingIndex.value === null) {
        providers.value.push(payload);
    } else {
        providers.value.splice(editingIndex.value, 1, payload);
    }
    configModalOpen.value = false;
};

const removeProvider = (index: number) => {
    if (confirm(`Remove ${providers.value[index].name} from Bill Gateway?`)) {
        providers.value.splice(index, 1);
    }
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black text-slate-950">Bill Gateway</h3>
            <p class="text-slate-500">Track utility, telecom, school, insurance, and merchant bill payments through LinkUp Wallet.</p>
        </div>

        <div class="card rounded-3xl p-6">
            <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-2xl font-black">Utility Providers</h3>
                    <p class="text-slate-500">Manage API endpoints for Bill Pay.</p>
                </div>
                <button @click="openAddProvider" class="rounded-2xl bg-slate-950 px-5 py-2 font-black text-white">+ Add Provider</button>
            </div>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div v-if="!providers.length" class="col-span-full py-6 text-center text-slate-400 font-bold">No providers configured.</div>
                <div v-for="(p, i) in providers" :key="p.billerId || p.name" class="relative overflow-hidden rounded-3xl border border-slate-200 p-6">
                    <Zap class="absolute top-4 right-4 h-20 w-20 text-slate-100" />
                    <div
                        class="relative z-10 mb-4 grid h-12 w-12 place-items-center rounded-2xl"
                        :style="{ background: p.color + '1f', color: p.color }"
                    >
                        <Zap />
                    </div>
                    <h4 class="relative z-10 text-lg font-black">{{ p.name }}</h4>
                    <p class="relative z-10 text-sm text-slate-500">
                        API: {{ p.status }} <span :style="{ color: statusDot(p.status) }">●</span>
                    </p>
                    <p class="relative z-10 mb-4 text-xs text-slate-400">
                        {{ p.services }} · Biller {{ p.billerId }}<span v-if="p.pci"> · PCI ✓</span>
                    </p>
                    <div class="relative z-10 flex gap-2">
                        <button @click="openConfigProvider(i)" class="flex-1 rounded-2xl border border-slate-200 px-4 py-2 font-black hover:bg-slate-50">
                            Config
                        </button>
                        <button @click="removeProvider(i)" class="rounded-2xl bg-rose-50 px-4 py-2 font-black text-rose-600">✕</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Bills Paid</p>
                <h3 class="text-4xl font-black">{{ num(billStats.billsPaid) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Bill Volume</p>
                <h3 class="text-4xl font-black">{{ fmt(billStats.billVolume) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Gateway Fees</p>
                <h3 class="text-4xl font-black">{{ fmt(billStats.gatewayFees) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Failed Bills</p>
                <h3 class="text-4xl font-black">{{ num(billStats.failedBills) }}</h3>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <h3 class="mb-4 text-xl font-black">Bill Payment Ledger</h3>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs font-black text-slate-500 uppercase">
                        <tr>
                            <th class="py-3">Bill ID</th>
                            <th>User</th>
                            <th>Biller</th>
                            <th>Category</th>
                            <th>Country</th>
                            <th>Amount</th>
                            <th>Fee</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="b in billLedger" :key="b.id" class="border-t">
                            <td class="py-3 font-black text-purple-600">{{ b.id }}</td>
                            <td>{{ b.user }}</td>
                            <td>{{ b.biller }}</td>
                            <td>{{ b.category }}</td>
                            <td>{{ b.country }}</td>
                            <td>{{ fmt(b.amount) }}</td>
                            <td>{{ fmt(b.fee) }}</td>
                            <td>
                                <span
                                    :class="b.status === 'Paid' ? 'bg-green-50 text-green-700' : 'bg-rose-50 text-rose-700'"
                                    class="rounded-full px-3 py-1 text-xs font-black"
                                >
                                    {{ b.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Provider Config Modal -->
        <div v-if="configModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-5" @click.self="closeConfigModal">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 p-6">
                    <div class="flex items-center gap-3">
                        <div class="grid h-10 w-10 place-items-center rounded-2xl" :style="{ background: providerForm.color + '1f', color: providerForm.color }">
                            <Zap class="h-5 w-5" />
                        </div>
                        <h3 class="text-xl font-black">{{ editingIndex === null ? 'Add Provider' : 'Provider Config' }}</h3>
                    </div>
                    <button @click="closeConfigModal" class="text-slate-400 hover:text-slate-600"><X /></button>
                </div>
                <div class="space-y-3 p-6">
                    <div>
                        <label class="text-sm font-bold text-slate-600">Provider Name</label>
                        <input v-model="providerForm.name" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-600">API Base URL</label>
                        <input v-model="providerForm.apiBase" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="https://api.provider.com/v1" />
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-600">API Key / Secret</label>
                        <input v-model="providerForm.apiKey" type="password" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="sk_live_..." />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-bold text-slate-600">Biller ID</label>
                            <input v-model="providerForm.billerId" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-600">Status</label>
                            <select v-model="providerForm.status" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                                <option>Active</option>
                                <option>Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-600">Services Supported</label>
                        <input v-model="providerForm.services" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Electricity, Water, Mobile..." />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-bold text-slate-600">Payment Rails</label>
                            <input v-model="providerForm.rails" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-600">Settlement Account</label>
                            <input v-model="providerForm.settlement" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                        </div>
                    </div>
                    <label class="flex items-center gap-2 font-bold text-slate-600">
                        <input v-model="providerForm.pci" type="checkbox" class="h-5 w-5 accent-green-600" /> PCI-DSS compliant card handling
                    </label>
                    <div class="rounded-2xl bg-slate-50 p-4 text-xs text-slate-500">
                        Integration follows the standard bill-pay flow: <b>Biller Fetch → Bill Fetch → Payment Processing</b>, over SSL/TLS with
                        real-time settlement &amp; status callbacks (Reloadly / BBPS / ACI Worldwide compatible).
                    </div>
                </div>
                <div class="flex justify-between border-t border-slate-100 p-6">
                    <button @click="testConnection" class="rounded-2xl border border-slate-200 px-5 py-2 font-black">Test Connection</button>
                    <div class="flex gap-2">
                        <button @click="closeConfigModal" class="rounded-2xl border border-slate-200 px-5 py-2 font-black">Cancel</button>
                        <button @click="saveProvider" class="rounded-2xl bg-slate-950 px-5 py-2 font-black text-white">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
