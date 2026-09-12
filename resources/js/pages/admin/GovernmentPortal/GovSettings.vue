<script setup lang="ts">
defineProps<{
    config: {
        flat: number;
        pct: number;
        cap: number;
        cashout: number;
        saas: number;
        yield: number;
        split: number;
    };
    fmt: (n: number) => string;
}>();
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black text-slate-950">Government Portal — Fees & Settings</h3>
            <p class="text-slate-500">
                How LinkUp earns from government disbursements. All fees are adjustable;
                beneficiaries always receive 100% of their entitlement (fees are billed to the agency).
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <!-- Disbursement Fees -->
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Disbursement Fees (billed to agency)</h3>
                <label class="text-sm font-bold text-slate-600">Per-Disbursement Flat Fee ($)</label>
                <input v-model="config.flat" type="number" step="0.05" class="mb-3 mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2.5" />

                <label class="text-sm font-bold text-slate-600">Per-Disbursement % (capped)</label>
                <input v-model="config.pct" type="number" step="0.05" class="mb-3 mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2.5" />

                <label class="text-sm font-bold text-slate-600">% Cap ($ max per payout)</label>
                <input v-model="config.cap" type="number" step="0.5" class="mb-3 mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2.5" />

                <label class="text-sm font-bold text-slate-600">Beneficiary Cash-Out Fee (%) <span class="font-normal text-slate-400">— wallet spend stays free</span></label>
                <input v-model="config.cashout" type="number" step="0.1" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2.5" />
            </div>

            <!-- Platform & Float -->
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Platform & Float</h3>
                <label class="text-sm font-bold text-slate-600">SaaS Platform License — per agency / month ($)</label>
                <input v-model="config.saas" type="number" step="100" class="mb-3 mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2.5" />

                <label class="text-sm font-bold text-slate-600">Float Yield — annual % earned on pre-funded balance</label>
                <input v-model="config.yield" type="number" step="0.1" class="mb-3 mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2.5" />

                <label class="text-sm font-bold text-slate-600">LinkUp / Scotiabank Float Split (LinkUp %)</label>
                <input v-model="config.split" type="number" step="1" class="mb-4 mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2.5" />

                <button class="w-full rounded-2xl bg-slate-950 px-5 py-2.5 font-black text-white">Save Fee Schedule</button>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <h3 class="mb-4 text-xl font-black">Monthly Revenue Breakdown (all programs)</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="rounded-2xl bg-purple-50 p-4">
                    <p class="text-sm font-bold text-purple-700">Disbursement Fees</p>
                    <h4 class="text-2xl font-black">$48,250</h4>
                </div>
                <div class="rounded-2xl bg-sky-50 p-4">
                    <p class="text-sm font-bold text-sky-700">SaaS Licenses</p>
                    <h4 class="text-2xl font-black">$12,500</h4>
                </div>
                <div class="rounded-2xl bg-green-50 p-4">
                    <p class="text-sm font-bold text-green-700">Float Yield (LinkUp)</p>
                    <h4 class="text-2xl font-black">$32,063</h4>
                </div>
                <div class="rounded-2xl bg-slate-900 p-4 text-white">
                    <p class="text-sm font-bold">Total / month</p>
                    <h4 class="text-2xl font-black">$92,813</h4>
                </div>
            </div>
            <p class="mt-3 text-xs text-slate-500">
                Annualized: <b>$1,113,756</b>. These figures feed the Fee Revenue Dashboard, executive totals and Business Units.
            </p>
        </div>
    </div>
</template>
