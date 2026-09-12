<script setup lang="ts">
import { reactive, ref } from 'vue';
import { toast } from 'vue-sonner';

type MarketplaceFees = {
    commission: number;
    processing: number;
    fixed: number;
    trigger: string;
    hold: string;
};

const emit = defineEmits<{
    (event: 'sync-fees', payload: MarketplaceFees): void;
}>();

const defaultFees: MarketplaceFees = {
    commission: 5,
    processing: 2.9,
    fixed: 0.3,
    trigger: 'Release after delivered',
    hold: '3 days after delivery',
};

const readStoredFees = (): MarketplaceFees => {
    try {
        const stored = localStorage.getItem('linkupMktFees');
        return stored ? { ...defaultFees, ...JSON.parse(stored) } : { ...defaultFees };
    } catch {
        return { ...defaultFees };
    }
};

const fees = reactive<MarketplaceFees>(readStoredFees());
const note = ref('');

const inputClass = 'mt-2 mb-4 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none focus:border-sky-300 focus:ring-4 focus:ring-sky-100';

const sync = () => {
    emit('sync-fees', { ...fees });
};

const saveMarketplaceFees = () => {
    localStorage.setItem('linkupMktFees', JSON.stringify(fees));
    sync();
    note.value = 'Saved - synced to Fees Center & platform revenue.';
    toast.success('Marketplace fee settings saved.');
};

sync();
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black">Marketplace Fee Settings</h3>
            <p class="text-slate-500">Configure seller commissions, escrow, processing and release rules.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Seller Commission</h3>

                <label class="font-bold text-slate-600">Marketplace Commission (%)</label>
                <input v-model.number="fees.commission" type="number" step="0.01" min="0" :class="inputClass" @input="sync" />

                <label class="font-bold text-slate-600">Processing Fee (%)</label>
                <input v-model.number="fees.processing" type="number" step="0.01" min="0" :class="inputClass" @input="sync" />

                <label class="font-bold text-slate-600">Fixed Fee ($)</label>
                <input v-model.number="fees.fixed" type="number" step="0.01" min="0" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none focus:border-sky-300 focus:ring-4 focus:ring-sky-100" @input="sync" />
            </div>

            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Escrow Rules</h3>

                <label class="font-bold text-slate-600">Release Trigger</label>
                <select v-model="fees.trigger" :class="inputClass" @change="sync">
                    <option>Release after delivered</option>
                    <option>Release after buyer confirms</option>
                </select>

                <label class="font-bold text-slate-600">Hold Period</label>
                <input v-model="fees.hold" :class="inputClass" @input="sync" />

                <button class="rounded-2xl bg-slate-950 px-5 py-3 font-black text-white transition hover:bg-slate-800" @click="saveMarketplaceFees">Save Settings</button>
                <span class="mt-3 block text-sm text-slate-500">{{ note }}</span>

                <p class="mt-2 text-xs text-slate-500">
                    Commission feeds the unified <b>Fees Center</b> (Marketplace platform fee); Processing feeds the bank fee pool.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #eaf0f7;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
}
</style>
