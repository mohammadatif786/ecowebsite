<script setup lang="ts">
import TaxLayout from './components/TaxLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Pencil, Trash2 } from 'lucide-vue-next';

type RemittanceCenter = {
    id: number;
    agency_name: string;
    jurisdiction: string;
    payment_method: 'ach' | 'wire' | 'eftps' | 'state_api';
    routing_number?: string | null;
    account_number?: string | null;
    api_base?: string | null;
    has_api_key: boolean;
    has_api_secret: boolean;
    is_active: boolean;
    updated_at?: string | null;
};

type RemittanceReport = {
    id: string;
    event: string;
    organizer: string;
    jurisdiction: string;
    agency_name: string;
    gross: number;
    tax: number;
    transactions: number;
    status: string;
};

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    remittanceCenters: RemittanceCenter[];
    remittanceReports: RemittanceReport[];
}>();

const search = ref('');
const status = ref('All');
const editingCenter = ref<RemittanceCenter | null>(null);

const form = useForm({
    id: null as number | null,
    agency_name: '',
    jurisdiction: '',
    payment_method: '' as '' | 'ach' | 'wire' | 'eftps' | 'state_api',
    routing_number: '',
    account_number: '',
    api_base: '',
    api_key: '',
    api_secret: '',
    is_active: true,
});

const showBankFields = computed(() => ['ach', 'wire'].includes(form.payment_method));
const showApiFields = computed(() => ['eftps', 'state_api'].includes(form.payment_method));

const filtered = computed(() => {
    const q = search.value.toLowerCase();

    return props.remittanceReports.filter((report) => {
        const matchesStatus = status.value === 'All' || report.status === status.value;
        const matchesSearch = !q || [report.event, report.organizer, report.jurisdiction, report.agency_name, report.status].join(' ').toLowerCase().includes(q);

        return matchesStatus && matchesSearch;
    });
});

const liability = computed(() => props.remittanceReports.reduce((sum, report) => sum + Number(report.tax || 0), 0));
const paid = computed(() => props.remittanceReports.filter((report) => report.status === 'Paid').reduce((sum, report) => sum + Number(report.tax || 0), 0));
const pending = computed(() => Math.max(0, liability.value - paid.value));
const paidPct = computed(() => liability.value > 0 ? Math.round((paid.value / liability.value) * 100) : 0);
const chartStyle = computed(() => `background:conic-gradient(#10b981 0 ${paidPct.value}%, #f59e0b ${paidPct.value}% 100%)`);

const resetForm = () => {
    editingCenter.value = null;
    form.clearErrors();
    form.id = null;
    form.agency_name = '';
    form.jurisdiction = '';
    form.payment_method = '';
    form.routing_number = '';
    form.account_number = '';
    form.api_base = '';
    form.api_key = '';
    form.api_secret = '';
    form.is_active = true;
};

const editCenter = (center: RemittanceCenter) => {
    editingCenter.value = center;
    form.clearErrors();
    form.id = center.id;
    form.agency_name = center.agency_name;
    form.jurisdiction = center.jurisdiction;
    form.payment_method = center.payment_method;
    form.routing_number = center.routing_number || '';
    form.account_number = '';
    form.api_base = center.api_base || '';
    form.api_key = '';
    form.api_secret = '';
    form.is_active = center.is_active;
};

const saveCenter = () => {
    form.post(route('admin.finance.taxes.remittance-center.save'), {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    });
};

const deleteCenter = (center: RemittanceCenter) => {
    if (!window.confirm(`Delete ${center.agency_name}?`)) {
        return;
    }

    router.delete(route('admin.finance.taxes.remittance-center.destroy', center.id), {
        preserveScroll: true,
    });
};

const methodLabel = (method: string) => ({
    ach: 'ACH',
    wire: 'Wire',
    eftps: 'IRS EFTPS',
    state_api: 'State API',
}[method] || method);

const exportCSV = () => {
    const rows = [
        ['Event', 'Organizer', 'Jurisdiction', 'Agency', 'Gross', 'Tax Collected', 'Transactions', 'Status'],
        ...filtered.value.map((row) => [row.event, row.organizer, row.jurisdiction, row.agency_name, row.gross, row.tax, row.transactions, row.status]),
    ];
    const csvContent = rows.map((row) => row.map((cell) => `"${String(cell ?? '').replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'linkup_tax_remittance_center.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
};

const printPage = () => {
    window.print();
};
</script>

<template>
    <TaxLayout title="Tax Remittance Center" active-id="taxRemittanceCenterCommand" :initial-units="props.initialUnits" :initial-countries="props.initialCountries" v-slot="{ fmt, num }">
        <div class="space-y-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-3xl font-black">Tax Remittance Center</h3>
                    <p class="text-slate-500">Configure payment destinations and audit event-based tax collection from database records.</p>
                </div>
                <button class="rounded-2xl bg-slate-950 px-5 py-3 font-black text-white" @click="resetForm">+ Add Destination</button>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="card rounded-3xl p-6">
                    <h3 class="mb-4 text-xl font-black">{{ editingCenter ? 'Edit Destination' : 'Add Destination' }}</h3>
                    <form class="space-y-4" @submit.prevent="saveCenter">
                        <div>
                            <label class="text-xs font-black uppercase text-slate-500">Agency Name *</label>
                            <input v-model="form.agency_name" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3" placeholder="e.g. IRS / NY State Dept" />
                            <p v-if="form.errors.agency_name" class="mt-1 text-xs font-bold text-red-600">{{ form.errors.agency_name }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-black uppercase text-slate-500">Jurisdiction *</label>
                            <input v-model="form.jurisdiction" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3" placeholder="e.g. Federal, FL, Bahamas VAT" />
                            <p v-if="form.errors.jurisdiction" class="mt-1 text-xs font-bold text-red-600">{{ form.errors.jurisdiction }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-black uppercase text-slate-500">Payment Method *</label>
                            <select v-model="form.payment_method" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3">
                                <option value="">-- Select Method --</option>
                                <option value="ach">ACH Transfer</option>
                                <option value="wire">Wire Transfer</option>
                                <option value="eftps">IRS EFTPS API</option>
                                <option value="state_api">State Tax Portal API</option>
                            </select>
                            <p v-if="form.errors.payment_method" class="mt-1 text-xs font-bold text-red-600">{{ form.errors.payment_method }}</p>
                        </div>

                        <div v-if="showBankFields" class="grid grid-cols-1 gap-3 border-t border-dashed border-slate-200 pt-4 md:grid-cols-2">
                            <div>
                                <label class="text-xs font-black uppercase text-slate-500">Routing #</label>
                                <input v-model="form.routing_number" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 font-mono" placeholder="9 digits" />
                            </div>
                            <div>
                                <label class="text-xs font-black uppercase text-slate-500">Account #</label>
                                <input v-model="form.account_number" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 font-mono" :placeholder="editingCenter?.account_number || 'Account number'" />
                            </div>
                        </div>

                        <div v-if="showApiFields" class="space-y-3 border-t border-dashed border-slate-200 pt-4">
                            <div>
                                <label class="text-xs font-black uppercase text-slate-500">API Base URL</label>
                                <input v-model="form.api_base" type="url" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 font-mono" placeholder="https://..." />
                                <p v-if="form.errors.api_base" class="mt-1 text-xs font-bold text-red-600">{{ form.errors.api_base }}</p>
                            </div>
                            <input v-model="form.api_key" class="w-full rounded-xl border border-slate-200 px-4 py-3 font-mono" :placeholder="editingCenter?.has_api_key ? 'Leave blank to keep existing API key' : 'API key'" />
                            <input v-model="form.api_secret" type="password" class="w-full rounded-xl border border-slate-200 px-4 py-3 font-mono" :placeholder="editingCenter?.has_api_secret ? 'Leave blank to keep existing API secret' : 'API secret'" />
                        </div>

                        <label class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-700">
                            <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300" />
                            Active destination
                        </label>

                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 rounded-xl bg-blue-500 px-5 py-3 font-black text-white disabled:opacity-60" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : editingCenter ? 'Update Configuration' : 'Save Configuration' }}
                            </button>
                            <button v-if="editingCenter" type="button" class="rounded-xl border border-slate-200 px-4 py-3 font-black text-slate-600" @click="resetForm">Cancel</button>
                        </div>
                    </form>
                </div>

                <div class="card rounded-3xl p-6 xl:col-span-2">
                    <h3 class="mb-4 text-xl font-black">Active Configurations ({{ props.remittanceCenters.length }})</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">Agency</th>
                                    <th>Jurisdiction</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="center in props.remittanceCenters" :key="center.id" class="border-t">
                                    <td class="px-4 py-4 font-bold">{{ center.agency_name }}</td>
                                    <td>{{ center.jurisdiction }}</td>
                                    <td><span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-black">{{ methodLabel(center.payment_method) }}</span></td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-xs font-black" :class="center.is_active ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500'">
                                            {{ center.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="grid h-9 w-9 place-items-center rounded-xl bg-slate-100 text-slate-500 hover:bg-blue-100 hover:text-blue-700" title="Edit" @click="editCenter(center)">
                                                <Pencil class="h-4 w-4" />
                                            </button>
                                            <button class="grid h-9 w-9 place-items-center rounded-xl bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-700" title="Delete" @click="deleteCenter(center)">
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!props.remittanceCenters.length">
                                    <td colspan="5" class="px-4 py-12 text-center text-sm font-bold text-slate-400">No destinations configured</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="card rounded-3xl p-5"><p class="text-xs font-black uppercase text-slate-400">Total Tax Liability</p><h3 class="text-3xl font-black">{{ fmt(liability) }}</h3></div>
                <div class="card rounded-3xl p-5"><p class="text-xs font-black uppercase text-slate-400">Remitted (Paid)</p><h3 class="text-3xl font-black text-green-600">{{ fmt(paid) }}</h3></div>
                <div class="card rounded-3xl p-5"><p class="text-xs font-black uppercase text-slate-400">Pending Remittance</p><h3 class="text-3xl font-black text-amber-500">{{ fmt(pending) }}</h3></div>
                <div class="card rounded-3xl p-5"><p class="text-xs font-black uppercase text-slate-400">Pending vs Paid</p><div class="mx-auto mt-2 h-24 w-24 rounded-full" :style="chartStyle"></div></div>
            </div>

            <div class="card rounded-3xl p-6">
                <div class="mb-5 flex flex-col gap-3 md:flex-row">
                    <input v-model="search" class="flex-1 rounded-2xl border border-slate-200 px-4 py-3" placeholder="Search event, organizer, agency or jurisdiction..." />
                    <select v-model="status" class="rounded-2xl border border-slate-200 px-4 py-3">
                        <option>All</option>
                        <option>Pending</option>
                        <option>Paid</option>
                        <option>Review</option>
                    </select>
                    <button class="rounded-2xl border px-5 py-3 font-black" @click="exportCSV">CSV</button>
                    <button class="rounded-2xl border px-5 py-3 font-black" @click="printPage">Print</button>
                </div>
                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-xs uppercase text-slate-500">
                            <tr>
                                <th class="py-3">Event Name</th>
                                <th>Organizer</th>
                                <th>Jur.</th>
                                <th>Agency</th>
                                <th>Gross Sales</th>
                                <th>Tax Collected</th>
                                <th>Txns</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="report in filtered" :key="report.id" class="border-t">
                                <td class="py-3 font-black">{{ report.event }}</td>
                                <td>{{ report.organizer }}</td>
                                <td>{{ report.jurisdiction }}</td>
                                <td>{{ report.agency_name }}</td>
                                <td>{{ fmt(report.gross) }}</td>
                                <td class="font-black">{{ fmt(report.tax) }}</td>
                                <td>{{ num(report.transactions) }}</td>
                                <td><span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700">{{ report.status }}</span></td>
                            </tr>
                            <tr v-if="!filtered.length">
                                <td colspan="8" class="py-12 text-center text-sm font-bold text-slate-400">No remittance audit records found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </TaxLayout>
</template>
