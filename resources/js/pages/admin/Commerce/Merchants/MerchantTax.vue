<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Receipt, Download, Globe2, FileText } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Prototype Data
const mxMerchants = ref([
    { id: 1, name: 'SuperValue Nassau', country: 'Bahamas', region: 'Caribbean', vat: 10, volume: 420000, txns: 8500, taxId: 'BS-TIN-4821' },
    { id: 2, name: 'Kingston Gas Mart', country: 'Jamaica', region: 'Caribbean', vat: 15, volume: 380000, txns: 6400, taxId: 'JM-TRN-1190' },
    { id: 3, name: 'Caribbean Pharmacy', country: 'Trinidad & Tobago', region: 'Caribbean', vat: 12.5, volume: 260000, txns: 4200, taxId: 'TT-BIR-7733' },
    { id: 12, name: 'São Paulo Retail', country: 'Brazil', region: 'Latin America', vat: 17, volume: 520000, txns: 9200, taxId: 'BR-CNPJ-7720' },
    { id: 10, name: 'CDMX Mercado', country: 'Mexico', region: 'Latin America', vat: 16, volume: 410000, txns: 7800, taxId: 'MX-RFC-4410' },
]);

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);

const taxSummary = computed(() => {
    const byC: any = {};
    mxMerchants.value.forEach(m => {
        if (!byC[m.country]) byC[m.country] = { n: 0, gtv: 0, vat: m.vat, tax: 0 };
        byC[m.country].n++;
        byC[m.country].gtv += m.volume;
        byC[m.country].tax += (m.volume * (m.vat / 100));
    });
    return Object.keys(byC).map(c => ({ country: c, ...byC[c] })).sort((a, b) => b.gtv - a.gtv);
});

const headerMetrics = computed(() => ({
    gtv: '$2.8M',
    revenue: '$840k',
    bank: '$2.1M',
    net: '$420k',
    users: '1.2M',
    merchants: mxMerchants.value.length.toString(),
    organizers: '2.1k',
    countries: '15'
}));

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Merchant Tax Reports" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="mxTax" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Merchant Tax Reports" :countries="[]" :metrics="headerMetrics" @toggle-sidebar="toggleSidebar" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-3xl font-black text-slate-900 leading-tight flex items-center gap-3"><Receipt class="w-8 h-8 text-sky-600" /> Merchant Tax Reports</h2>
                        <p class="text-slate-500 font-medium mt-1">Monitor VAT/Tax collection and filing status across the merchant network.</p>
                    </div>
                    <button class="rounded-2xl bg-slate-950 text-white px-6 py-3.5 font-black flex items-center gap-2 shadow-lg transition active:scale-95 uppercase tracking-widest text-[11px]">
                        <Download class="w-4 h-4" /> Export All
                    </button>
                </div>

                <!-- Summary Table -->
                <div class="card rounded-3xl bg-white border border-slate-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-50">
                        <h4 class="font-black text-slate-800 uppercase tracking-widest text-[11px] opacity-60">Tax Summary by Country</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-[#f8fafc] text-[10px] font-black uppercase text-slate-400 tracking-[.08em]">
                                <tr>
                                    <th class="px-6 py-4">Country</th>
                                    <th class="px-6 py-4 text-center">Merchants</th>
                                    <th class="px-6 py-4">Taxable GTV</th>
                                    <th class="px-6 py-4">VAT Rate</th>
                                    <th class="px-6 py-4 text-right">Est. Tax (VAT)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm font-bold">
                                <tr v-for="s in taxSummary" :key="s.country" class="hover:bg-slate-50 transition group">
                                    <td class="px-6 py-5 flex items-center gap-2 text-slate-900">
                                        <Globe2 class="w-4 h-4 text-slate-300" /> {{ s.country }}
                                    </td>
                                    <td class="px-6 py-5 text-center text-slate-500">{{ s.n }}</td>
                                    <td class="px-6 py-5 font-black text-slate-800">{{ fmt(s.gtv) }}</td>
                                    <td class="px-6 py-5 text-slate-400 font-black uppercase tracking-tighter">{{ s.vat }}%</td>
                                    <td class="px-6 py-5 text-right font-black text-sky-600">{{ fmt(s.tax) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Merchant Table -->
                <div class="card rounded-3xl bg-white border border-slate-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-50">
                        <h4 class="font-black text-slate-800 uppercase tracking-widest text-[11px] opacity-60">Per-Merchant Tax Ledger</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-[#f8fafc] text-[10px] font-black uppercase text-slate-400 tracking-[.08em]">
                                <tr>
                                    <th class="px-6 py-4">Merchant</th>
                                    <th class="px-6 py-4">Tax ID / TIN</th>
                                    <th class="px-6 py-4">Taxable GTV</th>
                                    <th class="px-6 py-4 text-right">Est. Tax</th>
                                    <th class="px-6 py-4 text-right">Report</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm font-bold">
                                <tr v-for="m in mxMerchants" :key="m.id" class="hover:bg-slate-50 transition group">
                                    <td class="px-6 py-5">
                                        <div class="font-black text-slate-900">{{ m.name }}</div>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">{{ m.country }}</div>
                                    </td>
                                    <td class="px-6 py-5 font-mono text-xs text-slate-400">{{ m.taxId }}</td>
                                    <td class="px-6 py-5 font-black text-slate-800">{{ fmt(m.volume) }}</td>
                                    <td class="px-6 py-5 text-right font-black text-sky-600">{{ fmt(m.volume * (m.vat / 100)) }}</td>
                                    <td class="px-6 py-5 text-right">
                                        <button class="rounded-xl border border-slate-200 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-900 hover:text-white transition flex items-center gap-2 ml-auto">
                                            <FileText class="w-3.5 h-3.5" /> Generate
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
