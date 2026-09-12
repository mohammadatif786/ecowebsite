<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import TaxLayout from './components/TaxLayout.vue';

type TaxType = 'percentage' | 'fixed';

type TaxRecord = {
    id: number;
    country: string;
    country_label: string | null;
    tax_type: TaxType;
    tax: number | string;
};

type CountryOption = {
    name: string;
    code: string | null;
    subregion: string | null;
};

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    taxes: TaxRecord[];
    countries: CountryOption[];
    filters: Record<string, any>;
}>();

const defaultApplies = 'Tickets, add-ons, LinkUp Eats, marketplace, wallet service fees, ads';
const search = ref(props.filters?.search || '');
const showTaxModal = ref(false);
const editingTax = ref<TaxRecord | null>(null);

const form = useForm({
    country: '',
    country_label: '',
    tax_type: 'percentage' as TaxType,
    tax: '',
});

const fallbackCountryCodes: Record<string, string> = {
    algeria: 'DZ',
    andorra: 'AD',
    bahamas: 'BS',
    'the bahamas': 'BS',
    pakistan: 'PK',
    jamaica: 'JM',
    barbados: 'BB',
    'trinidad & tobago': 'TT',
    'trinidad and tobago': 'TT',
    mexico: 'MX',
    brazil: 'BR',
    canada: 'CA',
    'united states': 'US',
    'united states of america': 'US',
};

const filteredTaxes = computed(() => {
    const q = String(search.value || '').toLowerCase();

    if (!q) {
        return props.taxes;
    }

    return props.taxes.filter((tax) => [
        displayCountry(tax),
        tax.country,
        tax.tax_type,
        defaultApplies,
        countryRegion(tax),
    ].join(' ').toLowerCase().includes(q));
});

const stats = computed(() => ({
    total: props.taxes.length,
    percent: props.taxes.filter((tax) => tax.tax_type === 'percentage').length,
    fixed: props.taxes.filter((tax) => tax.tax_type === 'fixed').length,
    countries: new Set(props.taxes.map((tax) => displayCountry(tax))).size,
}));

let searchTimer: number | undefined;

const searchTaxes = () => {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => {
        router.get(
            route('admin.finance.taxes.management'),
            { search: search.value },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 350);
};

const countryByCode = (code: string | null | undefined) => {
    const normalized = String(code || '').toUpperCase();
    return props.countries.find((country) => String(country.code || '').toUpperCase() === normalized);
};

const countryByName = (name: string | null | undefined) => {
    const normalized = String(name || '').toLowerCase();
    return props.countries.find((country) => String(country.name || '').toLowerCase() === normalized);
};

const resolveCountryCode = (label: string | null | undefined, currentCode?: string | null) => {
    const code = String(currentCode || '').trim().toUpperCase();

    if (code.length === 2) {
        return code;
    }

    return fallbackCountryCodes[String(label || '').trim().toLowerCase()] || code;
};

const syncCountryFromLabel = () => {
    const selected = countryByName(form.country_label);

    if (selected) {
        form.country = resolveCountryCode(selected.name, selected.code);
        form.country_label = selected.name;
        form.clearErrors('country', 'country_label');
    }
};

const displayCountry = (tax: TaxRecord) => {
    if (tax.country_label) {
        return tax.country_label;
    }

    return countryByCode(tax.country)?.name || tax.country || 'Unknown';
};

const countryRegion = (tax: TaxRecord) => {
    return countryByCode(tax.country)?.subregion || countryByName(tax.country_label)?.subregion || 'International';
};

const taxCode = (tax: TaxRecord) => {
    return resolveCountryCode(displayCountry(tax), tax.country);
};

const resetTaxForm = () => {
    form.clearErrors();
    form.country = '';
    form.country_label = '';
    form.tax_type = 'percentage';
    form.tax = '';
};

const openAddTaxModal = () => {
    editingTax.value = null;
    resetTaxForm();
    showTaxModal.value = true;
};

const openEditTaxModal = (tax: TaxRecord) => {
    editingTax.value = tax;
    form.clearErrors();
    form.country = String(tax.country || '').toUpperCase();
    form.country_label = displayCountry(tax);
    form.tax_type = tax.tax_type || 'percentage';
    form.tax = String(tax.tax ?? '');
    showTaxModal.value = true;
};

const closeTaxModal = () => {
    showTaxModal.value = false;
    editingTax.value = null;
    form.clearErrors();
};

const saveTax = () => {
    syncCountryFromLabel();
    const selected = countryByName(form.country_label);
    const resolvedCode = resolveCountryCode(form.country_label || selected?.name, form.country || selected?.code);
    const payload = {
        country: resolvedCode,
        country_label: form.country_label || selected?.name || '',
        tax_type: form.tax_type,
        tax: form.tax,
    };

    form.country = payload.country;
    form.country_label = payload.country_label;
    form.clearErrors('country', 'country_label');

    const options = {
        preserveScroll: true,
        onSuccess: () => closeTaxModal(),
    };

    if (editingTax.value) {
        form.transform(() => payload).put(route('admin.finance.taxes.update', editingTax.value.id), options);
        return;
    }

    form.transform(() => payload).post(route('admin.finance.taxes.store'), options);
};

const deleteTax = (tax: TaxRecord) => {
    if (!window.confirm(`Delete tax rule for ${displayCountry(tax)}?`)) {
        return;
    }

    router.delete(route('admin.finance.taxes.destroy', tax.id), {
        preserveScroll: true,
    });
};

const formatRate = (tax: TaxRecord) => {
    const value = Number(tax.tax || 0).toFixed(2);

    if (tax.tax_type === 'fixed') {
        return `$${value}`;
    }

    return `${value}%`;
};
</script>

<template>
    <TaxLayout title="Tax Management" active-id="taxManagementCommand" :initial-units="props.initialUnits" :initial-countries="props.initialCountries" v-slot="{ num }">
        <div class="space-y-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-3xl font-black">Tax Management</h3>
                    <p class="text-slate-500">Assign country-specific tax rules for tickets, wallet fees, marketplace, LinkUp Eats, ASUE, bill pay, ads, and digital services.</p>
                </div>
                <button class="rounded-2xl bg-purple-600 px-5 py-3 font-black text-white shadow-lg shadow-purple-200" @click="openAddTaxModal">+ Add Tax</button>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="card flex items-center gap-4 rounded-3xl p-5">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-purple-100 text-purple-600">%</div>
                    <div>
                        <h3 class="text-3xl font-black">{{ num(stats.total) }}</h3>
                        <p class="text-sm text-slate-500">Total Taxes</p>
                    </div>
                </div>
                <div class="card flex items-center gap-4 rounded-3xl p-5">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-green-100 text-green-600">%</div>
                    <div>
                        <h3 class="text-3xl font-black">{{ num(stats.percent) }}</h3>
                        <p class="text-sm text-slate-500">Percentage Based</p>
                    </div>
                </div>
                <div class="card flex items-center gap-4 rounded-3xl p-5">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-blue-100 text-blue-600">$</div>
                    <div>
                        <h3 class="text-3xl font-black">{{ num(stats.fixed) }}</h3>
                        <p class="text-sm text-slate-500">Fixed Amount</p>
                    </div>
                </div>
                <div class="card flex items-center gap-4 rounded-3xl p-5">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-100 text-amber-600">G</div>
                    <div>
                        <h3 class="text-3xl font-black">{{ num(stats.countries) }}</h3>
                        <p class="text-sm text-slate-500">Countries</p>
                    </div>
                </div>
            </div>

            <div class="card overflow-hidden rounded-3xl">
                <div class="border-b border-slate-100 p-5">
                    <input v-model="search" class="w-full rounded-2xl border border-slate-200 px-4 py-3 md:w-96" placeholder="Search country, region, tax type, applies to..." @input="searchTaxes" />
                </div>
                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-5 py-4">Country</th>
                                <th>Region</th>
                                <th>Code</th>
                                <th>Tax Name / Type</th>
                                <th>Rate</th>
                                <th>Applies To</th>
                                <th>Remittance</th>
                                <th>Frequency</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tax in filteredTaxes" :key="tax.id" class="border-t">
                                <td class="px-5 py-4 font-black">{{ displayCountry(tax) }}</td>
                                <td>{{ countryRegion(tax) }}</td>
                                <td><span class="rounded-lg bg-slate-100 px-2 py-1 font-bold">{{ taxCode(tax) }}</span></td>
                                <td><span class="rounded-full bg-purple-50 px-3 py-1 text-xs font-black text-purple-700">{{ tax.tax_type }}</span></td>
                                <td class="font-black">{{ formatRate(tax) }}</td>
                                <td>{{ defaultApplies }}</td>
                                <td>LinkUp collects</td>
                                <td>Monthly</td>
                                <td><span class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700">Active</span></td>
                                <td>
                                    <div class="flex gap-2">
                                        <button class="grid h-9 w-9 place-items-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-purple-100 hover:text-purple-700" title="Edit" aria-label="Edit tax" @click="openEditTaxModal(tax)">
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                        <button class="grid h-9 w-9 place-items-center rounded-xl bg-red-50 text-red-500 transition hover:bg-red-100 hover:text-red-700" title="Delete" aria-label="Delete tax" @click="deleteTax(tax)">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!filteredTaxes.length">
                                <td colspan="10" class="px-5 py-12 text-center text-sm font-bold text-slate-400">No taxes configured</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="border-t p-5 text-sm text-slate-500">Showing {{ filteredTaxes.length }} taxes</div>
            </div>
        </div>

        <div v-if="showTaxModal" class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/55 px-4 py-6">
            <div class="w-full max-w-[768px] overflow-hidden rounded-[24px] bg-white shadow-2xl">
                <div class="flex items-start justify-between bg-gradient-to-r from-purple-600 to-fuchsia-500 px-7 py-6 text-white">
                    <div>
                        <h3 class="text-3xl font-black">{{ editingTax ? 'Edit Tax' : 'Add Tax' }}</h3>
                        <p class="mt-1 text-sm font-medium text-white/90">{{ editingTax ? 'Update a country tax rule' : 'Create a country tax rule' }}</p>
                    </div>
                    <button class="grid h-10 w-10 place-items-center rounded-full text-3xl font-light leading-none text-white/90 hover:bg-white/10" @click="closeTaxModal" aria-label="Close tax modal">x</button>
                </div>

                <form @submit.prevent="saveTax">
                    <div class="grid grid-cols-1 gap-4 px-7 py-7 md:grid-cols-2">
                        <div>
                            <select v-model="form.country_label" class="w-full rounded-2xl border border-slate-200 px-4 py-3.5 text-sm font-medium outline-none focus:border-purple-400 focus:ring-4 focus:ring-purple-100" @change="syncCountryFromLabel">
                                <option value="">Select country</option>
                                <option v-for="country in props.countries" :key="country.code || country.name" :value="country.name">
                                    {{ country.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.country_label" class="mt-1 text-xs font-bold text-red-600">{{ form.errors.country_label }}</p>
                        </div>
                        <div>
                            <input v-model="form.country" readonly class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm font-medium uppercase outline-none focus:border-purple-400 focus:ring-4 focus:ring-purple-100" placeholder="Code" />
                            <p v-if="form.errors.country" class="mt-1 text-xs font-bold text-red-600">{{ form.errors.country }}</p>
                        </div>
                        <div>
                            <select v-model="form.tax_type" class="w-full rounded-2xl border border-slate-200 px-4 py-3.5 text-sm font-medium outline-none focus:border-purple-400 focus:ring-4 focus:ring-purple-100">
                                <option value="percentage">percentage</option>
                                <option value="fixed">fixed</option>
                            </select>
                            <p v-if="form.errors.tax_type" class="mt-1 text-xs font-bold text-red-600">{{ form.errors.tax_type }}</p>
                        </div>
                        <div>
                            <input v-model="form.tax" type="number" min="0" step="0.01" class="w-full rounded-2xl border border-slate-200 px-4 py-3.5 text-sm font-medium outline-none focus:border-purple-400 focus:ring-4 focus:ring-purple-100" placeholder="Rate" />
                            <p v-if="form.errors.tax" class="mt-1 text-xs font-bold text-red-600">{{ form.errors.tax }}</p>
                        </div>
                        <input :value="defaultApplies" readonly class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm font-medium outline-none md:col-span-2" placeholder="Applies to" />
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50/70 px-7 py-5">
                        <button type="button" class="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-black text-slate-900 hover:bg-slate-50" @click="closeTaxModal">Cancel</button>
                        <button type="submit" class="rounded-2xl bg-purple-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-purple-200 hover:bg-purple-700 disabled:opacity-60" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save Tax' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </TaxLayout>
</template>
