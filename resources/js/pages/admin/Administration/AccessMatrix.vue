<script setup lang="ts">
import { computed, ref } from 'vue';

const search = ref('');

const columns = ['Super Admin', 'News', 'Wallet', 'Events', 'Eats', 'Marketplace', 'Marketing', 'Auditor'];

const matrixRows = [
    ['Dashboard', 'Full', 'View', 'View', 'View', 'View', 'View', 'View', 'View'],
    ['Caribbean 360 News', 'Full', 'Full', 'No Access', 'No Access', 'No Access', 'No Access', 'View', 'View'],
    ['E-Wallet / ASUE / Bills', 'Full', 'No Access', 'Full', 'View Settlements', 'No Access', 'View Seller Wallets', 'No Access', 'View'],
    ['Event Management', 'Full', 'No Access', 'Settlement View', 'Full', 'No Access', 'No Access', 'Campaign View', 'View'],
    ['LinkUp Eats', 'Full', 'No Access', 'Settlement View', 'No Access', 'Full', 'No Access', 'Promo View', 'View'],
    ['Marketplace', 'Full', 'No Access', 'Settlement View', 'No Access', 'No Access', 'Full', 'Promo View', 'View'],
    ['Emails / Push', 'Full', 'Send News', 'No Access', 'Event Campaigns', 'Eats Campaigns', 'Marketplace Campaigns', 'Full', 'View'],
    ['Taxes / Compliance', 'Full', 'No Access', 'View', 'View Event Tax', 'View Eats Tax', 'View Seller Tax', 'No Access', 'View'],
    ['Admin Roles', 'Full', 'No Access', 'No Access', 'No Access', 'No Access', 'No Access', 'No Access', 'View'],
];

const filteredRows = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return matrixRows;

    return matrixRows.filter((row) => row.join(' ').toLowerCase().includes(query));
});

const permissionClass = (value: string) => {
    if (value === 'Full') return 'bg-purple-50 text-purple-700';
    if (value === 'No Access') return 'bg-slate-100 text-slate-500';
    if (value === 'View') return 'bg-sky-50 text-sky-700';
    return 'bg-green-50 text-green-700';
};
</script>

<template>
    <section class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h3 class="text-3xl font-black">Access Matrix</h3>
                <p class="text-slate-500">Quick view of which roles can access each LinkUp backend module.</p>
            </div>
            <input
                v-model="search"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none md:w-96"
                placeholder="Search module, role, permission..."
            />
        </div>

        <div class="card scrollbar overflow-x-auto rounded-3xl p-6">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-500">
                    <tr>
                        <th class="py-3 pr-4">Module</th>
                        <th v-for="column in columns" :key="column" class="py-3 pr-4">{{ column }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="row in filteredRows" :key="row[0]">
                        <td class="py-4 pr-4 font-black">{{ row[0] }}</td>
                        <td v-for="(permission, index) in row.slice(1)" :key="`${row[0]}-${index}`" class="py-4 pr-4">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold" :class="permissionClass(permission)">
                                {{ permission }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
