<script setup lang="ts">
import TaxLayout from './components/TaxLayout.vue';
import { computed, ref } from 'vue';
const props = defineProps<{ initialUnits: any[]; initialCountries: any[] }>();
const search = ref('');
const jurisdictions = ref([
    { jurisdiction: 'Bahamas VAT', tax: 'VAT', rate: 10, api: 'Connected', collected: 18420, remitted: 9200, frequency: 'Monthly' },
    { jurisdiction: 'Jamaica GCT', tax: 'GCT', rate: 15, api: 'Connected', collected: 22100, remitted: 13000, frequency: 'Monthly' },
    { jurisdiction: 'Florida Sales Tax', tax: 'Sales Tax', rate: 7.5, api: 'Pending', collected: 8700, remitted: 0, frequency: 'Monthly' },
    { jurisdiction: 'Canada GST/HST', tax: 'GST/HST', rate: 13, api: 'Connected', collected: 15400, remitted: 9000, frequency: 'Quarterly' },
]);
const filtered = computed(() => jurisdictions.value.filter(j => JSON.stringify(j).toLowerCase().includes(search.value.toLowerCase())));
const totals = computed(() => ({ collected: jurisdictions.value.reduce((s, j) => s + j.collected, 0), remitted: jurisdictions.value.reduce((s, j) => s + j.remitted, 0), connected: jurisdictions.value.filter(j => j.api === 'Connected').length }));
const fileAll = () => { jurisdictions.value.forEach(j => j.remitted = j.collected); };
</script>
<template>
    <TaxLayout title="Tax Authority APIs" active-id="taxAuthorityCommand" :initial-units="props.initialUnits" :initial-countries="props.initialCountries" v-slot="{ fmt }">
        <div class="space-y-6"><div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between"><div><h3 class="text-3xl font-black text-cyan-600">Tax Authority Integrations</h3><p class="text-slate-500">Connect to each jurisdiction's tax API across the Caribbean, United States & Canada - collect consumer tax, then file & remit automatically once collected.</p></div><button @click="fileAll" class="rounded-2xl bg-cyan-600 px-5 py-3 font-black text-white">File & Remit All Due</button></div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-5"><div class="card metric dark rounded-3xl p-5"><p class="text-sm text-slate-300">Tax Collected (period)</p><h3 class="text-2xl font-black">{{ fmt(totals.collected) }}</h3></div><div class="card rounded-3xl p-5"><p class="text-sm text-slate-500">Remitted</p><h3 class="text-2xl font-black text-emerald-600">{{ fmt(totals.remitted) }}</h3></div><div class="card rounded-3xl p-5"><p class="text-sm text-slate-500">Due to Authorities</p><h3 class="text-2xl font-black text-amber-600">{{ fmt(totals.collected - totals.remitted) }}</h3></div><div class="card rounded-3xl p-5"><p class="text-sm text-slate-500">Jurisdictions</p><h3 class="text-2xl font-black">{{ jurisdictions.length }}</h3></div><div class="card rounded-3xl p-5"><p class="text-sm text-slate-500">APIs Connected</p><h3 class="text-2xl font-black text-cyan-600">{{ totals.connected }}</h3></div></div>
        <div class="card rounded-3xl p-6"><div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center"><h4 class="flex-1 font-black">Jurisdiction Tax APIs</h4><input v-model="search" class="rounded-2xl border border-slate-200 px-4 py-2 md:w-72" placeholder="Search jurisdiction..." /></div><div class="scrollbar overflow-x-auto"><table class="w-full text-left"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-2">Jurisdiction</th><th>Tax</th><th>Rate</th><th>API Status</th><th>Collected</th><th>Remitted</th><th>Due</th><th>Frequency</th><th>Actions</th></tr></thead><tbody><tr v-for="j in filtered" :key="j.jurisdiction" class="border-t"><td class="py-3 font-black">{{ j.jurisdiction }}</td><td>{{ j.tax }}</td><td>{{ j.rate }}%</td><td><span class="rounded-full px-3 py-1 text-xs font-black" :class="j.api === 'Connected' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'">{{ j.api }}</span></td><td>{{ fmt(j.collected) }}</td><td>{{ fmt(j.remitted) }}</td><td class="font-black text-amber-600">{{ fmt(j.collected - j.remitted) }}</td><td>{{ j.frequency }}</td><td class="font-black text-cyan-600">Sync</td></tr></tbody></table></div></div></div>
    </TaxLayout>
</template>