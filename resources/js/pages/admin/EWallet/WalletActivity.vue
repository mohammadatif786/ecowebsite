<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, reactive, ref, watch } from 'vue';

// E-Wallet → Wallet Activity. `adminOnly` turns it into the Audit Trail (admin actions only).
const props = withDefaults(defineProps<{ adminOnly?: boolean }>(), { adminOnly: false });

type Person = { id: number; name: string; email?: string; tag?: string } | null;
type Row = {
    id: number; at: string; type: string; label: string; status: 'success' | 'failed';
    amount: number | null; currency: string | null; coins: number | null;
    user: Person; counterparty: Person; actor_type: string; actor: Person;
    reference: string | null; message: string | null; note: string | null;
    channel: string | null; ip: string | null; device: string;
};

const rows = ref<Row[]>([]);
const meta = reactive({ current_page: 1, last_page: 1, total: 0 });
const summary = ref<Record<string, number>>({});
const types = ref<Record<string, Record<string, string>>>({});
const loading = ref(false);
const error = ref('');
const selected = ref<Row | null>(null);

const f = reactive({
    search: '', user_id: '' as string | number, type: '', status: '', actor: props.adminOnly ? 'admin' : '',
    from: '', to: '', min_amount: '',
});
const page = ref(1);

const params = computed(() => {
    const p: Record<string, any> = { page: page.value };
    Object.entries(f).forEach(([k, v]) => { if (v !== '' && v !== null) p[k] = v; });
    return p;
});

const money = (n: number | null, c?: string | null) =>
    n === null ? '—' : (c && c !== 'USD' ? c + ' ' : '$') + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const when = (iso: string) => {
    const d = new Date(iso);
    return d.toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

let timer: ReturnType<typeof setTimeout> | undefined;
const load = async () => {
    loading.value = true; error.value = '';
    try {
        const { data } = await axios.get(route('admin.e-wallet.activity'), { params: params.value });
        rows.value = data.data; Object.assign(meta, data.meta); summary.value = data.summary; types.value = data.types;
    } catch (e: any) {
        error.value = e?.response?.data?.message || 'Could not load wallet activity.';
    } finally { loading.value = false; }
};

watch(() => ({ ...f }), () => { page.value = 1; clearTimeout(timer); timer = setTimeout(load, 300); }, { deep: true });
watch(page, load);
onMounted(load);

const forUser = (p: Person) => { if (p) { f.user_id = p.id; f.search = ''; selected.value = null; } };
const clearFilters = () => { Object.assign(f, { search: '', user_id: '', type: '', status: '', actor: props.adminOnly ? 'admin' : '', from: '', to: '', min_amount: '' }); };
const exportUrl = computed(() => route('admin.e-wallet.activity.export', { ...params.value, page: undefined }));

const kpis = computed(() => props.adminOnly
    ? [['Events today', summary.value.events_today ?? 0, ''], ['Failed today', summary.value.failed_today ?? 0, 'text-rose-600']]
    : [
        ['Wallet actions today', summary.value.events_today ?? 0, ''],
        ['Members active today', summary.value.members_today ?? 0, ''],
        ['Failed today', summary.value.failed_today ?? 0, 'text-rose-600'],
        ['Wrong PINs (24h)', summary.value.pin_failures_24h ?? 0, 'text-amber-600'],
        ['Money out today', money(summary.value.money_out_today ?? 0), ''],
    ]);
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-3xl font-black text-slate-950">{{ adminOnly ? 'Audit Trail' : 'Wallet Activity' }}</h3>
                <p class="text-slate-500">
                    {{ adminOnly
                        ? 'Every wallet action taken by an admin: identity reviews, ID document views, withdrawal decisions.'
                        : 'Every wallet action by members and admins — top-ups, sends, requests, coins, bank, PIN and identity checks — including failed attempts.' }}
                </p>
            </div>
            <a :href="exportUrl" class="rounded-2xl bg-slate-950 px-5 py-2 font-black text-white text-center">Export CSV</a>
        </div>

        <div :class="['grid grid-cols-2 gap-4', adminOnly ? 'md:grid-cols-2' : 'md:grid-cols-5']">
            <div v-for="k in kpis" :key="String(k[0])" class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">{{ k[0] }}</p>
                <h3 :class="['text-3xl font-black', k[2]]">{{ typeof k[1] === 'number' ? k[1].toLocaleString() : k[1] }}</h3>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <!-- Filters -->
            <div class="mb-5 grid gap-3 md:grid-cols-2 xl:grid-cols-7">
                <input v-model="f.search" class="xl:col-span-2 rounded-2xl border border-slate-200 px-4 py-2" placeholder="Search member name, email, LinkUp ID, member #, reference or IP" />
                <select v-model="f.type" class="rounded-2xl border border-slate-200 px-3 py-2 bg-white">
                    <option value="">All actions</option>
                    <optgroup v-for="(items, group) in types" :key="group" :label="String(group)">
                        <option v-for="(label, key) in items" :key="key" :value="key">{{ label }}</option>
                    </optgroup>
                </select>
                <select v-model="f.status" class="rounded-2xl border border-slate-200 px-3 py-2 bg-white">
                    <option value="">Any result</option><option value="success">Success</option><option value="failed">Failed</option>
                </select>
                <select v-if="!adminOnly" v-model="f.actor" class="rounded-2xl border border-slate-200 px-3 py-2 bg-white">
                    <option value="">Members & admins</option><option value="user">Members only</option><option value="admin">Admins only</option>
                </select>
                <input v-model="f.from" type="date" class="rounded-2xl border border-slate-200 px-3 py-2" title="From" />
                <input v-model="f.to" type="date" class="rounded-2xl border border-slate-200 px-3 py-2" title="To" />
            </div>
            <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
                <span v-if="f.user_id" class="rounded-full bg-purple-50 text-purple-700 px-3 py-1 font-black">Member #{{ f.user_id }} <button class="ml-1" @click="f.user_id = ''">✕</button></span>
                <input v-if="!adminOnly" v-model="f.min_amount" type="number" min="0" class="w-40 rounded-2xl border border-slate-200 px-3 py-1.5" placeholder="Min amount $" />
                <button class="font-bold text-slate-500 hover:text-slate-900" @click="clearFilters">Clear filters</button>
                <span class="ml-auto text-slate-400 font-bold">{{ meta.total.toLocaleString() }} records</span>
            </div>

            <p v-if="error" class="mb-3 rounded-2xl bg-rose-50 p-3 font-bold text-rose-700">{{ error }}</p>

            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs font-black text-slate-500 uppercase">
                        <tr>
                            <th class="py-3">When</th><th>Member</th><th>Action</th><th>Amount</th><th>Other member</th><th>Result</th><th>By</th><th>Channel · Device</th><th>IP</th>
                        </tr>
                    </thead>
                    <tbody :class="{ 'opacity-50': loading }">
                        <tr v-if="!rows.length && !loading">
                            <td colspan="9" class="py-8 text-center text-slate-400 font-bold">No wallet activity matches these filters.</td>
                        </tr>
                        <tr v-for="r in rows" :key="r.id" class="border-t hover:bg-slate-50 cursor-pointer" @click="selected = r">
                            <td class="py-3 whitespace-nowrap text-slate-500">{{ when(r.at) }}</td>
                            <td>
                                <button v-if="r.user" class="text-left" @click.stop="forUser(r.user)">
                                    <span class="block font-black hover:text-purple-600">{{ r.user.name }}</span>
                                    <span class="block text-xs text-slate-400">#{{ r.user.id }}{{ r.user.email ? ' · ' + r.user.email : '' }}</span>
                                </button>
                                <span v-else class="text-slate-400">—</span>
                            </td>
                            <td class="font-bold">{{ r.label }}<span v-if="r.reference" class="block text-xs font-normal text-slate-400">{{ r.reference }}</span></td>
                            <td class="font-black whitespace-nowrap">
                                {{ r.amount !== null ? money(r.amount, r.currency) : (r.coins ? r.coins.toLocaleString() + ' coins' : '—') }}
                            </td>
                            <td>
                                <button v-if="r.counterparty" class="text-left font-bold hover:text-purple-600" @click.stop="forUser(r.counterparty)">{{ r.counterparty.name }}</button>
                                <span v-else class="text-slate-300">—</span>
                            </td>
                            <td>
                                <span :class="r.status === 'success' ? 'bg-green-50 text-green-700' : 'bg-rose-50 text-rose-700'" class="rounded-full px-3 py-1 text-xs font-black">
                                    {{ r.status === 'success' ? 'Success' : 'Failed' }}
                                </span>
                                <span v-if="r.message && r.status === 'failed'" class="block text-xs text-rose-600 mt-1 max-w-[16rem] truncate" :title="r.message">{{ r.message }}</span>
                            </td>
                            <td class="text-sm">
                                <span v-if="r.actor_type === 'admin'" class="rounded-full bg-slate-900 text-white px-2 py-0.5 text-xs font-black">Admin</span>
                                <span class="ml-1 text-slate-500">{{ r.actor_type === 'admin' ? (r.actor?.name || '') : 'Member' }}</span>
                            </td>
                            <td class="text-sm text-slate-500 whitespace-nowrap">{{ r.channel || '—' }} · {{ r.device }}</td>
                            <td class="text-sm text-slate-400">{{ r.ip || '—' }}</td>
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

        <!-- Detail -->
        <div v-if="selected" class="fixed inset-0 z-[200] grid place-items-center bg-black/40 p-4" @click.self="selected = null">
            <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <p class="text-xs font-black uppercase text-slate-400">Event #{{ selected.id }}</p>
                        <h4 class="text-2xl font-black">{{ selected.label }}</h4>
                    </div>
                    <button class="text-xl font-black text-slate-400" @click="selected = null">✕</button>
                </div>
                <dl class="grid grid-cols-3 gap-y-2 text-sm">
                    <dt class="font-bold text-slate-500">When</dt><dd class="col-span-2">{{ new Date(selected.at).toLocaleString() }}</dd>
                    <dt class="font-bold text-slate-500">Member</dt><dd class="col-span-2">{{ selected.user?.name || '—' }} <span class="text-slate-400">#{{ selected.user?.id }} {{ selected.user?.email }}</span></dd>
                    <dt class="font-bold text-slate-500">Result</dt><dd class="col-span-2">{{ selected.status }}{{ selected.message ? ' — ' + selected.message : '' }}</dd>
                    <dt class="font-bold text-slate-500">Amount</dt><dd class="col-span-2">{{ selected.amount !== null ? money(selected.amount, selected.currency) : '—' }}<span v-if="selected.coins"> · {{ selected.coins }} coins</span></dd>
                    <dt class="font-bold text-slate-500">Other member</dt><dd class="col-span-2">{{ selected.counterparty ? selected.counterparty.name + ' #' + selected.counterparty.id : '—' }}</dd>
                    <dt class="font-bold text-slate-500">Reference</dt><dd class="col-span-2">{{ selected.reference || '—' }}</dd>
                    <dt class="font-bold text-slate-500">Note</dt><dd class="col-span-2">{{ selected.note || '—' }}</dd>
                    <dt class="font-bold text-slate-500">Done by</dt><dd class="col-span-2">{{ selected.actor_type === 'admin' ? 'Admin ' + (selected.actor?.name || '') : 'The member' }}</dd>
                    <dt class="font-bold text-slate-500">Channel</dt><dd class="col-span-2">{{ selected.channel }} · {{ selected.device }} · {{ selected.ip }}</dd>
                </dl>
                <button v-if="selected.user" class="mt-5 w-full rounded-2xl bg-slate-950 py-3 font-black text-white" @click="forUser(selected.user)">Show all activity for this member</button>
            </div>
        </div>
    </div>
</template>
