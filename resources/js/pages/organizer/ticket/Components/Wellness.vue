<script setup lang="ts">
import { WELLNESS_SERVICES } from '../../constants/cookout_wellness_services'
import axios from 'axios';
import { route } from 'ziggy-js'
import { onMounted, watch, ref } from 'vue';

const props = defineProps({
    ticket: {
        type: Object,
        default: () => ({})
    }
})

const safeInt = (n: any) => {
    const x = parseInt(String(n), 10);
    return Number.isFinite(x) ? x : 0;
};

const num = (n: any) => {
    const x = Number(n);
    return Number.isFinite(x) ? x : 0;
};

const initWellness = () => {
    if (!props.ticket.wellness) {
        props.ticket.wellness = { includeService: 'yes' };
    }
    if (!props.ticket.wellness.includeService) {
        props.ticket.wellness.includeService = 'yes';
    }
    if (!props.ticket.wellness.booking) props.ticket.wellness.booking = {};
    if (!Array.isArray(props.ticket.wellness.services)) props.ticket.wellness.services = [];
    if (!Array.isArray(props.ticket.wellness.manualAddons)) props.ticket.wellness.manualAddons = [];
    if (!Array.isArray(props.ticket.wellness.customServices)) {
        props.ticket.wellness.customServices = [];
    } else {
        // Ensure checked status is boolean
        props.ticket.wellness.customServices.forEach((s: any) => {
            if (s.checked === undefined) s.checked = true;
            else s.checked = Boolean(s.checked);
        });
    }

    // Default booking settings
    if (props.ticket.wellness.booking.duration == null) props.ticket.wellness.booking.duration = 60;
    if (props.ticket.wellness.booking.buffer == null) props.ticket.wellness.booking.buffer = 15;
    if (props.ticket.wellness.booking.maxPerSlot == null) props.ticket.wellness.booking.maxPerSlot = 1;
    if (!props.ticket.wellness.booking.mode) props.ticket.wellness.booking.mode = 'studio';
    if (!props.ticket.wellness.booking.slotDate) props.ticket.wellness.booking.slotDate = new Date().toISOString().slice(0, 10);
    if (!props.ticket.wellness.booking.slotStart) props.ticket.wellness.booking.slotStart = '09:00';
    if (!props.ticket.wellness.booking.slotEnd) props.ticket.wellness.booking.slotEnd = '18:00';
    if (!Array.isArray(props.ticket.wellness.booking.slots)) props.ticket.wellness.booking.slots = [];
};

const toggleWellnessService = (itemName: string, checked: boolean) => {
    if (checked) {
        if (!props.ticket.wellness.services.some((x: any) => x.name === itemName)) {
            const defaults = WELLNESS_SERVICES.find(s => s.name === itemName) || { defaultMode: 'included', defaultPrice: 0, duration: 60 };
            props.ticket.wellness.services.push({
                name: itemName,
                mode: defaults.defaultMode,
                price: defaults.defaultPrice,
                duration: props.ticket.wellness.booking.duration || defaults.duration
            });
        }
    } else {
        props.ticket.wellness.services = props.ticket.wellness.services.filter((x: any) => x.name !== itemName);
    }
};

const isWellnessServiceChecked = (itemName: string) => {
    return props.ticket.wellness?.services?.some((x: any) => x.name === itemName);
};

const getWellnessServiceData = (itemName: string) => {
    return props.ticket.wellness.services?.find((x: any) => x.name === itemName);
};

const applyWellnessPreset = () => {
    const key = props.ticket.wellness.preset;
    const presets: Record<string, string[]> = {
        massage: ["Swedish Massage", "Deep Tissue Upgrade", "Hot Stone Upgrade"],
        facial: ["Classic Facial"],
        nails: ["Manicure", "Pedicure"],
        full: ["Swedish Massage", "Classic Facial", "Manicure", "Pedicure"],
        spa_essentials: ["Swedish Massage", "Classic Facial", "Manicure"],
        bridal_prep: ["Classic Facial", "Manicure", "Pedicure"],
        sports_recovery: ["Sports Massage", "Deep Tissue Upgrade", "Hot Stone Upgrade"],
        prenatal_care: ["Prenatal Massage", "Foot Massage"]
    };
    const checks = presets[key];
    if (checks) {
        checks.forEach(n => toggleWellnessService(n, true));
    }
};

const customServiceName = ref('');
const addCustomService = () => {
    if (!customServiceName.value.trim()) return;
    props.ticket.wellness.customServices.push({
        name: customServiceName.value,
        checked: true,
        mode: 'addon',
        price: 0,
        duration: props.ticket.wellness.booking.duration || 60
    });
    customServiceName.value = '';
};

const removeCustomService = (index: number) => {
    props.ticket.wellness.customServices.splice(index, 1);
};

const addWellnessAddon = () => {
    props.ticket.wellness.manualAddons.push({ name: '', price: 0, qty: 1 });
};

const removeWellnessAddon = (index: number) => {
    props.ticket.wellness.manualAddons.splice(index, 1);
};

// Slot Generation Logic
const generateWellnessSlots = async () => {
    const startTime = String(props.ticket.wellness.booking.slotStart || '09:00');
    const endTime = String(props.ticket.wellness.booking.slotEnd || '18:00');
    const duration = safeInt(props.ticket.wellness.booking.duration || 60);
    const buffer = safeInt(props.ticket.wellness.booking.buffer || 0);
    const step = duration + buffer;

    const startMin = timeToMinutes(startTime);
    const endMin = timeToMinutes(endTime);

    const slots: any[] = [];
    for (let m = startMin; m + duration <= endMin; m += step) {
        slots.push({
            key: `${m}`,
            label: minutesToTime(m),
            start: minutesToTime(m),
            end: minutesToTime(m + duration),
            disabled: false
        });
    }
    props.ticket.wellness.booking.slots = slots;
};

const timeToMinutes = (t: string) => {
    const [hh, mm] = t.split(':').map(Number);
    return hh * 60 + mm;
};

const minutesToTime = (m: number) => {
    const hh = Math.floor(m / 60);
    const mm = m % 60;
    return `${String(hh).padStart(2, '0')}:${String(mm).padStart(2, '0')}`;
};

const clearDateBlocks = () => {
    props.ticket.wellness.booking.slots = [];
};

onMounted(initWellness);
watch(() => props.ticket, initWellness, { immediate: true });
</script>

<template>
    <div class="rounded-2xl border border-blue-100 bg-[#f8fbff] p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">✨</span>
                <h3 class="text-[17px] font-black text-slate-900 tracking-tight">Wellness & Spa Service Setup</h3>
            </div>
            <span class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-[11px] font-black text-slate-500 uppercase tracking-wider shadow-sm">
                Wellness only
            </span>
        </div>

        <p class="text-[11px] text-slate-400 font-medium leading-relaxed">
            Use the main Price as your base service price. Optional add-ons + mobile fee can be added below.
        </p>

        <!-- Service Preset -->
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="text-lg">🗂️</span>
                <h4 class="text-[14px] font-black text-slate-800">Service Preset (Auto-check)</h4>
            </div>
            <select v-model="ticket.wellness.preset" @change="applyWellnessPreset"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition-all appearance-none cursor-pointer shadow-sm">
                <option value="">— Select —</option>
                <option value="massage">💆 Massage Focus</option>
                <option value="facial">✨ Facial / Skincare</option>
                <option value="nails">💅 Nails</option>
                <option value="full">🌿 Full Spa Day</option>
                <option value="spa_essentials">🧖 Spa Day Essentials</option>
                <option value="bridal_prep">👰 Bridal Prep</option>
                <option value="sports_recovery">🏃 Sports Recovery</option>
                <option value="prenatal_care">🤰 Prenatal Care</option>
            </select>
            <p class="text-[11px] text-slate-400 font-medium italic">
                Adds a quick setup vibe — it won't remove anything you already selected.
            </p>
        </div>

        <!-- Booking Settings -->
        <div class="bg-white border border-slate-100 rounded-2xl p-5 space-y-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📆</span>
                    <h4 class="text-[15px] font-black text-slate-900 tracking-tight">Booking Settings</h4>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border border-slate-100">
                    Slots + Mobile
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-[11px] font-black text-slate-400 uppercase mb-1.5 block">Session Duration</label>
                    <select v-model="ticket.wellness.booking.duration" class="w-full rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-700 outline-none">
                        <option :value="30">30 minutes</option>
                        <option :value="60">60 minutes</option>
                        <option :value="90">90 minutes</option>
                        <option :value="120">120 minutes</option>
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-black text-slate-400 uppercase mb-1.5 block">Buffer Time (minutes)</label>
                    <input type="number" v-model="ticket.wellness.booking.buffer" class="w-full rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-700 outline-none" />
                </div>
                <div>
                    <label class="text-[11px] font-black text-slate-400 uppercase mb-1.5 block">Max Bookings Per Slot</label>
                    <input type="number" v-model="ticket.wellness.booking.maxPerSlot" class="w-full rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-700 outline-none" />
                </div>
                <div>
                    <label class="text-[11px] font-black text-slate-400 uppercase mb-1.5 block">Service Mode</label>
                    <select v-model="ticket.wellness.booking.mode" class="w-full rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-700 outline-none">
                        <option value="studio">In-Studio</option>
                        <option value="mobile">Mobile (Travel)</option>
                        <option value="in_home">In-Home</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-[11px] font-black text-slate-400 uppercase block">Refund / Cancellation Policy</label>
                <textarea v-model="ticket.wellness.booking.policy" rows="3" class="w-full rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-600 outline-none focus:bg-white focus:border-blue-200 transition-all resize-none"></textarea>
                <p class="text-[10px] text-slate-400 italic">You can show this on the booking page and in confirmation emails.</p>
            </div>
        </div>

        <!-- Slot Generator -->
        <div class="bg-white border border-slate-100 rounded-2xl p-5 space-y-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🕒</span>
                    <h4 class="text-[15px] font-black text-slate-900 tracking-tight">Slot Time Picker (Auto-block)</h4>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-[10px] font-black text-blue-500 uppercase tracking-widest border border-blue-100">
                    Auto-Generator
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase mb-1 block">Date</label>
                    <input type="date" v-model="ticket.wellness.booking.slotDate" class="w-full rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 outline-none" />
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase mb-1 block">Workday Starts</label>
                    <input type="time" v-model="ticket.wellness.booking.slotStart" class="w-full rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 outline-none" />
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase mb-1 block">Workday Ends</label>
                    <input type="time" v-model="ticket.wellness.booking.slotEnd" class="w-full rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 outline-none" />
                </div>
            </div>

            <button @click="generateWellnessSlots" class="w-full py-4 rounded-xl bg-[#4f46e5] text-white text-sm font-black hover:bg-[#4338ca] transition-all shadow-md active:scale-[0.98]">
                Generate Slots
            </button>

            <div v-if="ticket.wellness.booking.slots.length > 0" class="space-y-4 pt-2">
                <div class="flex items-center justify-between border-t border-slate-50 pt-4">
                    <h5 class="text-[12px] font-black text-slate-900">Available Slots</h5>
                    <div class="flex gap-2">
                        <button @click="clearDateBlocks" class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-500 text-[10px] font-black border border-slate-100 hover:bg-slate-100 transition-all uppercase tracking-wider">Clear</button>
                    </div>
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                    <div v-for="slot in ticket.wellness.booking.slots" :key="slot.key" class="px-2 py-2 rounded-lg border border-slate-100 bg-slate-50 text-center text-[11px] font-bold text-slate-600">
                        {{ slot.label }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Selection -->
        <div class="space-y-4">
            <h4 class="text-[15px] font-black text-slate-900 tracking-tight">Services (Included vs Add-on)</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div v-for="item in WELLNESS_SERVICES" :key="item.name" class="relative group">
                    <div :class="[
                        'flex flex-col p-4 rounded-xl border transition-all select-none',
                        isWellnessServiceChecked(item.name) ? 'bg-white border-blue-400 shadow-sm ring-1 ring-blue-100' : 'bg-white/60 border-slate-100 hover:border-slate-200'
                    ]">
                        <label class="flex items-center justify-between cursor-pointer mb-0">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" :checked="isWellnessServiceChecked(item.name)" @change="e => toggleWellnessService(item.name, (e.target as HTMLInputElement).checked)" class="sr-only" />
                                <div :class="['w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all', isWellnessServiceChecked(item.name) ? 'bg-blue-500 border-blue-500' : 'bg-white border-slate-200 group-hover:border-slate-300']">
                                    <svg v-if="isWellnessServiceChecked(item.name)" class="w-3 h-3 text-white fill-current" viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/></svg>
                                </div>
                                <span class="text-[13px] font-bold text-slate-700">{{ item.name }}</span>
                            </div>
                            <span class="px-2 py-1 rounded-lg bg-slate-50 border border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-tighter">{{ item.duration }}m</span>
                        </label>

                        <!-- Expanded Details for Services -->
                        <div v-if="isWellnessServiceChecked(item.name)" class="mt-4 grid grid-cols-2 gap-3 animate-in fade-in slide-in-from-top-2">
                             <div>
                                 <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Mode</label>
                                 <select v-model="getWellnessServiceData(item.name).mode" class="w-full rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-sm font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-300">
                                     <option value="included">Included</option>
                                     <option value="addon">Add-on</option>
                                 </select>
                             </div>
                             <div>
                                 <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Price</label>
                                 <input type="number" v-model="getWellnessServiceData(item.name).price" :disabled="getWellnessServiceData(item.name).mode === 'included'" class="w-full rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-sm font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-300 disabled:opacity-50" />
                             </div>
                             <div class="col-span-2 text-[10px] text-slate-400 mt-1">
                                 If Add-on, this becomes an upgrade price.
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Services -->
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-5 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🧩</span>
                    <h4 class="text-[15px] font-black text-slate-900 tracking-tight">Custom Wellness Services</h4>
                </div>
                <button @click="addCustomService" class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-900 text-[12px] font-black hover:bg-slate-50 transition-all shadow-sm">
                    Add Service
                </button>
            </div>

            <p class="text-[11px] text-slate-400 font-medium">
                Adds a quick setup vibe — it won't remove anything you already selected. Custom services appear above and can be checked like presets.
            </p>

            <input type="text" v-model="customServiceName" placeholder="Custom service name" class="w-full rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 outline-none focus:bg-white focus:border-blue-200 transition-all" @keyup.enter="addCustomService" />

            <div v-for="(custom, idx) in ticket.wellness.customServices" :key="idx" class="flex items-center gap-2 animate-in fade-in slide-in-from-left-2">
                 <div class="flex-1 flex items-center gap-3 p-3 bg-white border border-blue-100 rounded-xl shadow-sm">
                     <input type="checkbox" v-model="custom.checked" class="w-5 h-5 accent-blue-500 rounded-md" />
                     <input type="text" v-model="custom.name" class="flex-1 bg-transparent border-none outline-none text-[13px] font-bold text-slate-700" />
                     <button @click="removeCustomService(idx)" class="text-rose-500 hover:text-rose-600 transition-colors">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                     </button>
                 </div>
            </div>
        </div>

        <!-- Manual Add-ons -->
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="text-[15px] font-black text-slate-900 tracking-tight">+ Manual Wellness Add-ons</h4>
                <button @click="addWellnessAddon" class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-900 text-[12px] font-black hover:bg-slate-50 transition-all shadow-sm">
                    Add Add-on
                </button>
            </div>

            <p class="text-[11px] text-slate-400 font-medium">
                Examples: Aromatherapy, Deep Tissue Upgrade, Hot Stones, Travel Fee, etc.
            </p>

            <div v-for="(addon, idx) in ticket.wellness.manualAddons" :key="idx" class="p-4 bg-white rounded-xl border border-blue-50 shadow-sm space-y-3 animate-in fade-in slide-in-from-top-2">
                <div class="flex gap-3">
                    <input v-model="addon.name" type="text" placeholder="Add-on Name" class="flex-1 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-sm font-medium outline-none focus:bg-white focus:border-blue-200 transition-all" />
                    <button @click="removeWellnessAddon(idx)" class="text-rose-400 hover:text-rose-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <input v-model="addon.price" type="number" step="0.01" placeholder="Price ($)" class="w-full rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-sm font-bold text-emerald-600 outline-none focus:bg-white focus:border-blue-200 transition-all" />
                    <input v-model="addon.qty" type="number" min="1" placeholder="Qty" class="w-full rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-200 transition-all" />
                </div>
            </div>
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
.slide-in-from-top-2 {
    animation-name: slideInFromTop;
}
.slide-in-from-left-2 {
    animation-name: slideInFromLeft;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInFromTop {
    from { transform: translateY(-0.5rem); }
    to { transform: translateY(0); }
}

@keyframes slideInFromLeft {
    from { transform: translateX(-0.5rem); }
    to { transform: translateX(0); }
}
</style>
