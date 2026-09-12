<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import {
    Plus,
    X,
    WalletCards,
    ChevronDown,
    ChevronRight,
    Search,
} from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

import '@/../../resources/css/new_admin.css';

type Unit = {
    key: string;
    name: string;
    platformRate: number;
    bankRate: number;
    costRate: number;
};

type Country = {
    country: string;
    region: string;
    users: number;
    merchants: number;
    organizers: number;
    [key: string]: string | number;
};

type Employee = {
    id: string;
    name: string;
    role: string;
    type: 'Executive' | 'Staff' | 'Contractor';
    dept: string;
    country: string;
    gross: number;
    bonus: number;
    bank: string;
    status: 'Active' | 'Paused';
};

type PayrollRun = {
    id: string;
    period: string;
    headcount: number;
    gross: number;
    deductions: number;
    net: number;
    method: string;
    status: string;
    date: string;
};

const props = defineProps<{
    initialUnits?: Unit[];
    initialCountries?: Country[];
}>();

const SCOTIA_SHARE = 0.4;

const fallbackUnits: Unit[] = [
    { key: 'tickets', name: 'Ticket Sales', platformRate: 0.065, bankRate: 0, costRate: 0.01 },
    { key: 'subscriptions', name: 'Subscriptions', platformRate: 1, bankRate: 0, costRate: 0.04 },
    { key: 'marketplace', name: 'Marketplace', platformRate: 0.05, bankRate: 0, costRate: 0.01 },
    { key: 'eats', name: 'LinkUp Eats', platformRate: 0.075, bankRate: 0, costRate: 0.025 },
    { key: 'merchantPay', name: 'Merchant Pay', platformRate: 0.02, bankRate: 0, costRate: 0.007 },
    { key: 'wallet', name: 'Wallet & Money Movement', platformRate: 0.025, bankRate: 0.0175, costRate: 0.008 },
    { key: 'live', name: 'LinkUp Live', platformRate: 0.5, bankRate: 0, costRate: 0.08 },
    { key: 'ads', name: 'Advertising Revenue', platformRate: 1, bankRate: 0, costRate: 0.12 },
];

const fallbackCountries: Country[] = [
    { country: 'Bahamas', region: 'Local', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000 },
    { country: 'Jamaica', region: 'Regional', users: 280000, merchants: 1200, organizers: 250, tickets: 720000, subscriptions: 90000, marketplace: 410000, eats: 650000, merchantPay: 1600000, wallet: 950000, live: 180000, ads: 45000 },
    { country: 'Trinidad & Tobago', region: 'Regional', users: 190000, merchants: 900, organizers: 190, tickets: 610000, subscriptions: 70000, marketplace: 380000, eats: 520000, merchantPay: 1350000, wallet: 840000, live: 160000, ads: 39000 },
    { country: 'Barbados', region: 'Regional', users: 70000, merchants: 380, organizers: 95, tickets: 330000, subscriptions: 42000, marketplace: 190000, eats: 260000, merchantPay: 720000, wallet: 460000, live: 85000, ads: 21000 },
    { country: 'Dominican Republic', region: 'Regional', users: 220000, merchants: 1000, organizers: 210, tickets: 520000, subscriptions: 68000, marketplace: 310000, eats: 430000, merchantPay: 1100000, wallet: 710000, live: 150000, ads: 36000 },
    { country: 'United States', region: 'International', users: 450000, merchants: 2400, organizers: 410, tickets: 1280000, subscriptions: 180000, marketplace: 820000, eats: 0, merchantPay: 2500000, wallet: 1750000, live: 420000, ads: 160000 },
    { country: 'Canada', region: 'International', users: 260000, merchants: 1400, organizers: 260, tickets: 840000, subscriptions: 130000, marketplace: 560000, eats: 0, merchantPay: 1600000, wallet: 1100000, live: 300000, ads: 110000 },
    { country: 'Brazil', region: 'International', users: 370000, merchants: 1900, organizers: 350, tickets: 960000, subscriptions: 150000, marketplace: 640000, eats: 0, merchantPay: 1850000, wallet: 1300000, live: 360000, ads: 130000 },
    { country: 'Colombia', region: 'International', users: 240000, merchants: 1300, organizers: 240, tickets: 690000, subscriptions: 105000, marketplace: 470000, eats: 0, merchantPay: 1250000, wallet: 860000, live: 240000, ads: 90000 },
];

const payrollTax = ref<Record<string, { inc: number, emp: number, er: number }>>({
    'Bahamas': { inc: 0, emp: 3.9, er: 5.9 },
    'Jamaica': { inc: 25, emp: 3, er: 3.5 },
    'Trinidad & Tobago': { inc: 25, emp: 3.4, er: 5.6 },
    'United States': { inc: 22, emp: 7.65, er: 7.65 },
    'Dominican Republic': { inc: 15, emp: 5.91, er: 7.1 },
    'Colombia': { inc: 19, emp: 8, er: 12 },
    'Mexico': { inc: 20, emp: 2.4, er: 7 },
    'Brazil': { inc: 27.5, emp: 11, er: 20 }
});

const initialEmployees: Employee[] = [
    { id: 'EMP-001', name: 'Cassius Stuart', role: 'Chief Executive Officer', type: 'Executive', dept: 'Executive', country: 'Bahamas', gross: 18000, bonus: 0, bank: 'Scotiabank ••2290', status: 'Active' },
    { id: 'EMP-002', name: 'Kayley Stuart', role: 'Chief Operating Officer', type: 'Executive', dept: 'Executive', country: 'Bahamas', gross: 14000, bonus: 0, bank: 'Scotiabank ••2291', status: 'Active' },
    { id: 'EMP-003', name: 'Maria Gomez', role: 'Chief Compliance Officer', type: 'Executive', dept: 'Compliance', country: 'Colombia', gross: 11000, bonus: 0, bank: 'Scotiabank ••4410', status: 'Active' },
    { id: 'EMP-004', name: 'David Knowles', role: 'Chief Financial Officer', type: 'Executive', dept: 'Finance', country: 'Bahamas', gross: 12500, bonus: 0, bank: 'Scotiabank ••2293', status: 'Active' },
    { id: 'EMP-005', name: 'Andre Charles', role: 'Lead Engineer', type: 'Staff', dept: 'Engineering', country: 'Jamaica', gross: 7200, bonus: 0, bank: 'Scotiabank ••5521', status: 'Active' },
    { id: 'EMP-006', name: 'Simone Clarke', role: 'Backend Engineer', type: 'Staff', dept: 'Engineering', country: 'Jamaica', gross: 6100, bonus: 0, bank: 'Scotiabank ••5522', status: 'Active' },
    { id: 'EMP-007', name: 'Carlos Vega', role: 'Settlement Manager', type: 'Staff', dept: 'Finance', country: 'Mexico', gross: 5400, bonus: 0, bank: 'Scotiabank ••7010', status: 'Active' },
    { id: 'EMP-008', name: 'Aaliyah Mohammed', role: 'Support Lead', type: 'Staff', dept: 'Support', country: 'Trinidad & Tobago', gross: 4200, bonus: 0, bank: 'Scotiabank ••3380', status: 'Active' },
    { id: 'EMP-009', name: 'Lucas Oliveira', role: 'Marketing Manager', type: 'Staff', dept: 'Marketing', country: 'Brazil', gross: 4800, bonus: 0, bank: 'Scotiabank ••9120', status: 'Active' },
    { id: 'EMP-010', name: 'María Rodríguez', role: 'Operations Analyst', type: 'Staff', dept: 'Operations', country: 'Dominican Republic', gross: 3900, bonus: 0, bank: 'Scotiabank ••6650', status: 'Active' },
    { id: 'EMP-011', name: 'Nadia Williams', role: 'Content Lead', type: 'Staff', dept: 'Marketing', country: 'United States', gross: 6800, bonus: 0, bank: 'Scotiabank ••1180', status: 'Active' },
    { id: 'EMP-012', name: 'Diego Torres', role: 'QA Engineer (Contract)', type: 'Contractor', dept: 'Engineering', country: 'Mexico', gross: 3600, bonus: 0, bank: 'Scotiabank ••7011', status: 'Active' }
];

const employees = ref<Employee[]>(JSON.parse(localStorage.getItem('linkupEmployees') || JSON.stringify(initialEmployees)));
const payrollRuns = ref<PayrollRun[]>(JSON.parse(localStorage.getItem('linkupPayrollRuns') || '[]'));
const payrollState = ref({ status: 'Draft' });

const sidebarVisible = ref(true);
const filters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });
const pySearch = ref('');
const showEmpModal = ref(false);
const empForm = ref<Partial<Employee>>({});

const countries = computed(() => (props.initialCountries?.length ? props.initialCountries : fallbackCountries));
const units = computed(() => (props.initialUnits?.length ? props.initialUnits : fallbackUnits));

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n || 0);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n || 0));

const scale = computed(() => {
    const period = filters.value.period;
    if (period === 'Today') return 1 / 30;
    if (period === 'Weekly') return 0.25;
    if (period === 'Quarterly') return 3;
    if (period === 'Yearly') return 12;
    if (period === '5-Year') return 60;
    return 1;
});

const filteredCountries = computed(() =>
    countries.value.filter((country) => {
        const regionOk = filters.value.region === 'All' || country.region === filters.value.region;
        const countryOk = filters.value.country === 'All Countries' || country.country === filters.value.country;
        return regionOk && countryOk;
    }),
);

const countryFin = (country: Country) => {
    return units.value.reduce(
        (total, unit) => {
            const volume = Number(country[unit.key] || 0) * scale.value;
            total.gross += volume;
            total.platform += volume * unit.platformRate;
            total.bank += volume * unit.bankRate;
            total.cost += volume * unit.costRate;
            return total;
        },
        { gross: 0, platform: 0, bank: 0, cost: 0 },
    );
};

const platformTotals = computed(() => {
    return filteredCountries.value.reduce(
        (total, country) => {
            const fin = countryFin(country);
            total.gross += fin.gross;
            total.platform += fin.platform;
            total.bank += fin.bank;
            total.cost += fin.cost;
            total.users += Number(country.users || 0) * scale.value;
            total.merchants += Number(country.merchants || 0);
            total.organizers += Number(country.organizers || 0);
            return total;
        },
        { gross: 0, platform: 0, bank: 0, cost: 0, users: 0, merchants: 0, organizers: 0 },
    );
});

const ribbonMetrics = computed(() => ({
    gtv: fmt(platformTotals.value.gross),
    linkupRev: fmt(platformTotals.value.platform),
    procPool: fmt(platformTotals.value.bank),
    netProfit: fmt(platformTotals.value.platform - platformTotals.value.cost + platformTotals.value.bank * (1 - SCOTIA_SHARE)),
    users: num(platformTotals.value.users),
    merchants: num(platformTotals.value.merchants),
    organizers: num(platformTotals.value.organizers),
    countries: num(filteredCountries.value.length),
}));

// Payroll Logic
const getTax = (country: string) => payrollTax.value[country] || { inc: 15, emp: 5, er: 7 };
const getGrossPay = (e: Employee) => (e.gross || 0) + (e.bonus || 0);
const getDeductions = (e: Employee) => getGrossPay(e) * (getTax(e.country).inc + getTax(e.country).emp) / 100;
const getNetPay = (e: Employee) => getGrossPay(e) - getDeductions(e);
const getEmployerCost = (e: Employee) => getGrossPay(e) * getTax(e.country).er / 100;

const pyCurrentPeriodLabel = () => {
    const d = new Date();
    return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][d.getMonth()] + ' ' + d.getFullYear();
};

const filteredEmployees = computed(() => {
    const q = pySearch.value.toLowerCase().trim();
    return employees.value.filter((e) => {
        const regionOk = filters.value.region === 'All' || countries.value.find(c => c.country === e.country)?.region === filters.value.region;
        const countryOk = filters.value.country === 'All Countries' || e.country === filters.value.country;
        const searchOk = !q || (e.name + ' ' + e.role + ' ' + e.dept + ' ' + e.country + ' ' + e.type).toLowerCase().includes(q);
        return regionOk && countryOk && searchOk;
    });
});

const payrollKpis = computed(() => {
    const active = filteredEmployees.value.filter(e => e.status === 'Active');
    const gross = active.reduce((a, e) => a + getGrossPay(e), 0);
    const net = active.reduce((a, e) => a + getNetPay(e), 0);
    const ded = active.reduce((a, e) => a + getDeductions(e), 0);
    const erCost = active.reduce((a, e) => a + getEmployerCost(e), 0);
    const execs = active.filter(e => e.type === 'Executive').length;
    const paused = filteredEmployees.value.filter(e => e.status !== 'Active').length;

    return [
        { label: 'Headcount', val: active.length, sub: `${execs} executives${paused ? ' · ' + paused + ' paused' : ''}`, color: '' },
        { label: 'Gross Payroll', val: fmt(gross * scale.value), sub: 'Total liability', color: '' },
        { label: 'Net to Bank', val: fmt(net * scale.value), sub: 'Disbursement amount', color: 'text-green-600' },
        { label: 'Tax + Social Withheld', val: fmt(ded * scale.value), sub: 'Government liabilities', color: 'text-amber-600' },
        { label: 'Employer Contributions', val: fmt(erCost * scale.value), sub: 'Additional social cost', color: 'text-sky-600' }
    ];
});

// Accordion
const openGroups = ref<Record<string, boolean>>({});
const toggleGroup = (key: string) => { openGroups.value[key] = !openGroups.value[key]; };
const isGroupOpen = (key: string, def = false) => openGroups.value[key] !== undefined ? openGroups.value[key] : def;

const groupedEmployees = computed(() => {
    const rm: Record<string, Record<string, Employee[]>> = {};
    filteredEmployees.value.forEach((it) => {
        const c = it.country || 'Other';
        const r = countries.value.find(x => x.country === c)?.region || 'Other';
        if (!rm[r]) rm[r] = {};
        if (!rm[r][c]) rm[r][c] = [];
        rm[r][c].push(it);
    });
    return rm;
});

// Modals
const openEmployee = (id?: string) => {
    const e = id ? employees.value.find(x => x.id === id) : null;
    empForm.value = e ? { ...e } : { type: 'Staff', dept: 'Engineering', country: 'Bahamas', status: 'Active', gross: 0, bonus: 0, bank: 'Scotiabank ••' };
    showEmpModal.value = true;
};

const saveEmployee = () => {
    if (!empForm.value.name) return alert('Name is required');
    if (empForm.value.id) {
        const idx = employees.value.findIndex(x => x.id === empForm.value.id);
        if (idx > -1) employees.value[idx] = { ...empForm.value } as Employee;
    } else {
        const nextId = 'EMP-' + String(employees.value.length + 1).padStart(3, '0');
        employees.value.push({ ...empForm.value, id: nextId } as Employee);
    }
    localStorage.setItem('linkupEmployees', JSON.stringify(employees.value));
    showEmpModal.value = false;
};

const deleteEmployee = (id: string) => {
    if (confirm('Delete employee?')) {
        employees.value = employees.value.filter(x => x.id !== id);
        localStorage.setItem('linkupEmployees', JSON.stringify(employees.value));
    }
};

const toggleStatus = (id: string) => {
    const e = employees.value.find(x => x.id === id);
    if (e) {
        e.status = e.status === 'Active' ? 'Paused' : 'Active';
        localStorage.setItem('linkupEmployees', JSON.stringify(employees.value));
    }
};

const approveRun = () => {
    payrollState.value.status = 'Approved';
};

const payRun = () => {
    const active = employees.value.filter(e => e.status === 'Active');
    const gross = active.reduce((a, e) => a + getGrossPay(e), 0);
    const ded = active.reduce((a, e) => a + getDeductions(e), 0);
    const net = active.reduce((a, e) => a + getNetPay(e), 0);

    payrollRuns.value.unshift({
        id: 'PR-' + new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0'),
        period: pyCurrentPeriodLabel(),
        headcount: active.length,
        gross: Math.round(gross),
        deductions: Math.round(ded),
        net: Math.round(net),
        method: 'Scotiabank Batch ••2290',
        status: 'Paid',
        date: new Date().toISOString().slice(0, 10)
    });

    employees.value.forEach(e => { e.bonus = 0; });
    payrollState.value.status = 'Draft';
    localStorage.setItem('linkupPayrollRuns', JSON.stringify(payrollRuns.value));
    localStorage.setItem('linkupEmployees', JSON.stringify(employees.value));
};

const handleFilterChange = (nextFilters: { region: string; country: string; period: string }) => {
    filters.value = nextFilters;
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Payroll" />

    <div class="flex min-h-screen">
        <NewAppSidebar v-show="sidebarVisible" active-id="payrollCommand" />

        <main class="flex-1 overflow-x-hidden transition-all duration-300">
            <NewAppHeader
                title="Payroll"
                :countries="countries"
                :metrics="ribbonMetrics"
                @toggle-sidebar="sidebarVisible = !sidebarVisible"
                @filter-change="handleFilterChange"
                @search="pySearch = $event"
            />

            <section class="space-y-6 p-5 lg:p-8">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-3xl font-black text-slate-950">Payroll</h3>
                        <p class="text-slate-500 max-w-2xl">Pay executives, staff & contractors directly to their Scotiabank accounts. Gross-to-net with country tax & social contributions, approvals, and a batch run to the bank.</p>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                    <div v-for="kpi in payrollKpis" :key="kpi.label" class="card rounded-3xl p-5">
                        <p class="text-slate-500 font-bold text-sm">{{ kpi.label }}</p>
                        <h3 class="text-3xl font-black mt-1" :class="kpi.color">{{ kpi.val }}</h3>
                        <p class="text-xs text-slate-400 mt-1">{{ kpi.sub }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 2xl:grid-cols-3 gap-6">
                    <!-- Employee Roster -->
                    <div class="2xl:col-span-2 card rounded-3xl p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                            <h3 class="text-xl font-black">Employee Roster</h3>
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="relative w-full sm:w-64">
                                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                    <input v-model="pySearch" class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-2 text-sm font-bold" placeholder="Search name, role, dept...">
                                </div>
                                <button @click="openEmployee()" class="rounded-2xl bg-purple-600 text-white px-4 py-2 font-black text-sm hover:bg-purple-700 transition">+ Add Employee</button>
                            </div>
                        </div>

                        <div class="overflow-x-auto scrollbar">
                            <table class="w-full text-left">
                                <thead class="text-[10px] uppercase text-slate-400 font-black tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="py-3">Employee</th>
                                        <th>Dept</th>
                                        <th>Gross</th>
                                        <th>Deductions</th>
                                        <th>Net Pay</th>
                                        <th>Scotiabank</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="(countries, region) in groupedEmployees" :key="region">
                                        <tr class="cursor-pointer bg-slate-800 text-white" @click="toggleGroup('R:' + region)">
                                            <td colspan="7" class="py-2 px-4 font-black">
                                                <span class="flex items-center gap-2">
                                                    <ChevronDown v-if="isGroupOpen('R:' + region, true)" class="h-4 w-4" />
                                                    <ChevronRight v-else class="h-4 w-4" />
                                                    🌎 {{ region }} <span class="opacity-60">({{ Object.values(countries).flat().length }})</span>
                                                </span>
                                            </td>
                                        </tr>

                                        <template v-if="isGroupOpen('R:' + region, true)">
                                            <template v-for="(items, country) in countries" :key="country">
                                                <tr class="cursor-pointer bg-slate-100" @click="toggleGroup('C:' + country)">
                                                    <td colspan="7" class="py-2 px-8 font-black text-slate-700">
                                                        <span class="flex items-center gap-2">
                                                            <ChevronDown v-if="isGroupOpen('C:' + country)" class="h-4 w-4" />
                                                            <ChevronRight v-else class="h-4 w-4" />
                                                            {{ country }} <span class="opacity-50">({{ items.length }})</span>
                                                        </span>
                                                    </td>
                                                </tr>

                                                <tr v-for="e in items" v-show="isGroupOpen('C:' + country)" :key="e.id" class="border-t hover:bg-slate-50 transition" :class="{ 'opacity-50': e.status !== 'Active' }">
                                                    <td class="py-4 px-4">
                                                        <div class="font-black flex items-center gap-2">
                                                            {{ e.name }}
                                                            <span v-if="e.status === 'Paused'" class="rounded-full px-2 py-0.5 bg-rose-50 text-rose-600 text-[10px] font-black uppercase">Paused</span>
                                                        </div>
                                                        <div class="text-xs text-slate-500 font-bold mt-0.5">
                                                            {{ e.role }} • <span class="px-1.5 py-0.5 rounded bg-slate-100">{{ e.type }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="text-sm font-bold text-slate-600">{{ e.dept }}</td>
                                                    <td class="font-black">
                                                        {{ fmt(getGrossPay(e)) }}
                                                        <div v-if="e.bonus" class="text-[10px] text-emerald-600 font-bold">incl. {{ fmt(e.bonus) }} bonus</div>
                                                    </td>
                                                    <td class="text-rose-600 font-bold text-sm">-{{ fmt(getDeductions(e)) }}</td>
                                                    <td class="font-black text-green-600">{{ fmt(getNetPay(e)) }}</td>
                                                    <td class="text-xs font-mono font-bold text-slate-500">{{ e.bank }}</td>
                                                    <td class="py-4 px-4 text-right">
                                                        <div class="flex justify-end gap-1">
                                                            <button @click="openEmployee(e.id)" class="px-2.5 py-1 text-[10px] font-black uppercase rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100">Edit</button>
                                                            <button @click="toggleStatus(e.id)" class="px-2.5 py-1 text-[10px] font-black uppercase rounded-lg bg-slate-50 text-slate-600 hover:bg-slate-100">{{ e.status === 'Active' ? 'Pause' : 'Activate' }}</button>
                                                            <button @click="deleteEmployee(e.id)" class="px-2.5 py-1 text-[10px] font-black uppercase rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100">X</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </template>
                                        </template>
                                    </template>
                                    <tr v-if="filteredEmployees.length === 0">
                                        <td colspan="7" class="py-12 text-center text-slate-400 font-bold">No employees found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Run Payroll Box -->
                    <div class="card rounded-3xl p-6 h-fit">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-10 w-10 rounded-xl bg-red-600 grid place-items-center text-white font-black text-xl">S</div>
                            <h3 class="text-xl font-black">Run Payroll</h3>
                        </div>
                        <p class="text-sm text-slate-500 mb-6 font-medium">Approve the current period, then disburse a single batch to staff Scotiabank accounts.</p>

                        <div class="rounded-2xl bg-slate-50 p-5 space-y-3 border border-slate-100">
                            <div class="flex justify-between items-center"><span class="text-slate-500 font-bold text-sm">Period</span><span class="font-black">{{ pyCurrentPeriodLabel() }}</span></div>
                            <div class="flex justify-between items-center"><span class="text-slate-500 font-bold text-sm">Active Employees</span><span class="font-black">{{ employees.filter(e => e.status === 'Active').length }}</span></div>
                            <div class="flex justify-between items-center"><span class="text-slate-500 font-bold text-sm">Gross</span><span class="font-black">{{ fmt(employees.filter(e => e.status === 'Active').reduce((a, e) => a + getGrossPay(e), 0)) }}</span></div>
                            <div class="flex justify-between items-center"><span class="text-slate-500 font-bold text-sm">Deductions</span><span class="font-black text-rose-600">-{{ fmt(employees.filter(e => e.status === 'Active').reduce((a, e) => a + getDeductions(e), 0)) }}</span></div>
                            <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                                <span class="text-slate-500 font-black text-sm uppercase">Net to disburse</span>
                                <span class="font-black text-green-600 text-2xl tracking-tighter">{{ fmt(employees.filter(e => e.status === 'Active').reduce((a, e) => a + getNetPay(e), 0)) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-bold text-sm">Status</span>
                                <span class="font-black uppercase text-xs px-2 py-1 rounded" :class="{ 'bg-slate-200 text-slate-600': payrollState.status === 'Draft', 'bg-sky-100 text-sky-600': payrollState.status === 'Approved' }">{{ payrollState.status }}</span>
                            </div>
                        </div>

                        <div class="mt-6 space-y-3">
                            <button v-if="payrollState.status === 'Draft'" @click="approveRun" class="w-full rounded-2xl bg-slate-950 text-white px-6 py-4 font-black hover:bg-slate-800 transition shadow-lg shadow-slate-200">Approve Payroll</button>
                            <template v-if="payrollState.status === 'Approved'">
                                <button @click="payRun" class="w-full rounded-2xl bg-red-600 text-white px-6 py-4 font-black flex items-center justify-center gap-2 hover:bg-red-700 transition shadow-lg shadow-red-100">
                                    <span class="h-5 w-5 rounded bg-white text-red-600 grid place-items-center text-[10px] font-black">S</span> Disburse to Scotiabank
                                </button>
                                <button @click="payrollState.status = 'Draft'" class="w-full rounded-2xl bg-slate-100 text-slate-700 px-6 py-3 font-black text-sm hover:bg-slate-200 transition">Revert to Draft</button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- History -->
                <div class="card rounded-3xl p-6">
                    <h3 class="text-xl font-black mb-6">Payroll History</h3>
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left">
                            <thead class="text-[10px] uppercase text-slate-400 font-black tracking-widest border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-4">Run ID</th>
                                    <th>Period</th>
                                    <th>Headcount</th>
                                    <th>Gross</th>
                                    <th>Deductions</th>
                                    <th>Net Paid</th>
                                    <th>Method</th>
                                    <th class="text-right px-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="run in payrollRuns" :key="run.id" class="border-t hover:bg-slate-50 transition">
                                    <td class="py-4 px-4 font-black">{{ run.id }}</td>
                                    <td class="font-bold text-slate-700">{{ run.period }}</td>
                                    <td class="font-bold text-slate-500">{{ run.headcount }} staff</td>
                                    <td class="font-black">{{ fmt(run.gross) }}</td>
                                    <td class="text-rose-500 font-bold">-{{ fmt(run.deductions) }}</td>
                                    <td class="font-black text-green-600">{{ fmt(run.net) }}</td>
                                    <td class="text-xs font-bold text-slate-400">{{ run.method }}</td>
                                    <td class="py-4 px-4 text-right">
                                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase bg-green-50 text-green-600 border border-green-100">{{ run.status }}</span>
                                    </td>
                                </tr>
                                <tr v-if="payrollRuns.length === 0">
                                    <td colspan="8" class="py-12 text-center text-slate-400 font-bold">No payroll runs found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Employee Modal -->
    <div v-if="showEmpModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 p-5 backdrop-blur-sm">
        <div class="w-full max-w-2xl overflow-hidden rounded-[40px] bg-white shadow-2xl animate-in zoom-in-95 duration-200">
            <!-- Modal Header -->
            <div class="p-8 bg-[#C026D3] text-white flex justify-between items-start relative">
                <div>
                    <h3 class="text-3xl font-black">{{ empForm.id ? 'Edit Employee' : 'Add Employee' }}</h3>
                    <p class="text-white/80 text-sm font-bold mt-1">Pay is calculated gross-to-net using the employee's country tax rates.</p>
                </div>
                <button @click="showEmpModal = false" class="text-white hover:text-white/80 transition p-2">
                    <X class="w-8 h-8" />
                </button>
            </div>

            <div class="p-8 space-y-6 max-h-[75vh] overflow-y-auto scrollbar">
                <!-- Form Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                    <!-- Full Name -->
                    <div class="space-y-2">
                        <label class="text-sm font-black text-slate-700 ml-1">Full Name *</label>
                        <input v-model="empForm.name" class="w-full rounded-2xl border border-slate-200 px-5 py-3.5 font-bold focus:ring-4 focus:ring-purple-50 outline-none transition bg-slate-50/30">
                    </div>
                    <!-- Job Title -->
                    <div class="space-y-2">
                        <label class="text-sm font-black text-slate-700 ml-1">Job Title / Role</label>
                        <input v-model="empForm.role" class="w-full rounded-2xl border border-slate-200 px-5 py-3.5 font-bold focus:ring-4 focus:ring-purple-50 outline-none transition bg-slate-50/30">
                    </div>
                    <!-- Type -->
                    <div class="space-y-2">
                        <label class="text-sm font-black text-slate-700 ml-1">Type</label>
                        <select v-model="empForm.type" class="w-full rounded-2xl border border-slate-200 px-5 py-3.5 font-bold focus:ring-4 focus:ring-purple-50 outline-none transition bg-slate-50/30 appearance-none">
                            <option>Executive</option><option>Staff</option><option>Contractor</option>
                        </select>
                    </div>
                    <!-- Department -->
                    <div class="space-y-2">
                        <label class="text-sm font-black text-slate-700 ml-1">Department</label>
                        <select v-model="empForm.dept" class="w-full rounded-2xl border border-slate-200 px-5 py-3.5 font-bold focus:ring-4 focus:ring-purple-50 outline-none transition bg-slate-50/30 appearance-none">
                            <option>Executive</option><option>Engineering</option><option>Finance</option><option>Compliance</option><option>Marketing</option><option>Support</option><option>Operations</option><option>HR</option>
                        </select>
                    </div>
                    <!-- Country -->
                    <div class="space-y-2">
                        <label class="text-sm font-black text-slate-700 ml-1">Country</label>
                        <select v-model="empForm.country" class="w-full rounded-2xl border border-slate-200 px-5 py-3.5 font-bold focus:ring-4 focus:ring-purple-50 outline-none transition bg-slate-50/30 appearance-none">
                            <option v-for="c in countries" :key="c.country">{{ c.country }}</option>
                        </select>
                    </div>
                    <!-- Status -->
                    <div class="space-y-2">
                        <label class="text-sm font-black text-slate-700 ml-1">Status</label>
                        <select v-model="empForm.status" class="w-full rounded-2xl border border-slate-200 px-5 py-3.5 font-bold focus:ring-4 focus:ring-purple-50 outline-none transition bg-slate-50/30 appearance-none">
                            <option>Active</option><option>Paused</option>
                        </select>
                    </div>
                    <!-- Monthly Gross Salary -->
                    <div class="space-y-2">
                        <label class="text-sm font-black text-slate-700 ml-1">Monthly Gross Salary ($)</label>
                        <input v-model.number="empForm.gross" type="number" class="w-full rounded-2xl border border-slate-200 px-5 py-3.5 font-bold focus:ring-4 focus:ring-purple-50 outline-none transition bg-slate-50/30">
                    </div>
                    <!-- One-Time Bonus -->
                    <div class="space-y-2">
                        <label class="text-sm font-black text-slate-700 ml-1">One-Time Bonus ($)</label>
                        <input v-model.number="empForm.bonus" type="number" class="w-full rounded-2xl border border-slate-200 px-5 py-3.5 font-bold focus:ring-4 focus:ring-purple-50 outline-none transition bg-slate-50/30">
                    </div>
                    <!-- Scotiabank Account -->
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-sm font-black text-slate-700 ml-1">Scotiabank Account</label>
                        <input v-model="empForm.bank" class="w-full rounded-2xl border border-slate-200 px-5 py-3.5 font-bold focus:ring-4 focus:ring-purple-50 outline-none transition bg-slate-50/30" placeholder="Scotiabank ••0000">
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-8 flex justify-end gap-4 items-center">
                <button @click="showEmpModal = false" class="px-10 py-3.5 rounded-2xl bg-slate-100 font-black text-slate-900 hover:bg-slate-200 transition">Cancel</button>
                <button @click="saveEmployee" class="px-10 py-3.5 rounded-2xl bg-[#0F172A] text-white font-black hover:bg-slate-800 transition shadow-lg">Save Employee</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.new-admin-body {
    background: radial-gradient(circle at top left, rgba(40, 168, 255, 0.12), transparent 30%),
                radial-gradient(circle at top right, rgba(217, 236, 16, 0.18), transparent 28%),
                linear-gradient(180deg, #f8fbff, #eef4fa);
}

.card {
    background: #fff;
    border: 1px solid #eaf0f7;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
}

.metric {
    position: relative;
    overflow: hidden;
}

.metric:after {
    content: "";
    position: absolute;
    right: -40px;
    top: -40px;
    width: 135px;
    height: 135px;
    border-radius: 999px;
    background: rgba(40, 168, 255, 0.09);
}

.metric.dark {
    background: linear-gradient(135deg, #07111f, #13233d);
    color: #fff;
}

.metric.dark:after {
    background: rgba(217, 236, 16, 0.14);
}

.scrollbar::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
}

.nav-active {
    background: linear-gradient(90deg, rgba(40, 168, 255, 0.15), rgba(217, 236, 16, 0.16));
    border-right: 4px solid #28A8FF;
}
</style>
