<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import TablePackage from './TablePackage.vue';
import DeleteWarningModal from './DeleteWarningModal.vue';
import DrinkAddOn from './DrinkAddOn.vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import Cookout from './Cookout.vue';
import Wellness from './Wellness.vue';
import { X, Ticket as TicketIcon, Loader2 } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps({
    ticket: {
        type: Object,
        default: () => ({})
    },
    category: {
        type: Array,
        default: () => []
    },
    package: {
        type: Array,
        default: () => []
    },
    drink: {
        type: Object as () => Record<string, any[]>,
        default: () => ({})
    },
    eventTitle: {
        type: String,
        default: ''
    },
    events: {
        type: Array as any,
        default: () => []
    },
    isEventLocked: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close', 'saved', 'deleted']);

// Safe deep clone helper
const safeClone = (obj: any) => {
    if (!obj) return {};
    try {
        return JSON.parse(JSON.stringify(obj));
    } catch (e) {
        return { ...obj };
    }
};

const localTicket = ref<any>(safeClone(props.ticket));
const serverErrors = ref<Record<string, string[]>>({});

const hasAddons = (addons: any) => {
    if (!addons) return false;

    // Check if it's the wrapper object with .items
    if (addons.items) {
        return Object.values(addons.items).some((arr: any) => Array.isArray(arr) && arr.length > 0);
    }

    // Check if it's the raw items object directly
    const possibleCategories = ['mixDrinks', 'wines', 'waters', 'beers', 'softDrinks', 'bottles'];
    if (typeof addons === 'object' && !Array.isArray(addons)) {
        return possibleCategories.some(cat => addons[cat] && Array.isArray(addons[cat]) && addons[cat].length > 0);
    }

    if (Array.isArray(addons)) return addons.length > 0;

    return false;
}

const syncLocalTicket = (data: any) => {
    const cloned = safeClone(data);
    if (hasAddons(cloned.drink_addons)) {
        cloned.has_drink_addons = 'yes';
    } else if (cloned.has_table !== 'yes') {
        cloned.has_drink_addons = 'no';
    }
    localTicket.value = cloned;
};

// Initial sync
syncLocalTicket(props.ticket);

// Sync local state if prop changes (though Index.vue uses :key to re-mount)
watch(() => props.ticket, (newVal) => {
    syncLocalTicket(newVal);
    serverErrors.value = {};
}, { deep: true });

const isProcessing = ref(false);
const showDrinkModal = ref(false);
const openCloeRemoveModal = ref(false);

const isEdit = computed(() => !!localTicket.value.id);
const isCookout = computed(() => localTicket.value.type === 'Cookouts/Food' || localTicket.value.type === 'Cookouts');
const isWellness = computed(() => localTicket.value.type === 'Wellness and Spa');
const isRegularEvent = computed(() => !isCookout.value && !isWellness.value);

const formatDateTimeLocal = (dateString: string | null): string => {
    if (!dateString) return '';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '';
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

const handleSave = async () => {
    isProcessing.value = true;
    serverErrors.value = {};
    try {
        const response = await axios.post(route('organizer.ticket.store.update', localTicket.value));
        toast.success(response.data.message);
        emit('saved', response.data.ticket || response.data.data || localTicket.value);
        emit('close');
    } catch (error: any) {
        if (error.response?.status === 422) {
            serverErrors.value = error.response.data.errors || {};
            toast.error("Please check the form for errors.");
        } else {
            toast.error(error.response?.data?.message || 'An error occurred while saving the ticket.');
        }
    } finally {
        isProcessing.value = false;
    }
}

const openDrinkModal = () => {
    showDrinkModal.value = true;
}

const closeDrinkModal = () => {
    showDrinkModal.value = false;
}

const handleSaveDrinks = (drinks: any) => {
    localTicket.value.drink_addons = drinks;
    localTicket.value.has_drink_addons = "yes";
    closeDrinkModal();
}

const flattenDrinks = (drinkAddons: any) => {
    if (!drinkAddons) return [];

    let items = drinkAddons;
    if (drinkAddons.items) {
        items = drinkAddons.items;
    }

    if (Array.isArray(items)) {
        return items.map(d => ({
            ...d,
            name: d.name || 'Unnamed Drink',
            qty: d.qty || d.quantity || 1,
            cost: d.cost || d.price || 0
        }));
    }

    const possibleCategories = ['mixDrinks', 'wines', 'waters', 'beers', 'softDrinks', 'bottles'];
    const arr: any[] = [];
    for (const cat of possibleCategories) {
        if (items[cat] && Array.isArray(items[cat])) {
            for (const item of items[cat]) {
                arr.push({
                    category: cat,
                    ...item,
                    name: item.name || 'Unnamed Drink',
                    qty: item.qty || item.quantity || 1,
                    cost: item.cost || item.price || 0
                });
            }
        }
    }
    return arr;
}

const removeSelectedDrink = (drinkToRemove: any, index: number) => {
    const addons = localTicket.value.drink_addons;
    if (!addons) return;

    if (Array.isArray(addons)) {
        localTicket.value.drink_addons = addons.filter((_: any, i: number) => i !== index);
    } else {
        const next = safeClone(addons);
        const itemsObj = next.items || next;
        const cat = drinkToRemove.category;

        if (cat && itemsObj[cat] && Array.isArray(itemsObj[cat])) {
            itemsObj[cat] = itemsObj[cat].filter((d: any) => d.name !== drinkToRemove.name);
            localTicket.value.drink_addons = next;
        } else if (!cat && Array.isArray(itemsObj)) {
             // Fallback for case where it's an array but nested
             next.items = itemsObj.filter((_: any, i: number) => i !== index);
             localTicket.value.drink_addons = next;
        }
    }
}

const formatDrinkPrice = (price: string | number, quantity: string | number): string => {
    const total = Number(price) * Number(quantity);
    return total.toFixed(2);
}

const handleTicketDeleted = (id: number) => {
    emit('deleted', id);
    emit('close');
}

const handleTicketUpdate = (updated: any) => {
    Object.assign(localTicket.value, updated);
}

const handleEventChange = () => {
    const selected = props.events.find(e => e.id === localTicket.value.event_id);
    if (selected && selected.category) {
        localTicket.value.type = selected.category;
    }
}

// Ensure mutual exclusivity for Table and Drink Add-ons
watch(() => localTicket.value.has_table, (newVal) => {
    if (newVal === 'yes') {
        localTicket.value.has_drink_addons = 'no';
    }
});

watch(() => localTicket.value.has_drink_addons, (newVal) => {
    if (newVal === 'yes') {
        localTicket.value.has_table = 'no';
    }
});
</script>

<template>
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 overflow-hidden">
        <div class="bg-white rounded-[20px] w-full max-w-3xl max-h-[92vh] overflow-hidden shadow-2xl transform transition-transform flex flex-col">

            <!-- Header matching orgTicketFormModalHTML line 3122 -->
            <div class="-m-1">
                <div class="rounded-t-[19px] overflow-hidden sticky top-0 z-10" style="background:linear-gradient(120deg,#4f46e5,#7c3aed)">
                    <div class="p-5 flex items-start justify-between gap-3 text-white">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="h-11 w-11 rounded-2xl bg-white/20 grid place-items-center shrink-0">
                                <TicketIcon class="w-5 h-5 text-white" />
                            </span>
                            <div class="min-w-0">
                                <h3 class="font-black text-xl truncate text-white">
                                    {{ isEdit ? 'Update Ticket' : 'Add Ticket to Event' }}
                                </h3>
                                <p class="text-white/70 text-xs font-bold">
                                    {{ isEdit ? 'Ticket · ID: ' + String(localTicket.id).replace('TT-','') : 'Configure pricing, availability and details' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <template v-if="isEdit">
                                <button @click="toast.info('Viewing saved data')" class="rounded-xl bg-white/20 hover:bg-white/30 px-3 py-2 text-xs font-black text-white transition">
                                    View Saved
                                </button>
                                <button @click="openCloeRemoveModal = true" class="rounded-xl bg-rose-500 hover:bg-rose-600 px-3 py-2 text-xs font-black text-white transition">
                                    Remove
                                </button>
                            </template>
                            <button @click="$emit('close')" class="h-8 w-8 rounded-lg bg-white/20 hover:bg-white/30 grid place-items-center shrink-0 transition">
                                <X class="w-4 h-4 text-white" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="modal-scroll overflow-y-auto p-5 flex-1">
                <div class="space-y-4">

                    <!-- Basic Info Section (Line 3139) -->
                    <div class="rounded-2xl border border-slate-100 p-4 space-y-4">
                        <p class="text-[11px] font-black text-indigo-500 uppercase tracking-wide">Basic Info</p>

                        <div>
                            <label class="text-sm font-bold">Organizer Category *</label>
                            <select v-model="localTicket.type" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none mt-1" :class="serverErrors.type ? 'border-rose-500' : 'border-slate-200'">
                                <option disabled value="">Select Category</option>
                                <option v-for="cat in props.category" :key="String(cat)" :value="cat">{{ cat }}</option>
                            </select>
                            <p v-if="serverErrors.type" class="text-xs text-rose-500 font-bold mt-1 block ml-1">{{ serverErrors.type[0] }}</p>
                            <p v-else class="text-[11px] text-slate-400 mt-1">Cookout / Wellness sections appear based on category.</p>
                        </div>

                        <div>
                            <label class="text-sm font-bold">Event *</label>
                            <select v-model="localTicket.event_id"
                                :disabled="props.isEventLocked"
                                @change="handleEventChange"
                                class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none mt-1"
                                :class="[
                                    serverErrors.event_id ? 'border-rose-500' : 'border-slate-200',
                                    props.isEventLocked ? 'bg-slate-50 text-slate-500 cursor-not-allowed' : 'bg-white'
                                ]">
                                <option disabled value="0">Select Event</option>
                                <option v-for="event in props.events" :key="event.id" :value="event.id">{{ event.title }}</option>
                            </select>
                            <p v-if="serverErrors.event_id" class="text-xs text-rose-500 font-bold mt-1 block ml-1">{{ serverErrors.event_id[0] }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-bold">Ticket Type *</label>
                            <select v-model="localTicket.ticket_type" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none mt-1" :class="serverErrors.ticket_type ? 'border-rose-500' : 'border-slate-200'">
                                <template v-if="isCookout">
                                    <option value="cookout ticket food included">Cookout Ticket (Food Included)</option>
                                    <option value="cookout ticket entry only">Cookout Ticket (Entry Only)</option>
                                    <option value="vip plate package">VIP Plate Package</option>
                                    <option value="kids plate">Kids Plate</option>
                                    <option value="family bundle">Family Bundle</option>
                                </template>
                                <template v-else-if="isWellness">
                                    <option value="massage focus">Massage Focus</option>
                                    <option value="facial or skincare">Facial / Skincare</option>
                                    <option value="nails">Nails</option>
                                    <option value="full spa day">Full Spa Day</option>
                                    <option value="custom service">Custom Service</option>
                                </template>
                                <template v-else>
                                    <optgroup label="General Admission & Timing">
                                        <option value="general admission">General Admission</option>
                                        <option value="early bird">Early Bird</option>
                                        <option value="late entry">Late Entry</option>
                                        <option value="last minute">Last Minute</option>
                                    </optgroup>
                                    <optgroup label="Premium & Perks">
                                        <option value="vip">VIP</option>
                                        <option value="very vip">VVIP (Very VIP)</option>
                                        <option value="backstage meet greet">Backstage / Meet & Greet</option>
                                        <option value="all access pass">All-Access Pass</option>
                                    </optgroup>
                                    <option value="table with bottles and seating">Table (with bottles, seating)</option>
                                </template>
                            </select>
                            <p v-if="serverErrors.ticket_type" class="text-xs text-rose-500 font-bold mt-1 block ml-1">{{ serverErrors.ticket_type[0] }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-bold">Ticket Name *</label>
                            <input v-model="localTicket.name" placeholder="E.g. VIP Early Bird" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none mt-1" :class="serverErrors.name ? 'border-rose-500' : 'border-slate-200'" />
                            <p v-if="serverErrors.name" class="text-xs text-rose-500 font-bold mt-1 block ml-1">{{ serverErrors.name[0] }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-bold">Description</label>
                            <textarea v-model="localTicket.description" rows="2" placeholder="Tell guests what's included..." class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none mt-1 resize-none"></textarea>
                        </div>
                    </div>

                    <!-- Pricing & Availability (Line 3169) -->
                    <div class="rounded-2xl border border-slate-100 p-4 space-y-4">
                        <p class="text-[11px] font-black text-indigo-500 uppercase tracking-wide">Pricing & Availability</p>

                        <div>
                            <label class="text-sm font-bold">Is this ticket free? *</label>
                            <div class="flex gap-4 mt-1">
                                <label class="flex items-center gap-1.5 text-sm font-bold cursor-pointer">
                                    <input type="radio" value="no" v-model="localTicket.is_free" class="accent-indigo-600" />
                                    No
                                </label>
                                <label class="flex items-center gap-1.5 text-sm font-bold cursor-pointer">
                                    <input type="radio" value="yes" v-model="localTicket.is_free" class="accent-indigo-600" />
                                    Yes
                                </label>
                            </div>
                        </div>

                        <div v-if="localTicket.is_free === 'no'" class="grid grid-cols-2 gap-3 animate-in fade-in duration-200">
                            <div>
                                <label class="text-sm font-bold">Price ($)</label>
                                <input type="number" step="0.01" v-model="localTicket.price" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none font-bold mt-1" :class="serverErrors.price ? 'border-rose-500' : 'border-slate-200'" />
                                <span v-if="serverErrors.price" class="text-[10px] text-rose-500 font-bold block ml-1">{{ serverErrors.price[0] }}</span>
                            </div>
                            <div>
                                <label class="text-sm font-bold">Promo Price ($)</label>
                                <input type="number" step="0.01" v-model="localTicket.promo_price" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none text-slate-500 mt-1" />
                            </div>
                        </div>

                        <!-- Promoter Commission Section -->
                        <div class="rounded-2xl bg-[#f0f7ff] border border-[#e0effe] p-4 space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="text-sm">🤝</span>
                                <h4 class="text-[13px] font-black text-[#1e3a8a]">Promoter commission</h4>
                            </div>
                            <p class="text-[11px] text-[#2563eb] leading-tight font-medium">
                                What creators earn when they tag this ticket on their Live or Vibes. Higher = more creators push your tickets.
                            </p>

                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="localTicket.commMode = 'pct'"
                                    :class="localTicket.commMode === 'pct' ? 'bg-white border-2 border-[#3b82f6] text-[#2563eb] shadow-sm' : 'bg-[#f8fafc] border border-[#e2e8f0] text-[#64748b]'"
                                    class="rounded-xl py-2.5 text-[12px] font-black transition-all">
                                    Percentage
                                </button>
                                <button type="button" @click="localTicket.commMode = 'flat'"
                                    :class="localTicket.commMode === 'flat' ? 'bg-white border-2 border-[#3b82f6] text-[#2563eb] shadow-sm' : 'bg-[#f8fafc] border border-[#e2e8f0] text-[#64748b]'"
                                    class="rounded-xl py-2.5 text-[12px] font-black transition-all">
                                    Flat rate
                                </button>
                                <button type="button" @click="localTicket.commMode = 'none'"
                                    :class="localTicket.commMode === 'none' ? 'bg-white border-2 border-[#3b82f6] text-[#2563eb] shadow-sm' : 'bg-[#f8fafc] border border-[#e2e8f0] text-[#64748b]'"
                                    class="rounded-xl py-2.5 text-[12px] font-black transition-all">
                                    Exclude
                                </button>
                            </div>

                            <div v-if="localTicket.commMode !== 'none'" class="flex items-center gap-3">
                                <input v-if="localTicket.commMode === 'pct'" v-model="localTicket.commission" type="number" min="0" max="50"
                                    class="w-20 rounded-xl border border-[#cbd5e1] bg-white px-3 py-2 text-sm font-black text-center outline-none focus:border-[#3b82f6]"/>
                                <input v-else-if="localTicket.commMode === 'flat'" v-model="localTicket.commFlat" type="number" min="0"
                                    class="w-20 rounded-xl border border-[#cbd5e1] bg-white px-3 py-2 text-sm font-black text-center outline-none focus:border-[#3b82f6]"/>
                                <span class="text-[12px] font-bold text-[#2563eb]">
                                    {{ localTicket.commMode === 'pct' ? '% of each ticket' : '$ per ticket sold' }}
                                </span>
                            </div>

                            <div class="pt-0.5">
                                <p class="text-[11px] font-medium text-[#2563eb]">
                                    <template v-if="localTicket.commMode === 'none'">Creators can still tag this ticket — they just earn nothing.</template>
                                    <template v-else-if="localTicket.commMode === 'flat'">Promoters earn <b class="text-[#1e40af]">${{ Number(localTicket.commFlat || 0).toFixed(2) }}</b> per ticket sold.</template>
                                    <template v-else>Promoters earn <b class="text-[#1e40af]">{{ localTicket.commission || 0 }}%</b>.</template>
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-sm font-bold">Quantity *</label>
                                <input type="number" v-model="localTicket.quantity" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none font-bold mt-1" :class="serverErrors.quantity ? 'border-rose-500' : 'border-slate-200'" />
                                <span v-if="serverErrors.quantity" class="text-[10px] text-rose-500 font-bold block ml-1">{{ serverErrors.quantity[0] }}</span>
                            </div>
                            <div>
                                <label class="text-sm font-bold">Tickets per Attendee</label>
                                <input type="number" v-model="localTicket.tickets_per_attendee" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none mt-1" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-sm font-bold">Sale Starts</label>
                                <input type="datetime-local" :value="formatDateTimeLocal(localTicket.sale_start)" @input="localTicket.sale_start = ($event.target as HTMLInputElement).value" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none mt-1" :class="serverErrors.sale_start ? 'border-rose-500' : 'border-slate-200'" />
                                <span v-if="serverErrors.sale_start" class="text-[10px] text-rose-500 font-bold block ml-1">{{ serverErrors.sale_start[0] }}</span>
                            </div>
                            <div>
                                <label class="text-sm font-bold">Sale Ends</label>
                                <input type="datetime-local" :value="formatDateTimeLocal(localTicket.sale_end)" @input="localTicket.sale_end = ($event.target as HTMLInputElement).value" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none mt-1" :class="serverErrors.sale_end ? 'border-rose-500' : 'border-slate-200'" />
                                <span v-if="serverErrors.sale_end" class="text-[10px] text-rose-500 font-bold block ml-1">{{ serverErrors.sale_end[0] }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-bold">Status</label>
                            <select v-model="localTicket.status" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none mt-1">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="paused">Paused</option>
                                <option value="sold_out">Sold Out</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table & Drinks Section -->
                    <div v-if="isRegularEvent" class="rounded-2xl border border-[#e2e8f0] bg-white p-5 space-y-5">
                        <p class="text-[12px] font-black text-[#4f46e5] uppercase tracking-widest">Table & Drinks</p>

                        <!-- Table Toggle -->
                        <div v-if="localTicket.has_drink_addons !== 'yes'" class="space-y-3">
                            <label class="text-[15px] font-black text-[#1e293b]">Does this ticket include a table?</label>
                            <div class="flex gap-6">
                                <label class="flex items-center gap-2 text-sm font-bold text-[#1e293b] cursor-pointer group">
                                    <div class="relative w-5 h-5 flex items-center justify-center">
                                        <input type="radio" value="no" v-model="localTicket.has_table" class="sr-only" />
                                        <div class="w-5 h-5 rounded-full border-2 transition-all" :class="localTicket.has_table === 'no' ? 'border-[#4f46e5] bg-white' : 'border-[#cbd5e1]'"></div>
                                        <div class="absolute w-2.5 h-2.5 rounded-full bg-[#4f46e5] transition-all scale-0" :class="{ 'scale-100': localTicket.has_table === 'no' }"></div>
                                    </div>
                                    No
                                </label>
                                <label class="flex items-center gap-2 text-sm font-bold text-[#1e293b] cursor-pointer group">
                                    <div class="relative w-5 h-5 flex items-center justify-center">
                                        <input type="radio" value="yes" v-model="localTicket.has_table" class="sr-only" />
                                        <div class="w-5 h-5 rounded-full border-2 transition-all" :class="localTicket.has_table === 'yes' ? 'border-[#4f46e5] bg-white' : 'border-[#cbd5e1]'"></div>
                                        <div class="absolute w-2.5 h-2.5 rounded-full bg-[#4f46e5] transition-all scale-0" :class="{ 'scale-100': localTicket.has_table === 'yes' }"></div>
                                    </div>
                                    Yes
                                </label>
                            </div>
                        </div>

                        <!-- Table Specific Fields -->
                        <div v-if="localTicket.has_table === 'yes'" class="animate-in fade-in slide-in-from-top-2 duration-300">
                            <TablePackage :package="props.package" :drink="props.drink" :ticket="localTicket" @update:ticket="handleTicketUpdate" />
                        </div>

                        <div class="divider border-t border-[#f1f5f9]" v-if="localTicket.has_table !== 'yes' && localTicket.has_drink_addons !== 'yes'"></div>

                        <!-- Drink Add-ons Toggle -->
                        <div v-if="localTicket.has_table !== 'yes'" class="space-y-3">
                            <label class="text-[15px] font-black text-[#1e293b]">Include drink add-ons?</label>
                            <div class="flex gap-6">
                                <label class="flex items-center gap-2 text-sm font-bold text-[#1e293b] cursor-pointer group">
                                    <div class="relative w-5 h-5 flex items-center justify-center">
                                        <input type="radio" value="no" v-model="localTicket.has_drink_addons" class="sr-only" />
                                        <div class="w-5 h-5 rounded-full border-2 transition-all" :class="localTicket.has_drink_addons === 'no' ? 'border-[#4f46e5] bg-white' : 'border-[#cbd5e1]'"></div>
                                        <div class="absolute w-2.5 h-2.5 rounded-full bg-[#4f46e5] transition-all scale-0" :class="{ 'scale-100': localTicket.has_drink_addons === 'no' }"></div>
                                    </div>
                                    No
                                </label>
                                <label class="flex items-center gap-2 text-sm font-bold text-[#1e293b] cursor-pointer group">
                                    <div class="relative w-5 h-5 flex items-center justify-center">
                                        <input type="radio" value="yes" v-model="localTicket.has_drink_addons" class="sr-only" />
                                        <div class="w-5 h-5 rounded-full border-2 transition-all" :class="localTicket.has_drink_addons === 'yes' ? 'border-[#4f46e5] bg-white' : 'border-[#cbd5e1]'"></div>
                                        <div class="absolute w-2.5 h-2.5 rounded-full bg-[#4f46e5] transition-all scale-0" :class="{ 'scale-100': localTicket.has_drink_addons === 'yes' }"></div>
                                    </div>
                                    Yes
                                </label>
                            </div>
                        </div>

                        <div v-if="localTicket.has_drink_addons === 'yes'" class="rounded-2xl bg-indigo-50 p-4 animate-in fade-in duration-300">
                            <p class="font-black mb-2 text-sm text-slate-900">Selected Drinks</p>
                            <div v-if="hasAddons(localTicket.drink_addons)" class="space-y-2">
                                <div v-for="(drink, dIndex) in flattenDrinks(localTicket.drink_addons)" :key="dIndex" class="flex items-center justify-between border-b border-indigo-100 py-2 last:border-0">
                                    <div>
                                        <p class="font-bold text-sm">{{ drink.name }}</p>
                                        <p class="text-[11px] text-slate-400">Qty: {{ drink.qty }}</p>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <b class="text-emerald-600 text-sm">${{ formatDrinkPrice(drink.cost, drink.qty) }}</b>
                                        <button @click="removeSelectedDrink(drink, dIndex)" class="text-rose-600 text-xs font-black">Remove</button>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-slate-400 text-sm mb-3">No drinks selected</p>
                            <button @click="openDrinkModal" class="btn px-4 py-2 text-xs font-black text-white mt-3 bg-indigo-600 rounded-xl hover:bg-indigo-700 transition shadow-md">Edit Drinks</button>
                        </div>
                    </div>

                    <!-- Cookout Section (Line 3217) -->
                    <div v-if="isCookout" class="sectionCard p-4 animate-in zoom-in-95 duration-300">
                        <Cookout :ticket="localTicket" />
                    </div>

                    <!-- Wellness Section (Line 3218) -->
                    <div v-if="isWellness" class="sectionCard p-4 animate-in zoom-in-95 duration-300">
                        <Wellness :ticket="localTicket" />
                    </div>
                </div>

                <!-- Footer Actions matching orgTicketFormModalHTML line 3219 -->
                <div class="flex gap-2 mt-5">
                    <button @click="$emit('close')" class="btn btn-ghost flex-1 py-2.5 font-black border border-slate-200 rounded-xl hover:bg-slate-50 transition">Cancel</button>
                    <button @click="handleSave" :disabled="isProcessing" class="btn flex-1 py-2.5 font-black text-white rounded-xl transition hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 flex items-center justify-center gap-2" style="background:linear-gradient(120deg,#4f46e5,#7c3aed)">
                        <Loader2 v-if="isProcessing" class="w-4 h-4 animate-spin" />
                        {{ isEdit ? 'Save Ticket' : 'Add Ticket' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Sub-Modals -->
        <div v-if="openCloeRemoveModal" class="fixed inset-0 flex items-center justify-center bg-black/40 z-[60]">
            <DeleteWarningModal @close="openCloeRemoveModal = false" @deleted="handleTicketDeleted" :ticketId="localTicket.id" />
        </div>

        <DrinkAddOn v-if="showDrinkModal" @close="closeDrinkModal" @save-drinks="handleSaveDrinks" :drinks="props.drink" :ticket="localTicket" />
    </div>
</template>

<style scoped>
.modal-scroll {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
.modal-scroll::-webkit-scrollbar {
    width: 8px;
}
.modal-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.modal-scroll::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
    border: 2px solid white;
    background-clip: padding-box;
}
.modal-scroll::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8;
}

/* Animations */
.animate-in {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}
.fade-in {
    animation-name: fadeIn;
}
.slide-in-from-top-2 {
    animation-name: slideInFromTop;
}
.zoom-in-95 {
    animation-name: zoomIn95;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInFromTop {
    from { transform: translateY(-0.5rem); }
    to { transform: translateY(0); }
}

@keyframes zoomIn95 {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>
