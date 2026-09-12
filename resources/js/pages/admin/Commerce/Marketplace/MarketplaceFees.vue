<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Receipt, Save, Info, LockKeyhole } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    shopFee: any;
    escrowSettings: any;
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const countries = ref(props.initialCountries);

const form = useForm({
    commission: props.shopFee?.percent ?? 5.00,
    processing_fee: props.escrowSettings?.processing_fee ?? 2.90,
    fixed_fee: props.shopFee?.fixed ?? 0.30,
    release_trigger: props.escrowSettings?.release_trigger ?? 'Release after delivered',
    hold_period: props.escrowSettings?.hold_period ?? '3 days after delivery'
});

const formatCurrency = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(n);
const formatNumber = (n: number) => new Intl.NumberFormat('en-US').format(n);

const ribbonMetrics = computed(() => {
    const rs = countries.value;
    const t = rs.reduce((a, c) => {
        props.initialUnits.forEach(u => { a.gtv += (Number(c[u.key]) || 0); });
        a.users += (Number(c.users) || 0);
        a.merchants += (Number(c.merchants) || 0);
        a.organizers += (Number(c.organizers) || 0);
        return a;
    }, { gtv: 0, users: 0, merchants: 0, organizers: 0 });

    return {
        gtv: formatCurrency(t.gtv),
        linkupRev: formatCurrency(t.gtv * 0.12),
        procPool: formatCurrency(t.gtv * 0.03),
        netProfit: formatCurrency(t.gtv * 0.09),
        users: formatNumber(t.users),
        merchants: formatNumber(t.merchants),
        organizers: formatNumber(t.organizers),
        countries: formatNumber(rs.length)
    };
});

const saveNote = ref('');

const saveFees = () => {
    form.post(route('admin.commerce.marketplace.fees.update'), {
        preserveScroll: true,
        onSuccess: () => {
            saveNote.value = '✓ Saved — synced to Fees Center & platform revenue.';
            setTimeout(() => saveNote.value = '', 3000);
        }
    });
};

const filters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });
const handleFilterChange = (newFilters: any) => { filters.value = newFilters; };

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Marketplace Fees" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="marketplaceFeesCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Marketplace Fees" :countries="countries" :metrics="ribbonMetrics" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-slate-950 flex items-center gap-3"><Receipt class="w-8 h-8" /> Marketplace Fee Settings</h3>
                        <p class="text-slate-500 font-medium mt-1">Configure seller commissions, escrow, processing and release rules.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <!-- Commission Settings -->
                    <div class="card rounded-[32px] p-8 bg-white border-slate-100 shadow-sm">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="h-10 w-10 rounded-xl bg-sky-50 text-sky-600 grid place-items-center"><Receipt class="w-5 h-5" /></div>
                            <h3 class="text-xl font-black text-slate-800">Seller Commission</h3>
                        </div>

                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Marketplace Commission (%)</label>
                                <input v-model="form.commission" type="number" step="0.01" min="0" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-sky-500 font-black text-slate-700 outline-none transition-all" />
                                <div v-if="form.errors.commission" class="text-rose-500 text-[10px] font-black uppercase ml-1">{{ form.errors.commission }}</div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Processing Fee (%)</label>
                                <input v-model="form.processing_fee" type="number" step="0.01" min="0" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-sky-500 font-black text-slate-700 outline-none transition-all" />
                                <div v-if="form.errors.processing_fee" class="text-rose-500 text-[10px] font-black uppercase ml-1">{{ form.errors.processing_fee }}</div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Fixed Fee ($)</label>
                                <input v-model="form.fixed_fee" type="number" step="0.01" min="0" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-sky-500 font-black text-slate-700 outline-none transition-all" />
                                <div v-if="form.errors.fixed_fee" class="text-rose-500 text-[10px] font-black uppercase ml-1">{{ form.errors.fixed_fee }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Escrow Rules -->
                    <div class="card rounded-[32px] p-8 bg-white border-slate-100 shadow-sm flex flex-col">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 grid place-items-center"><LockKeyhole class="w-5 h-5" /></div>
                            <h3 class="text-xl font-black text-slate-800">Escrow Rules</h3>
                        </div>

                        <div class="space-y-6 flex-1">
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Release Trigger</label>
                                <select v-model="form.release_trigger" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-sky-500 font-black text-slate-700 outline-none transition-all appearance-none">
                                    <option>Release after delivered</option>
                                    <option>Release after buyer confirms</option>
                                </select>
                                <div v-if="form.errors.release_trigger" class="text-rose-500 text-[10px] font-black uppercase ml-1">{{ form.errors.release_trigger }}</div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Hold Period</label>
                                <input v-model="form.hold_period" type="text" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-sky-500 font-black text-slate-700 outline-none transition-all" />
                                <div v-if="form.errors.hold_period" class="text-rose-500 text-[10px] font-black uppercase ml-1">{{ form.errors.hold_period }}</div>
                            </div>
                        </div>

                        <div class="mt-8">
                            <button @click="saveFees" :disabled="form.processing" class="w-full rounded-2xl bg-slate-950 text-white py-4 font-black flex items-center justify-center gap-2 transition active:scale-[0.98] disabled:opacity-50">
                                <Save class="w-5 h-5" /> {{ form.processing ? 'Saving...' : 'Save Settings' }}
                            </button>
                            <div v-if="saveNote" class="mt-4 text-center text-emerald-600 font-bold text-sm">{{ saveNote }}</div>
                        </div>
                    </div>
                </div>

                <div class="p-6 rounded-[32px] bg-slate-900 text-white flex items-start gap-4">
                    <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center shrink-0 mt-1"><Info class="w-5 h-5 text-sky-400" /></div>
                    <div>
                        <h4 class="font-black text-lg">System Integration</h4>
                        <p class="text-slate-400 text-sm mt-1 leading-relaxed">
                            Commission feeds the unified <b>Fees Center</b> (Marketplace platform fee); Processing feeds the bank fee pool. Changes made here apply globally to all future marketplace transactions.
                        </p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
