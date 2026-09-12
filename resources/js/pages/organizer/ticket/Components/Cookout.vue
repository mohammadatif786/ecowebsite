<script setup lang="ts">
import { COOKOUT_PROTEINS, COOKOUT_SIDES, COOKOUT_DRINKS } from '../../constants/cookout_wellness_services'
import { onMounted, watch, ref } from 'vue';

const props = defineProps({
    ticket: {
        type: Object,
        default: () => ({}),
    }
})

// Initialize cookout setup as always enabled for cookout tickets
const initCookout = () => {
    if (!props.ticket.cookout) {
        props.ticket.cookout = { includeFood: 'yes' };
    }
    if (!props.ticket.cookout.includeFood) {
        props.ticket.cookout.includeFood = 'yes';
    }
    if (!Array.isArray(props.ticket.cookout.proteins)) props.ticket.cookout.proteins = [];
    if (!Array.isArray(props.ticket.cookout.sides)) props.ticket.cookout.sides = [];
    if (!Array.isArray(props.ticket.cookout.drinks)) props.ticket.cookout.drinks = [];
    if (!Array.isArray(props.ticket.cookout.manualAddons)) props.ticket.cookout.manualAddons = [];
    if (!Array.isArray(props.ticket.cookout.customSides)) {
        props.ticket.cookout.customSides = [];
    } else {
        // Ensure checked status is boolean
        props.ticket.cookout.customSides.forEach((s: any) => {
            if (s.checked === undefined) s.checked = true;
            else s.checked = Boolean(s.checked);
        });
    }

    if (!Array.isArray(props.ticket.cookout.customProteins)) {
        props.ticket.cookout.customProteins = [];
    } else {
        // Ensure checked status is boolean
        props.ticket.cookout.customProteins.forEach((p: any) => {
            if (p.checked === undefined) p.checked = true;
            else p.checked = Boolean(p.checked);
        });
    }
};

const getCookoutProtein = (itemName: string) => {
    return props.ticket.cookout.proteins.find((x: any) => x.name === itemName);
};

const toggleCookoutProtein = (itemName: string, checked: boolean) => {
    if (checked) {
        if (!props.ticket.cookout.proteins.some((x: any) => x.name === itemName)) {
            const defaults = COOKOUT_PROTEINS.find(p => p.name === itemName) || { defaultQty: 1, defaultMode: 'included', defaultPrice: 0 };
            props.ticket.cookout.proteins.push({
                name: itemName,
                qty: defaults.defaultQty || 1,
                mode: defaults.defaultMode || 'included',
                price: defaults.defaultPrice || 0,
            });
        }
    } else {
        props.ticket.cookout.proteins = props.ticket.cookout.proteins.filter((x: any) => x.name !== itemName);
    }
};

const isCookoutProteinChecked = (itemName: string) => {
    return props.ticket.cookout?.proteins?.some((x: any) => x.name === itemName);
};

const toggleCookoutSide = (itemName: string, checked: boolean) => {
    if (checked) {
        if (!props.ticket.cookout.sides.includes(itemName)) {
            props.ticket.cookout.sides.push(itemName);
        }
    } else {
        props.ticket.cookout.sides = props.ticket.cookout.sides.filter((x: any) => x !== itemName);
    }
};

const isCookoutSideChecked = (itemName: string) => {
    return props.ticket.cookout?.sides?.includes(itemName);
};

const getCookoutDrink = (itemName: string) => {
    return props.ticket.cookout.drinks.find((x: any) => x.name === itemName);
};

const toggleCookoutDrink = (itemName: string, checked: boolean) => {
    if (checked) {
        if (!props.ticket.cookout.drinks.some((x: any) => x.name === itemName)) {
            const defaults = COOKOUT_DRINKS.find(d => d.name === itemName) || { defaultQty: 1, defaultMode: 'addon', defaultPrice: 5 };
            props.ticket.cookout.drinks.push({
                name: itemName,
                mode: defaults.defaultMode || 'addon',
                price: defaults.defaultPrice || 0,
                qty: 1,
            });
        }
    } else {
        props.ticket.cookout.drinks = props.ticket.cookout.drinks.filter((x: any) => x.name !== itemName);
    }
};

const isCookoutDrinkChecked = (itemName: string) => {
    return props.ticket.cookout?.drinks?.some((x: any) => x.name === itemName);
};

const customProteinName = ref('');
const addCustomProtein = () => {
    if (!customProteinName.value.trim()) return;
    props.ticket.cookout.customProteins.push({
        name: customProteinName.value,
        checked: true,
        qty: 1,
        mode: 'addon',
        price: 0
    });
    customProteinName.value = '';
};

const removeCustomProtein = (index: number) => {
    props.ticket.cookout.customProteins.splice(index, 1);
};

const customSideName = ref('');
const addCustomSide = () => {
    if (!customSideName.value.trim()) return;
    props.ticket.cookout.customSides.push({ name: customSideName.value, checked: true });
    customSideName.value = '';
};

const removeCustomSide = (index: number) => {
    props.ticket.cookout.customSides.splice(index, 1);
};

const addCookoutAddon = () => {
    props.ticket.cookout.manualAddons.push({ name: '', price: 0, qty: 1 });
};

const removeCookoutAddon = (index: number) => {
    props.ticket.cookout.manualAddons.splice(index, 1);
};

const applyCookoutPreset = () => {
    const key = props.ticket.cookout.cuisinePreset;
    const presets: Record<string, { proteins?: string[]; sides?: string[]; drinks?: string[]; manualAddons?: { name: string; price: number; qty: number }[] }> = {
        trinidad: { proteins: ["Curry Chicken", "Oxtail"], sides: ["Pelau (Trinidad)", "Rice & Peas"], drinks: ["Sorrel"], manualAddons: [{ name: "Doubles", price: 6, qty: 1 }] },
        barbados: { proteins: ["Fried Fish", "Curry Chicken"], sides: ["Cou-Cou (Barbados)", "Macaroni (Mac Pie)"], drinks: ["Mauby"] },
        jamaica: { proteins: ["Jerk Chicken", "Brown Stew Fish"], sides: ["Rice & Peas", "Callaloo"], drinks: ["Sorrel"] },
        brazil: { proteins: ["Brazilian Picanha", "Feijoada (Brazil)"], sides: ["Rice"], drinks: ["Juice"] },
        bahamas: { proteins: ["Fried Fish"], sides: ["Johnny Cake", "Conch Salad (Bahamas)"], drinks: ["Coconut Water"] }
    };

    const p = presets[key];
    if (!p) return;

    if (p.proteins) p.proteins.forEach(n => toggleCookoutProtein(n, true));
    if (p.sides) p.sides.forEach(n => toggleCookoutSide(n, true));
    if (p.drinks) p.drinks.forEach(n => toggleCookoutDrink(n, true));
    if (p.manualAddons) {
        p.manualAddons.forEach(a => {
             if (!props.ticket.cookout.manualAddons.some((existing: any) => existing.name === a.name)) {
                 props.ticket.cookout.manualAddons.push({ ...a });
             }
        });
    }
};

onMounted(initCookout);
watch(() => props.ticket, initCookout, { immediate: true });
</script>

<template>
    <div class="rounded-2xl border border-orange-100 bg-[#fffcf5] p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">🔥</span>
                <h3 class="text-[17px] font-black text-slate-900 tracking-tight">Cookout Menu Setup</h3>
            </div>
            <span class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-[11px] font-black text-slate-500 uppercase tracking-wider shadow-sm">
                Cookout only
            </span>
        </div>

        <!-- Cuisine Preset -->
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="text-lg">🌍</span>
                <h4 class="text-[14px] font-black text-slate-800">Cuisine Preset (Auto-check)</h4>
            </div>
            <select v-model="ticket.cookout.cuisinePreset" @change="applyCookoutPreset"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-400 transition-all appearance-none cursor-pointer shadow-sm">
                <option value="">— Select —</option>
                <option value="trinidad">🇹🇹 Trinidad</option>
                <option value="barbados">🇧🇧 Barbados</option>
                <option value="jamaica">🇯🇲 Jamaica</option>
                <option value="brazil">🇧🇷 Brazil</option>
                <option value="bahamas">🇧🇸 Bahamas</option>
            </select>
            <p class="text-[11px] text-slate-400 font-medium italic">
                This doesn't remove anything — it just pre-selects a regional vibe.
            </p>
        </div>

        <!-- Proteins Section -->
        <div class="space-y-4">
            <h4 class="text-[15px] font-black text-slate-900 tracking-tight">Proteins (Caribbean + Latin)</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div v-for="item in COOKOUT_PROTEINS" :key="item.name"
                    class="relative group">
                    <div :class="[
                        'flex flex-col p-4 rounded-xl border transition-all select-none',
                        isCookoutProteinChecked(item.name) ? 'bg-white border-blue-400 shadow-sm ring-1 ring-blue-100' : 'bg-white/60 border-slate-100 hover:border-slate-200'
                    ]">
                        <label class="flex items-center gap-3 cursor-pointer mb-0">
                            <input type="checkbox" :checked="isCookoutProteinChecked(item.name)" @change="e => toggleCookoutProtein(item.name, (e.target as HTMLInputElement).checked)" class="sr-only" />
                            <div :class="['w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all', isCookoutProteinChecked(item.name) ? 'bg-blue-500 border-blue-500' : 'bg-white border-slate-200 group-hover:border-slate-300']">
                                <svg v-if="isCookoutProteinChecked(item.name)" class="w-3 h-3 text-white fill-current" viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/></svg>
                            </div>
                            <span class="text-[13px] font-bold text-slate-700">{{ item.name }}</span>
                        </label>

                        <!-- Expanded Details for Proteins -->
                        <div v-if="isCookoutProteinChecked(item.name)" class="mt-4 grid grid-cols-3 gap-3 animate-in fade-in slide-in-from-top-2">
                             <div>
                                 <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Qty</label>
                                 <input type="number" v-model="getCookoutProtein(item.name).qty" min="1" class="w-full rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-sm font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-300" />
                             </div>
                             <div>
                                 <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Mode</label>
                                 <select v-model="getCookoutProtein(item.name).mode" class="w-full rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-sm font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-300">
                                     <option value="included">Included</option>
                                     <option value="addon">Add-on</option>
                                 </select>
                             </div>
                             <div>
                                 <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Add-on $</label>
                                 <input type="number" v-model="getCookoutProtein(item.name).price" :disabled="getCookoutProtein(item.name).mode === 'included'" class="w-full rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-sm font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-300 disabled:opacity-50" />
                             </div>
                             <div class="col-span-3 text-[10px] text-slate-400 mt-1">
                                 Add-on Price applies only if mode is Add-on.
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Protein Input Area -->
            <div class="space-y-2">
                <div v-for="(custom, idx) in ticket.cookout.customProteins" :key="idx" class="flex flex-col p-4 bg-white border border-blue-200 rounded-xl shadow-sm space-y-4 animate-in fade-in slide-in-from-left-2">
                     <div class="flex items-center gap-3">
                         <input type="checkbox" v-model="custom.checked" class="w-5 h-5 accent-blue-500 rounded-md" />
                         <input type="text" v-model="custom.name" placeholder="Custom Protein Name" class="flex-1 bg-slate-50 border border-slate-100 rounded-lg px-3 py-2 text-[13px] font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-300" />
                         <button @click="removeCustomProtein(idx)" class="text-rose-500 hover:text-rose-600 transition-colors">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                         </button>
                     </div>
                     <div v-if="custom.checked" class="grid grid-cols-3 gap-3 animate-in fade-in">
                          <div>
                              <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Qty</label>
                              <input type="number" v-model="custom.qty" min="1" class="w-full rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-sm font-bold text-slate-700 outline-none" />
                          </div>
                          <div>
                              <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Mode</label>
                              <select v-model="custom.mode" class="w-full rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-sm font-bold text-slate-700 outline-none">
                                  <option value="included">Included</option>
                                  <option value="addon">Add-on</option>
                              </select>
                          </div>
                          <div>
                              <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Add-on $</label>
                              <input type="number" v-model="custom.price" :disabled="custom.mode === 'included'" class="w-full rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-sm font-bold text-slate-700 outline-none disabled:opacity-50" />
                          </div>
                     </div>
                </div>
                <div class="rounded-2xl border border-dashed border-slate-200 p-2 flex items-center gap-3 group">
                    <input type="text" v-model="customProteinName" placeholder="Add Custom Protein" class="flex-1 bg-transparent border-none outline-none px-3 py-2 text-[13px] font-medium text-slate-500 placeholder:text-slate-300" @keyup.enter="addCustomProtein" />
                    <button @click="addCustomProtein" class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-900 text-[12px] font-black hover:bg-slate-50 transition-all shadow-sm">
                        + Add Protein
                    </button>
                </div>
            </div>
        </div>

        <!-- Sides Section -->
        <div class="space-y-4 pt-4 border-t border-orange-50">
            <h4 class="text-[15px] font-black text-slate-900 tracking-tight">Sides</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <div v-for="item in COOKOUT_SIDES" :key="item.name" class="relative group">
                    <label :class="[
                        'flex items-center gap-3 p-4 rounded-xl border transition-all cursor-pointer select-none',
                        isCookoutSideChecked(item.name) ? 'bg-white border-blue-400 shadow-sm ring-1 ring-blue-100' : 'bg-white/60 border-slate-100 hover:border-slate-200'
                    ]">
                        <input type="checkbox" :checked="isCookoutSideChecked(item.name)" @change="e => toggleCookoutSide(item.name, (e.target as HTMLInputElement).checked)" class="sr-only" />
                        <div :class="['w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all', isCookoutSideChecked(item.name) ? 'bg-blue-500 border-blue-500' : 'bg-white border-slate-200 group-hover:border-slate-300']">
                            <svg v-if="isCookoutSideChecked(item.name)" class="w-3 h-3 text-white fill-current" viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/></svg>
                        </div>
                        <span class="text-[13px] font-bold text-slate-700">{{ item.name }}</span>
                    </label>
                </div>
            </div>

            <!-- Custom Sides Area -->
            <div class="space-y-2">
                <div v-for="(custom, idx) in ticket.cookout.customSides" :key="idx" class="flex items-center gap-2 animate-in fade-in slide-in-from-left-2">
                     <div class="flex-1 flex items-center gap-3 p-3 bg-white border border-blue-200 rounded-xl">
                         <input type="checkbox" v-model="custom.checked" class="w-5 h-5 accent-blue-500" />
                         <input type="text" v-model="custom.name" placeholder="Custom Side Name" class="flex-1 bg-transparent border-none outline-none text-[13px] font-bold text-slate-700 placeholder:text-slate-300" />
                     </div>
                     <button @click="removeCustomSide(idx)" class="text-rose-500 hover:text-rose-600 transition-colors p-2">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                     </button>
                </div>
                <div class="rounded-2xl border border-dashed border-slate-200 p-2 flex items-center gap-3 group">
                    <input type="text" v-model="customSideName" placeholder="Add Custom Side" class="flex-1 bg-transparent border-none outline-none px-3 py-2 text-[13px] font-medium text-slate-500 placeholder:text-slate-300" @keyup.enter="addCustomSide" />
                    <button @click="addCustomSide" class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-900 text-[12px] font-black hover:bg-slate-50 transition-all shadow-sm">
                        + Add Side
                    </button>
                </div>
            </div>
        </div>

        <!-- Drinks Section -->
        <div class="space-y-4 pt-4 border-t border-orange-50">
            <h4 class="text-[15px] font-black text-slate-900 tracking-tight">Drinks</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div v-for="item in COOKOUT_DRINKS" :key="item.name" class="relative group">
                    <div :class="[
                        'flex flex-col p-4 rounded-xl border transition-all select-none',
                        isCookoutDrinkChecked(item.name) ? 'bg-white border-blue-400 shadow-sm ring-1 ring-blue-100' : 'bg-white/60 border-slate-100 hover:border-slate-200'
                    ]">
                        <label class="flex items-center gap-3 cursor-pointer mb-0">
                            <input type="checkbox" :checked="isCookoutDrinkChecked(item.name)" @change="e => toggleCookoutDrink(item.name, (e.target as HTMLInputElement).checked)" class="sr-only" />
                            <div :class="['w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all', isCookoutDrinkChecked(item.name) ? 'bg-blue-500 border-blue-500' : 'bg-white border-slate-200 group-hover:border-slate-300']">
                                <svg v-if="isCookoutDrinkChecked(item.name)" class="w-3 h-3 text-white fill-current" viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/></svg>
                            </div>
                            <span class="text-[13px] font-bold text-slate-700">{{ item.name }}</span>
                        </label>

                        <!-- Expanded details for drinks -->
                        <div v-if="isCookoutDrinkChecked(item.name)" class="mt-4 flex items-center gap-2 animate-in fade-in slide-in-from-top-2">
                             <select v-model="getCookoutDrink(item.name).mode" class="w-24 rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-[11px] font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-300">
                                 <option value="included">Incl.</option>
                                 <option value="addon">Add-on</option>
                             </select>
                             <div class="relative flex-1 max-w-[80px]">
                                 <input type="number" v-model="getCookoutDrink(item.name).price" :disabled="getCookoutDrink(item.name).mode === 'included'" class="w-full rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-[11px] font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-300 disabled:opacity-50" placeholder="0" />
                             </div>
                             <input type="number" v-model="getCookoutDrink(item.name).qty" min="1" class="w-12 rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-[11px] font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-300" placeholder="1" />
                             <button @click="toggleCookoutDrink(item.name, false)" class="text-rose-500 text-[10px] font-black uppercase hover:text-rose-600 transition-colors">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Add-ons -->
        <div class="space-y-4 pt-4 border-t border-orange-50">
            <div class="flex items-center justify-between">
                <h4 class="text-[15px] font-black text-slate-900 tracking-tight">+ Manual Food Add-ons</h4>
                <button @click="addCookoutAddon" class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-900 text-[12px] font-black hover:bg-slate-50 transition-all shadow-sm">
                    Add Add-on
                </button>
            </div>

            <div v-for="(addon, idx) in ticket.cookout.manualAddons" :key="idx" class="p-4 bg-white rounded-xl border border-blue-100 shadow-sm animate-in fade-in slide-in-from-top-2">
                <div class="flex flex-col sm:flex-row gap-3">
                    <input v-model="addon.name" type="text" placeholder="asfd" class="flex-1 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-sm font-medium outline-none focus:border-blue-300 focus:bg-white transition-all" />
                    <div class="flex gap-2 items-center">
                        <input v-model="addon.qty" type="number" min="1" class="w-16 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 outline-none" />
                        <input v-model="addon.price" type="number" step="0.01" class="w-24 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-sm font-bold text-emerald-600 outline-none" />
                        <button @click="removeCookoutAddon(idx)" class="text-rose-500 p-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                </div>
            </div>

            <p class="text-[11px] text-slate-400 font-medium italic">
                Manual add-ons count toward checkout total (Price × Qty).
            </p>
        </div>
    </div>
</template>

<style scoped>
.animate-in {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}
.fade-in {
    animation-name: fadeIn;
}
.slide-in-from-left-2 {
    animation-name: slideInFromLeft;
}
.slide-in-from-top-2 {
    animation-name: slideInFromTop;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInFromLeft {
    from { transform: translateX(-0.5rem); }
    to { transform: translateX(0); }
}

@keyframes slideInFromTop {
    from { transform: translateY(-0.5rem); }
    to { transform: translateY(0); }
}
</style>
