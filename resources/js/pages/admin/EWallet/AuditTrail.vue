<script setup lang="ts">
import { computed, ref } from 'vue';

type AuditLog = {
    ts: string;
    actor: string;
    role: string;
    category: string;
    action: string;
    module: string;
    record: string;
    result: string;
    ip?: string;
    device: string;
};

const props = withDefaults(defineProps<{
    auditLogs?: any[] | null;
}>(), {
    auditLogs: () => [],
});

const auditFilter = ref('all');
const auditQuery = ref('');
const auditActor = ref('all');
const exportedNote = ref(false);
const localLogs = ref<AuditLog[]>([]);

const sessionIp = 'session';
const now = Date.now();
const minute = 60000;

const seededLogs = ref<AuditLog[]>([
    [2 * minute, 'Cassius Stuart', 'Super Admin', 'Compliance', 'Filed SAR', 'Wallet Compliance', 'AML-2975', 'Success'],
    [8 * minute, 'Maria Gomez', 'Compliance Officer', 'Compliance', 'Cleared AML case', 'Wallet Compliance', 'AML-2990', 'Success'],
    [14 * minute, 'Cassius Stuart', 'Super Admin', 'Update', 'Saved AML thresholds', 'Wallet Compliance', 'thresholds', 'Success'],
    [22 * minute, 'Nadia Williams', 'News Manager', 'Create', 'Published news story', 'Caribbean 360 News', 'NEWS-4471', 'Success'],
    [31 * minute, 'Cassius Stuart', 'Super Admin', 'Auth', 'Signed in', 'Admin Console', 'session', 'Success'],
    [33 * minute, 'Unknown', '-', 'Security', 'Failed login attempt', 'Admin Console', '-', 'Blocked'],
    [40 * minute, 'Carlos Vega', 'Settlement Manager', 'Update', 'Approved payout', 'Wallet Payouts', 'WPO-7001', 'Success'],
    [52 * minute, 'Maria Gomez', 'Compliance Officer', 'Delete', 'Removed flagged user', 'Users', 'USR-2207', 'Success'],
    [61 * minute, 'Cassius Stuart', 'Super Admin', 'Export', 'Exported revenue CSV', 'Fee Revenue', 'export', 'Success'],
    [70 * minute, 'David Knowles', 'Finance Admin', 'Update', 'Changed event fee to 6.5%', 'Pricing', 'Bahamas', 'Success'],
    [88 * minute, 'Cassius Stuart', 'Super Admin', 'Create', 'Created role: Wallet Operations', 'Admins', 'ROLE-12', 'Success'],
    [120 * minute, 'Risk Engine', 'System', 'System', 'Auto-flagged transaction', 'Compliance', 'WLT-1012', 'Success'],
    [140 * minute, 'Bill Gateway', 'System', 'System', 'Bill paid', 'Bills', 'BILL-1002', 'Success'],
    [160 * minute, 'Maria Gomez', 'Compliance Officer', 'Navigation', 'Viewed: Wallet Compliance Desk', 'Navigation', 'walletComplianceCommand', 'Success'],
].map((s: any[]) => ({
    ts: new Date(now - s[0]).toISOString(),
    actor: s[1],
    role: s[2],
    category: s[3],
    action: s[4],
    module: s[5],
    record: s[6],
    result: s[7],
    ip: sessionIp,
    device: s[2] === 'System' ? 'service' : `Chrome - ${s[1] === 'Unknown' ? 'Unknown device' : 'Admin Console'}`,
})) as any);

const normalizeLog = (row: any): AuditLog => ({
    ts: row.ts || row.date || new Date().toISOString(),
    actor: row.actor || row.admin || 'Unknown',
    role: row.role || '-',
    category: row.category || 'Update',
    action: row.action || row.detail || 'Activity recorded',
    module: row.module || 'Audit Trail',
    record: row.record || row.detail || '-',
    result: row.result || 'Success',
    ip: row.ip || sessionIp,
    device: row.device || `Chrome - ${row.admin || row.actor ? 'Admin Console' : 'Unknown device'}`,
});

const allLogs = computed(() => {
    const incoming = Array.isArray(props.auditLogs) ? props.auditLogs.map(normalizeLog) : [];
    return [...localLogs.value, ...incoming, ...seededLogs.value]
        .sort((a, b) => new Date(b.ts).getTime() - new Date(a.ts).getTime())
        .slice(0, 800);
});

const actors = computed(() => Array.from(new Set(allLogs.value.map((l) => l.actor))).sort());

const categoryChips = ['all', 'Navigation', 'Create', 'Update', 'Delete', 'Auth', 'Security', 'Export', 'Compliance', 'System'];

const filteredLogs = computed(() => {
    const q = auditQuery.value.toLowerCase();
    return allLogs.value.filter((log) => {
        const okFilter = auditFilter.value === 'all' || log.category === auditFilter.value;
        const okActor = auditActor.value === 'all' || log.actor === auditActor.value;
        const okSearch = !q || [log.actor, log.action, log.module, log.record].join(' ').toLowerCase().includes(q);
        return okFilter && okActor && okSearch;
    }).slice(0, 250);
});

const kpis = computed(() => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const count = (category: string) => allLogs.value.filter((log) => log.category === category).length;

    return [
        {
            label: 'Events Today',
            value: allLogs.value.filter((log) => new Date(log.ts) >= today).length,
            sub: `${allLogs.value.length} total tracked`,
            color: 'text-slate-950',
        },
        {
            label: 'Creates / Updates',
            value: count('Create') + count('Update'),
            sub: '',
            color: 'text-sky-600',
        },
        {
            label: 'Deletions',
            value: count('Delete'),
            sub: '',
            color: 'text-rose-600',
        },
        {
            label: 'Security Events',
            value: count('Security') + count('Auth'),
            sub: 'logins & blocks',
            color: 'text-amber-600',
        },
    ];
});

const formatTime = (iso: string) => {
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return iso;
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    let hour = d.getHours();
    const ap = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12 || 12;
    const minuteText = String(d.getMinutes()).padStart(2, '0');
    return `${months[d.getMonth()]} ${d.getDate()}, ${hour}:${minuteText} ${ap}`;
};

const categoryClass = (category: string) => ({
    Navigation: 'bg-slate-100 text-slate-600',
    Create: 'bg-green-50 text-green-700',
    Update: 'bg-sky-50 text-sky-700',
    Delete: 'bg-rose-50 text-rose-700',
    Auth: 'bg-indigo-50 text-indigo-700',
    Export: 'bg-purple-50 text-purple-700',
    Compliance: 'bg-amber-50 text-amber-700',
    Security: 'bg-rose-100 text-rose-800',
    System: 'bg-slate-50 text-slate-500',
}[category] || 'bg-slate-50 text-slate-600');

const resultClass = (result: string) => {
    if (result === 'Success') return 'text-green-600';
    if (result === 'Blocked') return 'text-rose-600';
    return 'text-amber-600';
};

const exportCSV = () => {
    const rows = [
        ['Time', 'Actor', 'Role', 'Category', 'Action', 'Module', 'Record', 'IP/Device', 'Result'],
        ...allLogs.value.map((log) => [formatTime(log.ts), log.actor, log.role, log.category, log.action, log.module, log.record, log.device || log.ip || '', log.result]),
    ];
    const csv = rows.map((row) => row.map((cell) => `"${String(cell).replaceAll('"', '""')}"`).join(',')).join('\n');
    const link = document.createElement('a');
    link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
    link.download = 'linkup_activity_log.csv';
    link.click();
    URL.revokeObjectURL(link.href);

    localLogs.value.unshift({
        ts: new Date().toISOString(),
        actor: 'Cassius Stuart',
        role: 'Super Admin',
        category: 'Export',
        action: 'Exported activity log CSV',
        module: 'Audit Trail',
        record: 'export',
        result: 'Success',
        ip: sessionIp,
        device: 'Chrome - Admin Console',
    });
    exportedNote.value = true;
    setTimeout(() => (exportedNote.value = false), 2200);
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-3xl font-black text-slate-950">Audit Trail - Who Did What</h3>
                <p class="text-slate-500">Comprehensive activity log: every sign-in, page visited, create, edit, delete, export and security event - captured live as the system is used.</p>
            </div>
            <div class="flex flex-col items-end gap-2">
                <button class="rounded-2xl bg-slate-950 px-5 py-3 font-black text-white" @click="exportCSV">Export CSV</button>
                <span v-if="exportedNote" class="text-sm font-bold text-green-600">CSV exported.</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div v-for="kpi in kpis" :key="kpi.label" class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">{{ kpi.label }}</p>
                <h3 class="text-4xl font-black" :class="kpi.color">{{ kpi.value }}</h3>
                <p v-if="kpi.sub" class="text-xs text-slate-500">{{ kpi.sub }}</p>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <input
                    v-model="auditQuery"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 md:w-80"
                    placeholder="Search actor, action, module, record..."
                />
                <select v-model="auditActor" class="rounded-2xl border border-slate-200 px-4 py-2.5 font-bold">
                    <option value="all">All Actors</option>
                    <option v-for="actor in actors" :key="actor" :value="actor">{{ actor }}</option>
                </select>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="chip in categoryChips"
                        :key="chip"
                        class="rounded-full px-3.5 py-1.5 text-xs font-black"
                        :class="auditFilter === chip ? 'bg-slate-950 text-white' : 'bg-slate-100 text-slate-600'"
                        @click="auditFilter = chip"
                    >
                        {{ chip === 'all' ? 'All' : chip }}
                    </button>
                </div>
            </div>

            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-2">Time</th>
                            <th>Actor</th>
                            <th>Category</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Record</th>
                            <th>IP / Device</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredLogs.length">
                            <td colspan="8" class="py-8 text-center font-bold text-slate-400">No matching activity.</td>
                        </tr>
                        <tr v-for="log in filteredLogs" v-else :key="log.ts + log.actor + log.action" class="border-t">
                            <td class="whitespace-nowrap py-3 text-slate-500">{{ formatTime(log.ts) }}</td>
                            <td class="font-black">
                                {{ log.actor }}
                                <div class="text-xs font-normal text-slate-400">{{ log.role }}</div>
                            </td>
                            <td>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-black" :class="categoryClass(log.category)">{{ log.category }}</span>
                            </td>
                            <td>{{ log.action }}</td>
                            <td class="text-slate-500">{{ log.module }}</td>
                            <td class="text-xs text-slate-500">{{ log.record }}</td>
                            <td class="text-xs text-slate-500">{{ log.device || log.ip }}</td>
                            <td>
                                <span class="font-bold" :class="resultClass(log.result)">{{ log.result }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>