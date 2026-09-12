<script setup lang="ts">
import TaxLayout from './components/TaxLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { CheckCircle2, Pencil, PlugZap, ShieldCheck } from 'lucide-vue-next';

type TaxApiSetting = {
    id: number;
    provider: string;
    end_point_url: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
};

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    taxApiSettings: TaxApiSetting[];
}>();

const providers = [
    { value: 'taxjar', label: 'TaxJar API', endpoint: 'https://api.taxjar.com/v2' },
    { value: 'avalara', label: 'Avalara API', endpoint: 'https://rest.avatax.com/api/v2' },
    { value: 'ziptax', label: 'Zip-Tax API', endpoint: 'https://api.zip-tax.com/request' },
    { value: 'custom', label: 'Custom API', endpoint: '' },
];

const saved = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    id: null as number | null,
    provider: '',
    end_point_url: '',
    api_key: '',
    is_active: false,
});

const activeSetting = computed(() => props.taxApiSettings.find((setting) => setting.is_active) || null);

const providerLabel = (provider: string) => {
    return providers.find((item) => item.value === provider)?.label || provider;
};

const resetForm = () => {
    editingId.value = null;
    form.clearErrors();
    form.id = null;
    form.provider = '';
    form.end_point_url = '';
    form.api_key = '';
    form.is_active = props.taxApiSettings.length === 0;
};

const loadProviderDefault = () => {
    const existing = props.taxApiSettings.find((setting) => setting.provider === form.provider);
    const provider = providers.find((item) => item.value === form.provider);

    if (existing) {
        editSetting(existing);
        return;
    }

    form.id = null;
    editingId.value = null;
    form.end_point_url = provider?.endpoint || '';
    form.api_key = '';
    form.is_active = props.taxApiSettings.length === 0;
    form.clearErrors();
};

const editSetting = (setting: TaxApiSetting) => {
    editingId.value = setting.id;
    form.clearErrors();
    form.id = setting.id;
    form.provider = setting.provider;
    form.end_point_url = setting.end_point_url || '';
    form.api_key = '';
    form.is_active = setting.is_active;
};

const save = () => {
    form.post(route('admin.finance.taxes.settings.save'), {
        preserveScroll: true,
        onSuccess: () => {
            saved.value = true;
            form.api_key = '';
            window.setTimeout(() => {
                saved.value = false;
            }, 2500);
        },
    });
};
</script>

<template>
    <TaxLayout title="Tax Settings" active-id="taxSettingsCommand" :initial-units="props.initialUnits" :initial-countries="props.initialCountries">
        <div class="space-y-6">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-3xl font-black">Tax Settings</h3>
                    <p class="text-slate-500">Configure tax provider API credentials and choose the active tax rate source.</p>
                </div>
                <button class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white" @click="resetForm">+ New Provider</button>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <div class="card metric dark rounded-3xl p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-bold text-slate-300">Active Provider</p>
                        <ShieldCheck class="h-5 w-5 text-lime-300" />
                    </div>
                    <h3 class="mt-2 text-2xl font-black">{{ activeSetting ? providerLabel(activeSetting.provider) : 'None' }}</h3>
                    <p class="mt-1 text-xs font-bold text-lime-300">{{ activeSetting?.end_point_url || 'No active endpoint configured' }}</p>
                </div>
                <div class="card rounded-3xl p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-bold text-slate-500">Configured APIs</p>
                        <PlugZap class="h-5 w-5 text-purple-500" />
                    </div>
                    <h3 class="mt-2 text-3xl font-black">{{ props.taxApiSettings.length }}</h3>
                    <p class="text-sm text-slate-500">Providers saved in database</p>
                </div>
                <div class="card rounded-3xl p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-bold text-slate-500">Mode</p>
                        <CheckCircle2 class="h-5 w-5 text-emerald-500" />
                    </div>
                    <h3 class="mt-2 text-3xl font-black">{{ form.id ? 'Update' : 'Create' }}</h3>
                    <p class="text-sm text-slate-500">{{ form.id ? 'Editing saved provider' : 'Adding provider settings' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="card rounded-3xl p-6">
                    <h3 class="mb-4 text-xl font-black">Tax Provider API Settings</h3>

                    <form class="space-y-4" @submit.prevent="save">
                        <div>
                            <label class="font-bold text-slate-600">Select Tax Provider</label>
                            <select v-model="form.provider" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none focus:border-purple-400" @change="loadProviderDefault">
                                <option value="">Choose Provider</option>
                                <option v-for="provider in providers" :key="provider.value" :value="provider.value">{{ provider.label }}</option>
                            </select>
                            <p v-if="form.errors.provider" class="mt-1 text-xs font-bold text-red-600">{{ form.errors.provider }}</p>
                        </div>

                        <div>
                            <label class="font-bold text-slate-600">API Endpoint URL</label>
                            <input v-model="form.end_point_url" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none focus:border-purple-400" placeholder="https://api.taxprovider.com/v2" />
                            <p v-if="form.errors.end_point_url" class="mt-1 text-xs font-bold text-red-600">{{ form.errors.end_point_url }}</p>
                        </div>

                        <div>
                            <label class="font-bold text-slate-600">API Key</label>
                            <input v-model="form.api_key" type="password" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none focus:border-purple-400" :placeholder="form.id ? 'Leave blank to keep existing encrypted key' : 'Enter API key'" />
                            <p v-if="form.errors.api_key" class="mt-1 text-xs font-bold text-red-600">{{ form.errors.api_key }}</p>
                        </div>

                        <label class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4 font-bold text-slate-700">
                            <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300" />
                            Set this provider as active tax API
                        </label>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <button type="submit" class="rounded-2xl bg-purple-600 px-5 py-3 font-black text-white shadow-lg shadow-purple-200 disabled:opacity-60" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : form.id ? 'Update Settings' : 'Create Settings' }}
                            </button>
                            <button type="button" class="rounded-2xl border border-slate-200 px-5 py-3 font-black text-slate-700" @click="resetForm">Clear</button>
                        </div>

                        <p v-if="saved" class="font-bold text-green-600">Tax settings saved.</p>
                    </form>
                </div>

                <div class="card overflow-hidden rounded-3xl">
                    <div class="border-b border-slate-100 p-6">
                        <h3 class="text-xl font-black">Saved Provider Settings</h3>
                        <p class="text-sm text-slate-500">API keys are encrypted and never displayed after save.</p>
                    </div>
                    <div class="scrollbar overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="px-5 py-4">Provider</th>
                                    <th>Endpoint</th>
                                    <th>Status</th>
                                    <th>Updated</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="setting in props.taxApiSettings" :key="setting.id" class="border-t">
                                    <td class="px-5 py-4 font-black">{{ providerLabel(setting.provider) }}</td>
                                    <td class="max-w-xs truncate text-sm text-slate-500">{{ setting.end_point_url }}</td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-xs font-black" :class="setting.is_active ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500'">
                                            {{ setting.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-sm text-slate-500">{{ setting.updated_at || '-' }}</td>
                                    <td>
                                        <button class="grid h-9 w-9 place-items-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-purple-100 hover:text-purple-700" title="Edit" @click="editSetting(setting)">
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!props.taxApiSettings.length">
                                    <td colspan="5" class="px-5 py-12 text-center text-sm font-bold text-slate-400">No tax API settings configured</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </TaxLayout>
</template>
