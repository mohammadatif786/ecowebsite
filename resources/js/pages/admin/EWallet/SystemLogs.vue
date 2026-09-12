<script setup lang="ts">
import { computed, ref } from 'vue';

type SystemEntry = {
    ts: string;
    level: 'INFO' | 'WARN' | 'ERROR' | string;
    service: string;
    msg: string;
};

type ActivityLog = {
    ts?: string;
    date?: string;
    actor?: string;
    admin?: string;
    role?: string;
    category?: string;
    action?: string;
    module?: string;
    record?: string;
    result?: string;
};

const props = withDefaults(defineProps<{
    systemLogs?: SystemEntry[] | null;
    auditLogs?: ActivityLog[] | null;
}>(), {
    systemLogs: () => [],
    auditLogs: () => [],
});

const sysLevel = ref('all');
const now = Date.now();
const minute = 60000;

const seededActivity = ref<ActivityLog[]>([
    { ts: new Date(now - 31 * minute).toISOString(), actor: 'Cassius Stuart', role: 'Super Admin', category: 'Auth', action: 'Signed in', module: 'Admin Console', record: 'session', result: 'Success' },
    { ts: new Date(now - 33 * minute).toISOString(), actor: 'Unknown', role: '-', category: 'Security', action: 'Failed login attempt', module: 'Admin Console', record: '-', result: 'Blocked' },
    { ts: new Date(now - 120 * minute).toISOString(), actor: 'Risk Engine', role: 'System', category: 'System', action: 'Auto-flagged transaction', module: 'Compliance', record: 'WLT-1012', result: 'Success' },
    { ts: new Date(now - 140 * minute).toISOString(), actor: 'Bill Gateway', role: 'System', category: 'System', action: 'Bill paid', module: 'Bills', record: 'BILL-1002', result: 'Success' },
]);

const baseServiceFeed = ref<SystemEntry[]>([
    { ts: new Date(now - 1 * minute).toISOString(), level: 'INFO', service: 'wallet-ledger', msg: 'Reconciliation completed in 1.2s.' },
    { ts: new Date(now - 4 * minute).toISOString(), level: 'INFO', service: 'bill-gateway', msg: 'Heartbeat OK (200).' },
    { ts: new Date(now - 9 * minute).toISOString(), level: 'WARN', service: 'asu-engine', msg: 'ASU group #418 exceeded monthly contribution threshold.' },
    { ts: new Date(now - 16 * minute).toISOString(), level: 'INFO', service: 'api-gateway', msg: 'Health check passed across 6 regions.' },
    { ts: new Date(now - 24 * minute).toISOString(), level: 'ERROR', service: 'fx-rate', msg: 'FX feed timeout for MXN; fell back to cached rate.' },
    { ts: new Date(now - 30 * minute).toISOString(), level: 'INFO', service: 'backup', msg: 'Nightly backup completed successfully.' },
]);

const normalizeActivity = (row: ActivityLog): ActivityLog => ({
    ts: row.ts || row.date || new Date().toISOString(),
    actor: row.actor || row.admin || 'Unknown',
    role: row.role || 'System',
    category: row.category || 'System',
    action: row.action || 'System event',
    module: row.module || 'core',
    record: row.record || '',
    result: row.result || 'Success',
});

const activityEntries = computed<SystemEntry[]>(() => {
    const incoming = Array.isArray(props.auditLogs) ? props.auditLogs.map(normalizeActivity) : [];
    return [...incoming, ...seededActivity.value]
        .filter((entry) => entry.category === 'System' || entry.category === 'Security' || entry.category === 'Auth')
        .map((entry) => {
            const record = entry.record && entry.record !== '-' ? ` (${entry.record})` : '';
            return {
                ts: entry.ts || new Date().toISOString(),
                level: entry.result === 'Blocked' ? 'ERROR' : entry.category === 'System' ? 'INFO' : 'WARN',
                service: entry.module || 'core',
                msg: `${entry.actor} - ${entry.action}${record}`,
            };
        });
});

const normalizedSystemLogs = computed<SystemEntry[]>(() => {
    const incoming = Array.isArray(props.systemLogs) ? props.systemLogs : [];
    return incoming.map((entry) => ({
        ts: entry.ts || new Date().toISOString(),
        level: entry.level || 'INFO',
        service: entry.service || 'core',
        msg: entry.msg || '',
    }));
});

const feed = computed(() => {
    return [...activityEntries.value, ...normalizedSystemLogs.value, ...baseServiceFeed.value]
        .sort((a, b) => new Date(b.ts).getTime() - new Date(a.ts).getTime())
        .slice(0, 800);
});

const kpis = computed(() => {
    const errors = feed.value.filter((entry) => entry.level === 'ERROR').length;
    const warnings = feed.value.filter((entry) => entry.level === 'WARN').length;
    return [
        { label: 'Wallet Ledger', value: 'Online', color: 'text-green-600' },
        { label: 'Warnings', value: warnings, color: 'text-amber-600' },
        { label: 'Errors', value: errors, color: 'text-rose-600' },
        { label: 'Crypto Engine', value: 'Disabled', color: 'text-slate-400' },
    ];
});

const levelChips = ['all', 'INFO', 'WARN', 'ERROR'];

const filteredFeed = computed(() => {
    return feed.value
        .filter((entry) => sysLevel.value === 'all' || entry.level === sysLevel.value)
        .slice(0, 200);
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

const levelClass = (level: string) => ({
    INFO: 'bg-slate-100 text-slate-600',
    WARN: 'bg-amber-50 text-amber-700',
    ERROR: 'bg-rose-50 text-rose-700',
}[level] || 'bg-slate-100 text-slate-600');
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black text-slate-950">System Logs</h3>
            <p class="text-slate-500">Technical &amp; security event stream - service health, errors, warnings, sign-ins and blocked attempts. Feeds from the live activity engine.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div v-for="kpi in kpis" :key="kpi.label" class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">{{ kpi.label }}</p>
                <h3 class="text-3xl font-black" :class="kpi.color">{{ kpi.value }}</h3>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-xl font-black">Event Stream</h3>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="level in levelChips"
                        :key="level"
                        class="rounded-full px-3.5 py-1.5 text-xs font-black"
                        :class="sysLevel === level ? 'bg-slate-950 text-white' : 'bg-slate-100 text-slate-600'"
                        @click="sysLevel = level"
                    >
                        {{ level === 'all' ? 'All Levels' : level }}
                    </button>
                </div>
            </div>

            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-2">Time</th>
                            <th>Level</th>
                            <th>Service</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredFeed.length">
                            <td colspan="4" class="py-8 text-center font-bold text-slate-400">No logs at this level.</td>
                        </tr>
                        <tr v-for="entry in filteredFeed" v-else :key="entry.ts + entry.service + entry.msg" class="border-t">
                            <td class="whitespace-nowrap py-3 text-slate-500">{{ formatTime(entry.ts) }}</td>
                            <td>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-black" :class="levelClass(entry.level)">{{ entry.level }}</span>
                            </td>
                            <td class="font-bold">{{ entry.service }}</td>
                            <td class="text-slate-600">{{ entry.msg }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>