<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { toast } from 'vue-sonner';

type EatsFees = {
    commission: number;
    service: number;
    processing: number;
    gratuity: number;
};

const emit = defineEmits<{
    (event: 'sync-fees', payload: EatsFees): void;
}>();

const defaultFees: EatsFees = {
    commission: 15,
    service: 5,
    processing: 3,
    gratuity: 15,
};

const readStoredFees = (): EatsFees => {
    try {
        const stored = localStorage.getItem('linkupEatsFees');
        return stored ? { ...defaultFees, ...JSON.parse(stored) } : { ...defaultFees };
    } catch {
        return { ...defaultFees };
    }
};

const fees = reactive<EatsFees>(readStoredFees());
const note = ref('');

const inputClass = 'mt-2 w-full rounded-2xl border border-slate-200 px-3 py-2 text-3xl font-black outline-none focus:border-orange-300 focus:ring-4 focus:ring-orange-100';

const preview = computed(() => {
    const subtotal = 100;
    const commission = subtotal * (Number(fees.commission || 0) / 100);
    const service = subtotal * (Number(fees.service || 0) / 100);
    const processing = subtotal * (Number(fees.processing || 0) / 100);

    return [
        ['Order Subtotal', `$${subtotal.toFixed(2)}`],
        ['LinkUp Commission', `$${commission.toFixed(2)}`],
        ['Service Fee', `$${service.toFixed(2)}`],
        ['Processing Split', `$${(processing * 0.6).toFixed(2)} LinkUp / $${(processing * 0.4).toFixed(2)} Bank`],
    ];
});

const sync = () => {
    emit('sync-fees', { ...fees });
};

const saveEatsFees = () => {
    localStorage.setItem('linkupEatsFees', JSON.stringify(fees));
    sync();
    note.value = 'Saved - synced to Fees Center & platform revenue.';
    toast.success('Eats fees saved.');
};

const resetEatsFees = () => {
    Object.assign(fees, defaultFees);
    localStorage.removeItem('linkupEatsFees');
    sync();
    note.value = 'Reset to defaults.';
    toast.success('Eats fees reset.');
};

sync();
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black text-orange-600">Fees & Pricing</h3>
            <p class="text-slate-500">Global and country-level settings for commission, delivery, service, gratuity, VAT, and bank processing split.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Restaurant Commission (%)</p>
                <input v-model.number="fees.commission" type="number" step="0.01" min="0" :class="inputClass" @input="sync" />
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Service Fee (%)</p>
                <input v-model.number="fees.service" type="number" step="0.01" min="0" :class="inputClass" @input="sync" />
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Processing Fee (%)</p>
                <input v-model.number="fees.processing" type="number" step="0.01" min="0" :class="inputClass" @input="sync" />
                <p class="mt-1 text-xs text-slate-500">Bank pool - split LinkUp 60% / Scotia 40%</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Default Gratuity (%)</p>
                <input v-model.number="fees.gratuity" type="number" step="0.01" min="0" :class="inputClass" @input="sync" />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button class="rounded-2xl bg-orange-600 px-5 py-3 font-black text-white transition hover:bg-orange-700" @click="saveEatsFees">Save Eats Fees</button>
            <button class="rounded-2xl border border-slate-200 px-5 py-3 font-black transition hover:bg-slate-50" @click="resetEatsFees">Reset</button>
            <span class="text-sm text-slate-500">{{ note }}</span>
        </div>

        <div class="card rounded-3xl p-6">
            <h4 class="mb-4 text-xl font-black">
                Fee Preview
                <span class="text-sm font-bold text-slate-400">(on a $100 order)</span>
            </h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div v-for="item in preview" :key="item[0]" class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-slate-500">{{ item[0] }}</p>
                    <b>{{ item[1] }}</b>
                </div>
            </div>
        </div>

        <p class="text-xs text-slate-500">
            These settings feed the unified <b>Fees Center</b> (LinkUp Eats row) and platform revenue. Commission + Service = LinkUp platform fee;
            Processing = bank fee pool.
        </p>
    </div>
</template>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #eaf0f7;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
}
</style>
