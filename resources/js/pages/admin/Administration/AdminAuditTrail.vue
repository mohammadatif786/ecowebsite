<script setup lang="ts">
const metrics = [
    { label: 'Role Changes', value: 12 },
    { label: 'Sensitive Exports', value: 7 },
    { label: 'Failed Logins', value: 3 },
];

const auditRows = [
    ['Today 10:41 AM', 'Cassius Stuart', 'Created role: Wallet Operations', 'Admins', 'Chrome / Nassau', 'Success'],
    ['Today 10:25 AM', 'Nadia Williams', 'Published news story', 'Caribbean 360 News', 'Safari / Miami', 'Success'],
    ['Today 9:58 AM', 'Maria Gomez', 'Reviewed wallet compliance alert', 'E-Wallet', 'Chrome / Colombia', 'Success'],
    ['Today 9:22 AM', 'Unknown', 'Failed login attempt', 'Admins', 'Unknown Device', 'Blocked'],
];
</script>

<template>
    <section class="space-y-6">
        <div>
            <h3 class="text-3xl font-black">Admin Audit Trail</h3>
            <p class="text-slate-500">Track role changes, permission edits, logins, exports, deletions, and sensitive actions.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <div v-for="metric in metrics" :key="metric.label" class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">{{ metric.label }}</p>
                <h3 class="text-4xl font-black">{{ metric.value }}</h3>
            </div>
        </div>

        <div class="card scrollbar overflow-x-auto rounded-3xl p-6">
            <table class="w-full text-left">
                <thead class="text-xs uppercase text-slate-500">
                    <tr>
                        <th class="py-3 pr-4">Time</th>
                        <th class="py-3 pr-4">Admin</th>
                        <th class="py-3 pr-4">Action</th>
                        <th class="py-3 pr-4">Module</th>
                        <th class="py-3 pr-4">IP / Device</th>
                        <th class="py-3">Result</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="row in auditRows" :key="row.join('-')">
                        <td class="py-4 pr-4">{{ row[0] }}</td>
                        <td class="py-4 pr-4">{{ row[1] }}</td>
                        <td class="py-4 pr-4">{{ row[2] }}</td>
                        <td class="py-4 pr-4">{{ row[3] }}</td>
                        <td class="py-4 pr-4">{{ row[4] }}</td>
                        <td class="py-4">
                            <span class="font-bold" :class="row[5] === 'Blocked' ? 'text-rose-600' : 'text-green-600'">{{ row[5] }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
