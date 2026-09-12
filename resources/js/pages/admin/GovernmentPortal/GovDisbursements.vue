<script setup lang="ts">
import { ref, computed } from 'vue';

const props = defineProps<{
    programs: any[];
    batches: any[];
    fmt: (n: number) => string;
    num: (n: number) => string;
    govMonthlyPayouts: (p: any) => number;
    govFeePer: (n: number) => number;
}>();

const selectedProgramName = ref(props.programs[0]?.name || '');
const selectedProgram = computed(() => props.programs.find(p => p.name === selectedProgramName.value));

const previewData = computed(() => {
    if (!selectedProgram.value) return null;
    const p = selectedProgram.value;
    const count = Math.round(props.govMonthlyPayouts(p));
    return {
        count,
        net: count * p.avg,
        fee: count * props.govFeePer(p.avg)
    };
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black text-slate-950">Disbursements</h3>
            <p class="text-slate-500">
                Create a payout batch for a program, approve under dual control, then disburse instantly to beneficiary wallets from the Scotiabank float.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
            <!-- New Batch -->
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">New Batch</h3>
                <label class="text-sm font-bold text-slate-600">Program</label>
                <select v-model="selectedProgramName" class="mb-3 mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2.5 font-bold">
                    <option v-for="p in programs" :key="p.id">{{ p.name }}</option>
                </select>

                <div v-if="previewData" class="mb-3 space-y-2 rounded-2xl bg-slate-50 p-4 text-sm">
                    <div class="flex justify-between">
                        <span class="font-bold text-slate-500">Beneficiaries</span>
                        <span class="font-black">{{ num(previewData.count) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-bold text-slate-500">Net to wallets</span>
                        <span class="font-black text-green-600">{{ fmt(previewData.net) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-bold text-slate-500">LinkUp fee (agency)</span>
                        <span class="font-black text-purple-600">{{ fmt(previewData.fee) }}</span>
                    </div>
                </div>

                <button class="w-full rounded-2xl bg-slate-950 px-5 py-2.5 font-black text-white">Create Draft Batch</button>
            </div>

            <!-- Batch Queue -->
            <div class="card rounded-3xl p-6 2xl:col-span-2">
                <h3 class="mb-4 text-xl font-black">Batch Queue</h3>
                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs uppercase text-slate-500">
                            <tr>
                                <th class="py-2">Batch</th>
                                <th>Program</th>
                                <th>Beneficiaries</th>
                                <th>Net to Wallets</th>
                                <th>LinkUp Fee</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="b in batches" :key="b.id" class="border-t">
                                <td class="py-2 font-black">{{ b.id }}</td>
                                <td>{{ b.program }}</td>
                                <td>{{ num(b.count) }}</td>
                                <td class="font-black text-green-600">{{ fmt(b.net) }}</td>
                                <td class="font-bold text-purple-600">{{ fmt(b.fee) }}</td>
                                <td>
                                    <span
                                        class="rounded-full px-2.5 py-0.5 text-xs font-black"
                                        :class="b.status === 'Disbursed' ? 'bg-green-50 text-green-700' : 'bg-sky-50 text-sky-700'"
                                    >
                                        {{ b.status }}
                                    </span>
                                </td>
                                <td>
                                    <button v-if="b.status === 'Approved'" class="rounded-lg bg-red-600 px-3 py-1 text-xs font-black text-white">
                                        Disburse
                                    </button>
                                    <span v-else class="text-xs font-bold text-slate-400">paid {{ b.date }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
