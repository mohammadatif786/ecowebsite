<script setup lang="ts">

import { ref, watch } from 'vue';
import DrinkTableSelector from './DrinkTableSelector.vue';
import { TABLE_DRINK_PACKAGES } from '../../constants/cookout_wellness_services';

interface DrinkItem {
    id: any;
    name: any;
    amount: any;
    qty: any;
}

const selectedBottles = ref<DrinkItem[]>([]);
const selectedChasers = ref<DrinkItem[]>([]);
const selectedWaters = ref<DrinkItem[]>([]);
const sections = ref<any[]>([]);
const selectedNote = ref('');

const props = defineProps<{
    package: any[];
    drink: Record<string, any[]>;
    ticket: Record<string, any>;
}>();

const selectedPackageId = ref<number | string | null>(null);

const applyTablePreset = (presetName: string) => {
    const pkg = TABLE_DRINK_PACKAGES[presetName as keyof typeof TABLE_DRINK_PACKAGES];
    if (!pkg) return;

    selectedBottles.value = pkg.bottles.map(b => ({ ...b, id: null, amount: 0 }));
    selectedChasers.value = pkg.chasers.map(c => ({ ...c, id: null, amount: 0 }));
    selectedWaters.value = pkg.waters.map(w => ({ ...w, id: null, amount: 0 }));
    selectedNote.value = pkg.notes;
};

const emit = defineEmits(['update:ticket'])

const mapPackageToTicket = (pkg: any) => {
    const normalize = (items: any[] = []) => {
        return items.map(i => ({
            id: i.id,
            name: i.name,
            amount: i.amount || 0,
            qty: i.qty || 1
        }))
    }

    return {
        main_bottles: normalize(pkg.bottles),
        chasers_or_mixers: normalize(pkg.chasers),
        water_options: normalize(pkg.waters),
        notes: pkg.notes || ''
    }
}

watch(() => props.ticket, (newTicket) => {
    if (!newTicket) return

    const pkgId = newTicket.package_id ? Number(newTicket.package_id) : null
    selectedPackageId.value = pkgId

    if (pkgId) {
        // Load from package
        const pkg = props.package.find(p => p.id === pkgId)
        console.log('Found package:', pkg, 'for id:', pkgId)
        if (pkg) {
            const mapped = mapPackageToTicket(pkg)
            console.log('Mapped package data:', mapped)
            selectedBottles.value = mapped.main_bottles
            selectedChasers.value = mapped.chasers_or_mixers
            selectedWaters.value = mapped.water_options
            selectedNote.value = mapped.notes
        }
    } else {
        // Load from ticket (custom selection)
        selectedBottles.value = newTicket.main_bottles || []
        selectedChasers.value = newTicket.chasers_or_mixers || []
        selectedWaters.value = newTicket.water_options || []
    }

    sections.value = newTicket.sections || []
}, { immediate: true, deep: true })

watch(selectedPackageId, (val) => {
    if (!val) {
        selectedBottles.value = []
        selectedChasers.value = []
        selectedWaters.value = []
        selectedNote.value = ''
        return
    }

    if (typeof val === 'string') {
        applyTablePreset(val);
        return;
    }

    const pkg = props.package.find(p => p.id === val)
    if (!pkg) return

    const mapped = mapPackageToTicket(pkg)

    if (mapped.main_bottles.length > 0 || mapped.chasers_or_mixers.length > 0 || mapped.water_options.length > 0) {
        selectedBottles.value = mapped.main_bottles
        selectedChasers.value = mapped.chasers_or_mixers
        selectedWaters.value = mapped.water_options
        selectedNote.value = mapped.notes
    }
})

watch(
    [selectedBottles, selectedChasers, selectedWaters, sections, selectedNote, selectedPackageId],
    () => {
        const updated = {
            ...props.ticket,
            package_id: selectedPackageId.value,
            main_bottles: selectedBottles.value,
            chasers_or_mixers: selectedChasers.value,
            water_options: selectedWaters.value,
            sections: sections.value,
            notes: selectedNote.value,
        }

        if (JSON.stringify(updated) !== JSON.stringify(props.ticket)) {
            emit('update:ticket', updated)
        }
    },
    { deep: true }
)

</script>
<template>
    <div class="rounded-2xl bg-[#f8fafc] border border-[#e2e8f0] p-5 space-y-6 mt-2">
        <!-- Note Banner -->
        <div class="rounded-xl bg-[#f1f5f9] p-4">
            <p class="text-[12px] text-[#64748b] font-medium leading-relaxed">
                <span class="font-black">Note:</span> Table tickets auto-set type to "Table".
            </p>
            <input type="hidden" value="Table" />
        </div>

        <!-- Table Price -->
        <div>
            <label class="text-sm font-black text-[#1e293b] mb-2 block">Table Price</label>
            <input min="0" step="0.01" v-model="ticket.table_price" type="number"
                class="w-full rounded-xl border border-[#e2e8f0] bg-white px-4 py-3 text-sm font-medium outline-none focus:border-[#4f46e5] focus:ring-1 focus:ring-[#4f46e5] transition-all" />
        </div>

        <!-- People per table -->
        <div>
            <label class="text-sm font-black text-[#1e293b] mb-2 block">People per table</label>
            <input type="number" min="0" v-model="ticket.table_capacity"
                class="w-full rounded-xl border border-[#e2e8f0] bg-white px-4 py-3 text-sm font-medium outline-none focus:border-[#4f46e5] focus:ring-1 focus:ring-[#4f46e5] transition-all" />
        </div>

        <!-- Drink Package Subsection -->
        <div class="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-5 space-y-4">
            <div class="flex items-center gap-2">
                <span class="text-lg">📦</span>
                <h4 class="text-[15px] font-black text-[#1e293b]">Drink Package (Optional)</h4>
            </div>

            <select v-model="selectedPackageId"
                class="w-full rounded-xl border border-[#e2e8f0] bg-white px-4 py-3 text-sm font-medium outline-none focus:border-[#4f46e5] focus:ring-1 focus:ring-[#4f46e5] transition-all appearance-none cursor-pointer">
                <option :value="null">— None —</option>
                <optgroup label="Saved Packages" v-if="package && package.length">
                    <option v-for="pkg in package" :key="pkg.id" :value="pkg.id">
                        {{ pkg.name }}
                    </option>
                </optgroup>
                <optgroup label="Quick Presets">
                    <option v-for="(data, name) in TABLE_DRINK_PACKAGES" :key="name" :value="name">
                        {{ name }}
                    </option>
                </optgroup>
            </select>

            <!-- Package Contents Card -->
            <div v-if="selectedPackageId" class="bg-white border border-[#e2e8f0] rounded-2xl p-5 animate-in fade-in slide-in-from-top-2 duration-300">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-lg">📋</span>
                    <h5 class="text-[14px] font-black text-[#7c3aed]">Package Contents:</h5>
                </div>

                <div class="space-y-4">
                    <!-- Bottles -->
                    <div v-if="selectedBottles.length > 0">
                        <p class="text-[10px] font-black text-[#94a3b8] uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                            🍾 MAIN BOTTLES
                        </p>
                        <div v-for="(item, idx) in selectedBottles" :key="idx" class="text-[13px] font-bold text-[#1e293b] leading-relaxed">
                            {{ item.name }} (Qty: {{ item.qty || item.quantity || 1 }})
                        </div>
                    </div>

                    <!-- Chasers -->
                    <div v-if="selectedChasers.length > 0">
                        <p class="text-[10px] font-black text-[#94a3b8] uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                            🥤 CHASERS / MIXERS
                        </p>
                        <div v-for="(item, idx) in selectedChasers" :key="idx" class="text-[13px] font-bold text-[#1e293b] leading-relaxed">
                            {{ item.name }} (Qty: {{ item.qty || item.quantity || 1 }})
                        </div>
                    </div>

                    <!-- Water -->
                    <div v-if="selectedWaters.length > 0">
                        <p class="text-[10px] font-black text-[#94a3b8] uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                            💧 WATER OPTIONS
                        </p>
                        <div v-for="(item, idx) in selectedWaters" :key="idx" class="text-[13px] font-bold text-[#1e293b] leading-relaxed">
                            {{ item.name }} (Qty: {{ item.qty || item.quantity || 1 }})
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Seating Sections -->
        <div class="space-y-4">
            <label class="text-sm font-black text-[#1e293b] block">Table Seating Sections</label>

            <div class="space-y-3">
                <div v-for="(section, index) in sections" :key="index" class="flex gap-2">
                    <input type="text" v-model="section.name" placeholder="E.g. VIP Front Row"
                        class="flex-1 rounded-xl border border-[#e2e8f0] bg-white px-4 py-3 text-sm font-medium outline-none focus:border-[#4f46e5] focus:ring-1 focus:ring-[#4f46e5] transition-all" />
                    <button type="button" @click="sections.splice(index, 1)"
                        class="px-4 py-2 rounded-xl bg-[#e11d48] text-white text-[12px] font-black hover:bg-[#be123c] transition-all shrink-0">
                        Remove
                    </button>
                </div>
            </div>

            <button @click="sections.push({ name: '' })" type="button"
                class="w-full py-4 rounded-xl bg-[#4f46e5] text-white text-sm font-black hover:bg-[#4338ca] transition-all shadow-md active:scale-[0.98]">
                + Add Seating Section
            </button>
        </div>
    </div>
</template>
