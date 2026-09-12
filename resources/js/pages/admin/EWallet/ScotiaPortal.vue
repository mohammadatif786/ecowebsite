<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Landmark, X, BarChart3, Globe2, FileText, KeyRound, ShieldCheck, Lock, AlertCircle } from 'lucide-vue-next';

const props = defineProps<{
    units: any[];
    countries: any[];
    fmt: (n: number) => string;
    num: (n: number) => string;
    getScale: () => number;
}>();

const SCOTIA_SHARE = 0.40;

// Authentication State
const isAuthed = ref(sessionStorage.getItem('scotiaAuthed') === '1');
const show2FA = ref(false);
const loginForm = ref({
    email: '',
    password: '',
    code: ''
});
const loginError = ref('');

// Portal Data State
const scotiaSettlements = ref<any[]>([]);
let scotiaSettleSeq = 4400;
const settlementModalOpen = ref(false);
const settlementForm = ref({
    amount: 0,
    dest: 'Scotiabank Operating Account ••••2290',
    note: ''
});
const settlementError = ref('');
const reportNote = ref('');

const kycRecords = [
    { id: 'KYC-ORG-001', profileType: 'Organizer', name: 'Addnre Pierre', email: 'andre@hotmail.com', country: 'Bahamas', submitted: '2026-06-01 08:15', status: 'Verified', risk: 'Low', idFront: 'Approved', idBack: 'Approved', address: 'Approved', selfie: 'Approved', bank: 'Approved', notes: 'Organizer documents verified.' },
    { id: 'KYC-ORG-002', profileType: 'Organizer', name: 'Bijoux Pierre', email: 'test@test.com', country: 'Bahamas', submitted: '2026-06-01 09:45', status: 'Pending Review', risk: 'Medium', idFront: 'Pending', idBack: 'Pending', address: 'Pending', selfie: 'Pending', bank: 'Pending', notes: 'Documents submitted; awaiting review.' },
    { id: 'KYC-USR-003', profileType: 'User', name: 'mohammad atif', email: 'mohammadatif24680@gmail.com', country: 'Bahamas', submitted: '2026-05-30 14:10', status: 'Verified', risk: 'Low', idFront: 'Approved', idBack: 'Approved', address: 'Approved', selfie: 'Approved', bank: 'Approved', notes: 'User KYC approved for wallet withdrawals.' },
    { id: 'KYC-DRV-004', profileType: 'Driver', name: 'Andre Blake', email: 'andre.blake@drivers.linkup', country: 'Jamaica', submitted: '2026-06-01 10:20', status: 'Pending Review', risk: 'Medium', idFront: 'Approved', idBack: 'Approved', address: 'Pending', selfie: 'Approved', bank: 'Pending', notes: 'Driver insurance and police record still pending.' },
    { id: 'KYC-SELL-005', profileType: 'Marketplace Seller', name: 'Caribbean Beauty Store', email: 'seller@caribbeanbeauty.com', country: 'United States', submitted: '2026-06-01 10:50', status: 'Investigating', risk: 'High', idFront: 'Approved', idBack: 'Approved', address: 'Approved', selfie: 'N/A', bank: 'Pending', notes: 'Seller payout held pending business docs and refund window.' },
    { id: 'KYC-MER-006', profileType: 'Merchant', name: 'SuperValue Nassau', email: 'merchant@supervalue.com', country: 'Bahamas', submitted: '2026-05-28 13:35', status: 'Verified', risk: 'Low', idFront: 'Approved', idBack: 'Approved', address: 'Approved', selfie: 'N/A', bank: 'Approved', notes: 'Merchant approved for QR payments and settlement.' },
    { id: 'KYC-LIVE-007', profileType: 'Live Creator', name: 'Island Queen Live', email: 'creator@islandqueen.live', country: 'Bahamas', submitted: '2026-06-01 12:30', status: 'Pending Review', risk: 'Medium', idFront: 'Approved', idBack: 'Pending', address: 'Approved', selfie: 'Approved', bank: 'Pending', notes: 'Creator can stream but cash-out is held until bank verification.' }
];

// Computed Data
const scotiaData = computed(() => {
    const scale = props.getScale();
    const procUnits = props.units.filter(u => u.bankRate > 0);
    let volume = 0, pool = 0;

    const byUnit = procUnits.map(u => {
        const gross = props.countries.reduce((s, c) => s + (c[u.key] || 0), 0) * scale;
        const fee = gross * u.bankRate;
        volume += gross;
        pool += fee;
        return { name: u.name, key: u.key, volume: gross, fee: fee, share: fee * SCOTIA_SHARE };
    }).sort((a, b) => b.share - a.share);

    const byCountry = props.countries.map(c => {
        let gross = 0, platform = 0, bank = 0;
        props.units.forEach(u => {
            const v = (c[u.key] || 0) * scale;
            gross += v;
            platform += v * u.platformRate;
            bank += v * u.bankRate;
        });
        return { country: c.country, region: c.region, fee: bank, share: bank * SCOTIA_SHARE };
    }).filter(x => x.fee > 0).sort((a, b) => b.share - a.share);

    const ledger: any[] = [];
    const today = '2026-06-02';
    props.countries.forEach(c => {
        procUnits.forEach(u => {
            const vol = (c[u.key] || 0) * scale;
            if (vol <= 0) return;
            const fee = vol * u.bankRate;
            const code = (c.country || '').replace(/[^A-Za-z]/g, '').slice(0, 3).toUpperCase() || 'XX';
            ledger.push({
                batch: `SCO-${code}-${u.key.slice(0, 4).toUpperCase()}`,
                date: today,
                unit: u.name,
                country: c.country,
                volume: vol,
                fee: fee,
                share: fee * SCOTIA_SHARE,
                status: fee * SCOTIA_SHARE > 15000 ? 'Settled' : 'Pending'
            });
        });
    });
    ledger.sort((a, b) => b.share - a.share);

    return { volume, pool, scotiaEarnings: pool * SCOTIA_SHARE, byUnit, byCountry, ledger };
});

const kycStats = computed(() => {
    return {
        total: kycRecords.length,
        verified: kycRecords.filter(k => k.status === 'Verified').length,
        pending: kycRecords.filter(k => k.status === 'Pending Review').length,
        risk: kycRecords.filter(k => k.status === 'Rejected' || k.risk === 'High').length
    };
});

const availableToSettle = computed(() => {
    const totalEarnings = scotiaData.value.scotiaEarnings;
    const pending = scotiaSettlements.value.filter(s => s.status === 'Requested').reduce((a, s) => a + s.amount, 0);
    return Math.max(0, totalEarnings - pending);
});

const settledToDate = computed(() => {
    return scotiaSettlements.value.filter(s => s.status === 'Paid').reduce((a, s) => a + s.amount, 0);
});

// Auth Methods
const handleLogin = () => {
    if (loginForm.value.email.toLowerCase() === 'partner@scotiabank.com' && loginForm.value.password === 'scotia2026') {
        loginError.value = '';
        show2FA.value = true;
    } else {
        loginError.value = 'Invalid email or password. Use demo credentials.';
    }
};

const verify2FA = () => {
    if (loginForm.value.code === '123456' || loginForm.value.code === 'LINKUP') { // Mock logic
        isAuthed.value = true;
        sessionStorage.setItem('scotiaAuthed', '1');
        loginError.value = '';
    } else {
        loginError.value = 'Invalid verification code. Try 123456.';
    }
};

const logout = () => {
    isAuthed.value = false;
    sessionStorage.removeItem('scotiaAuthed');
    show2FA.value = false;
    loginForm.value = { email: '', password: '', code: '' };
};

// Settlement Methods
const openSettlement = () => {
    settlementForm.value.amount = Number(availableToSettle.value.toFixed(2));
    settlementModalOpen.value = true;
};

const submitSettlement = () => {
    if (settlementForm.value.amount <= 0) {
        settlementError.value = 'Enter a valid amount.';
        return;
    }
    if (settlementForm.value.amount > availableToSettle.value + 0.01) {
        settlementError.value = 'Exceeds available balance.';
        return;
    }

    scotiaSettlements.value.unshift({
        id: `SET-SCO-${++scotiaSettleSeq}`,
        date: new Date().toISOString().slice(0, 10),
        amount: settlementForm.value.amount,
        dest: settlementForm.value.dest,
        note: settlementForm.value.note || 'Processing settlement',
        status: 'Requested',
        ref: ''
    });
    settlementModalOpen.value = false;
    reportNote.value = `✓ Settlement request submitted for ${props.fmt(settlementForm.value.amount)}.`;
};

const processSettlement = (id: string) => {
    const s = scotiaSettlements.value.find(x => x.id === id);
    if (s) {
        s.status = 'Paid';
        s.ref = `PAYREF-${Math.floor(100000 + Math.random() * 900000)}`;
    }
};

const cancelSettlement = (id: string) => {
    scotiaSettlements.value = scotiaSettlements.value.filter(x => x.id !== id);
};

const runReport = (type: string) => {
    reportNote.value = `✓ ${type.charAt(0).toUpperCase() + type.slice(1)} report generated and downloaded.`;
};

const scotiaMoney = (n: number) => props.fmt(n);

</script>

<template>
    <div class="space-y-6">
        <!-- LOGIN GATE -->
        <div v-if="!isAuthed" class="mx-auto mt-4 max-w-md">
            <div class="card rounded-[2.5rem] p-8">
                <div class="mb-6 flex items-center gap-3">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-red-600 text-xl font-black text-white">S</div>
                    <div>
                        <h3 class="text-2xl font-black">Scotiabank Partner Login</h3>
                        <p class="text-sm text-slate-500">Secure access to your LinkUp processing earnings</p>
                    </div>
                </div>

                <div v-if="loginError" class="mb-4 rounded-2xl bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">
                    {{ loginError }}
                </div>

                <div v-if="!show2FA">
                    <label class="mb-1 block text-sm font-bold">Partner Email</label>
                    <input v-model="loginForm.email" class="mb-4 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="partner@scotiabank.com" />
                    <label class="mb-1 block text-sm font-bold">Password</label>
                    <input v-model="loginForm.password" type="password" class="mb-5 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="••••••••" @keyup.enter="handleLogin" />
                    <button @click="handleLogin" class="w-full rounded-2xl bg-red-600 px-4 py-3 font-black text-white">Log In to Partner Portal</button>
                    <p class="mt-4 text-xs text-slate-400">Demo access — Email: <b>partner@scotiabank.com</b> · Password: <b>scotia2026</b></p>
                </div>

                <div v-else>
                    <div class="mb-4 rounded-2xl border border-sky-100 bg-sky-50 p-4 text-sm">
                        <b>Two-Factor Authentication</b>
                        <p class="text-slate-600">Enter the 6-digit code from your authenticator app to continue.</p>
                        <p class="mt-1 text-xs text-slate-400">Demo code: 123456</p>
                    </div>
                    <label class="mb-1 block text-sm font-bold">Verification Code</label>
                    <input v-model="loginForm.code" maxlength="6" class="mb-5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-center font-mono text-lg tracking-widest" placeholder="••••••" @keyup.enter="verify2FA" />
                    <button @click="verify2FA" class="w-full rounded-2xl bg-red-600 px-4 py-3 font-black text-white">Verify & Continue</button>
                    <button @click="show2FA = false" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 font-black">← Back</button>
                </div>
            </div>
        </div>

        <!-- PORTAL CONTENT -->
        <div v-else class="space-y-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex items-center gap-3">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-red-600 text-xl font-black text-white">S</div>
                    <div>
                        <h3 class="text-3xl font-black">Scotiabank Partner Portal</h3>
                        <p class="text-slate-500">Processing earnings from money moved through LinkUp — <span class="font-black">Monthly</span></p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="openSettlement" class="rounded-2xl bg-red-600 px-5 py-2 font-black text-white">Request Settlement</button>
                    <button @click="runReport('earnings')" class="rounded-2xl bg-slate-950 px-5 py-2 font-black text-white">Export Earnings CSV</button>
                    <button @click="logout" class="rounded-2xl border border-slate-200 px-5 py-2 font-black">Log Out</button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="card metric dark col-span-1 rounded-3xl p-5 xl:col-span-2">
                    <p class="font-bold text-slate-300">Scotiabank Earnings</p>
                    <h3 class="text-5xl font-black">{{ fmt(scotiaData.scotiaEarnings) }}</h3>
                    <p class="font-bold text-lime-300">Processing fee share</p>
                </div>
                <div class="card rounded-3xl p-5">
                    <p class="font-bold text-slate-500">Volume Processed</p>
                    <h3 class="text-4xl font-black">{{ fmt(scotiaData.volume) }}</h3>
                    <p class="font-bold text-sky-500">Money moved via Scotia rails</p>
                </div>
                <div class="card rounded-3xl p-5">
                    <p class="font-bold text-slate-500">Scotia Share</p>
                    <h3 class="text-4xl font-black">{{ (SCOTIA_SHARE * 100).toFixed(0) }}%</h3>
                    <p class="font-bold text-purple-500">LinkUp 60% / Scotia 40%</p>
                </div>
            </div>

            <!-- Fee Sources Table -->
            <div class="card rounded-3xl p-6">
                <h3 class="mb-1 text-xl font-black">Fee Sources — What the Bank Earns</h3>
                <p class="mb-3 text-sm text-slate-500">Every bank/processing fee feeding this partnership. Split LinkUp 60% / Scotiabank 40%.</p>
                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-xs font-black uppercase text-slate-500">
                            <tr>
                                <th class="py-2">Bank Fee Source</th>
                                <th class="text-right">Pool</th>
                                <th class="text-right">Scotiabank 40%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="unit in scotiaData.byUnit" :key="unit.key" class="border-t">
                                <td class="py-2 font-bold">{{ unit.name }}</td>
                                <td class="text-right">{{ fmt(unit.fee) }}</td>
                                <td class="text-right font-black text-red-600">{{ fmt(unit.share) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-slate-300 font-black">
                                <td class="py-2">Total</td>
                                <td class="text-right">{{ fmt(scotiaData.pool) }}</td>
                                <td class="text-right text-red-600">{{ fmt(scotiaData.scotiaEarnings) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Ledger Table -->
            <div class="card rounded-3xl p-6">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-xl font-black">Processing Transaction Ledger</h3>
                        <p class="text-slate-500">Settlement batches Scotiabank processed for LinkUp.</p>
                    </div>
                </div>
                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs font-black uppercase text-slate-500">
                            <tr>
                                <th class="py-3">Batch</th>
                                <th>Date</th>
                                <th>Business Unit</th>
                                <th>Country</th>
                                <th>Volume</th>
                                <th class="text-right">Bank Fee</th>
                                <th class="text-right">Scotia Share</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="x in scotiaData.ledger" :key="x.batch" class="border-t">
                                <td class="py-3 font-black">{{ x.batch }}</td>
                                <td>{{ x.date }}</td>
                                <td>{{ x.unit }}</td>
                                <td>{{ x.country }}</td>
                                <td>{{ fmt(x.volume) }}</td>
                                <td class="text-right font-bold">{{ fmt(x.fee) }}</td>
                                <td class="text-right font-black text-red-600">{{ fmt(x.share) }}</td>
                                <td>
                                    <span class="rounded-full px-3 py-1 text-xs font-black" :class="x.status === 'Settled' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'">
                                        {{ x.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- KYC Review -->
            <div class="card rounded-3xl p-6">
                <h3 class="text-xl font-black">Customer KYC / AML Review</h3>
                <p class="mb-4 text-slate-500">Verify identity & compliance status of LinkUp customers processed through Scotiabank.</p>

                <div class="mb-5 grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="font-bold text-slate-500">Total Customers</p>
                        <h4 class="text-2xl font-black">{{ kycStats.total }}</h4>
                    </div>
                    <div class="rounded-2xl bg-green-50 p-4">
                        <p class="font-bold text-green-700">Verified</p>
                        <h4 class="text-2xl font-black">{{ kycStats.verified }}</h4>
                    </div>
                    <div class="rounded-2xl bg-amber-50 p-4">
                        <p class="font-bold text-amber-700">Pending Review</p>
                        <h4 class="text-2xl font-black">{{ kycStats.pending }}</h4>
                    </div>
                    <div class="rounded-2xl bg-rose-50 p-4">
                        <p class="font-bold text-rose-700">High Risk / Rejected</p>
                        <h4 class="text-2xl font-black">{{ kycStats.risk }}</h4>
                    </div>
                </div>

                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs font-black uppercase text-slate-500">
                            <tr>
                                <th class="py-3">Customer</th>
                                <th>Type</th>
                                <th>Country</th>
                                <th>Risk</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="k in kycRecords" :key="k.id" class="border-t">
                                <td class="py-3 font-black">
                                    {{ k.name }}
                                    <div class="text-xs font-normal text-slate-500">{{ k.email }}</div>
                                </td>
                                <td>{{ k.profileType }}</td>
                                <td>{{ k.country }}</td>
                                <td>
                                    <span class="rounded-full px-3 py-1 text-xs font-black" :class="k.risk === 'High' ? 'bg-rose-50 text-rose-700' : (k.risk === 'Medium' ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700')">
                                        {{ k.risk }}
                                    </span>
                                </td>
                                <td>
                                    <span class="rounded-full px-3 py-1 text-xs font-black" :class="k.status === 'Verified' ? 'bg-green-50 text-green-700' : (k.status === 'Rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700')">
                                        {{ k.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Settlement Requests -->
            <div class="card rounded-3xl p-6">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-xl font-black">Settlement Requests</h3>
                        <p class="text-slate-500">Request payout of Scotiabank's earned processing share.</p>
                    </div>
                    <button @click="openSettlement" class="rounded-2xl bg-red-600 px-5 py-2 font-black text-white">+ Request Settlement</button>
                </div>

                <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="font-bold text-slate-500">Available to Settle</p>
                        <h4 class="text-2xl font-black">{{ fmt(availableToSettle) }}</h4>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="font-bold text-slate-500">Pending Requests</p>
                        <h4 class="text-2xl font-black">{{ fmt(scotiaSettlements.filter(s => s.status === 'Requested').reduce((a, s) => a + s.amount, 0)) }}</h4>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="font-bold text-slate-500">Settled to Date</p>
                        <h4 class="text-2xl font-black">{{ fmt(settledToDate) }}</h4>
                    </div>
                </div>

                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs font-black uppercase text-slate-500">
                            <tr>
                                <th class="py-3">Request</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Destination</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="s in scotiaSettlements" :key="s.id" class="border-t">
                                <td class="py-3 font-black">{{ s.id }}</td>
                                <td>{{ s.date }}</td>
                                <td class="font-black">{{ fmt(s.amount) }}</td>
                                <td>{{ s.dest }}</td>
                                <td>
                                    <span class="rounded-full px-3 py-1 text-xs font-black" :class="s.status === 'Paid' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'">
                                        {{ s.status }}
                                    </span>
                                </td>
                                <td>
                                    <div v-if="s.status === 'Requested'" class="flex gap-2">
                                        <button @click="processSettlement(s.id)" class="rounded-lg bg-green-600 px-3 py-1 text-xs font-black text-white">Process</button>
                                        <button @click="cancelSettlement(s.id)" class="rounded-lg bg-slate-200 px-3 py-1 text-xs font-black">Cancel</button>
                                    </div>
                                    <span v-else class="text-xs font-bold text-slate-400">{{ s.ref }}</span>
                                </td>
                            </tr>
                            <tr v-if="scotiaSettlements.length === 0">
                                <td colspan="6" class="py-6 text-center font-bold text-slate-400">No settlement requests yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Report Options -->
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Run Earnings Reports</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <button @click="runReport('unit')" class="flex flex-col items-start rounded-2xl border border-slate-200 p-4 text-left font-black hover:bg-slate-50">
                        <BarChart3 class="h-6 w-6 text-slate-500" />
                        <div class="mt-2">Earnings by Business Unit</div>
                        <p class="text-xs font-bold text-slate-500">Current filters · CSV</p>
                    </button>
                    <button @click="runReport('country')" class="flex flex-col items-start rounded-2xl border border-slate-200 p-4 text-left font-black hover:bg-slate-50">
                        <Globe2 class="h-6 w-6 text-slate-500" />
                        <div class="mt-2">Earnings by Country</div>
                        <p class="text-xs font-bold text-slate-500">Current filters · CSV</p>
                    </button>
                    <button @click="runReport('ledger')" class="flex flex-col items-start rounded-2xl border border-slate-200 p-4 text-left font-black hover:bg-slate-50">
                        <FileText class="h-6 w-6 text-slate-500" />
                        <div class="mt-2">Transaction Ledger</div>
                        <p class="text-xs font-bold text-slate-500">Current filters · CSV</p>
                    </button>
                </div>
                <p v-if="reportNote" class="mt-4 text-sm font-bold text-green-600">{{ reportNote }}</p>
            </div>
        </div>

        <!-- SETTLEMENT MODAL -->
        <Teleport to="body">
            <div v-if="settlementModalOpen" class="fixed inset-0 z-[10000] flex items-center justify-center bg-slate-900/50 p-5 backdrop-blur-sm" @click.self="settlementModalOpen = false">
                <div class="w-full max-w-lg overflow-hidden rounded-[2rem] bg-white shadow-2xl animate-in fade-in zoom-in duration-200">
                    <div class="relative bg-gradient-to-r from-red-600 to-rose-500 p-8 text-white">
                        <button @click="settlementModalOpen = false" class="absolute top-6 right-6 rounded-full p-2 hover:bg-white/10">
                            <X class="h-6 w-6" />
                        </button>
                        <h3 class="text-3xl font-black mb-1">Request Settlement</h3>
                        <p class="text-rose-100 text-sm">Request payout of Scotiabank's earned processing share.</p>
                    </div>

                    <div class="p-8 space-y-6">
                        <div class="flex justify-between rounded-2xl bg-slate-50 p-4">
                            <span class="font-bold text-slate-500">Available to settle</span>
                            <b class="text-lg font-black">{{ fmt(availableToSettle) }}</b>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-600">Amount to settle (USD)</label>
                            <input v-model.number="settlementForm.amount" type="number" step="0.01" class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-600">Destination account</label>
                            <select v-model="settlementForm.dest" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold appearance-none bg-no-repeat bg-[right_1rem_center]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-size: 1.25rem;">
                                <option>Scotiabank Operating Account ••••2290</option>
                                <option>Scotiabank Settlement Account ••••7741</option>
                                <option>Scotiabank Reserve Account ••••5530</option>
                            </select>
                        </div>

                        <div v-if="settlementError" class="rounded-xl bg-rose-50 p-3 text-sm font-bold text-rose-700">
                            {{ settlementError }}
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button @click="settlementModalOpen = false" class="rounded-2xl bg-slate-100 px-8 py-4 font-black text-slate-600 hover:bg-slate-200">Cancel</button>
                            <button @click="submitSettlement" class="rounded-2xl bg-slate-950 px-8 py-4 font-black text-white hover:bg-slate-800 shadow-lg shadow-slate-950/20">Submit Request</button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
