<script setup lang="ts">
import { computed, ref } from 'vue';

type WellnessBookingConfig = {
    buffer?: number;
    duration?: number;
    maxPerSlot?: number;
    mobileFee?: number;
    mode?: string;
    policy?: string;
    slotDate?: string;
    slotStart?: string;
    slotEnd?: string;
};

type WellnessServiceConfig = {
    name: string;
    mode?: 'included' | 'addon';
    price?: number;
    duration?: number;
    qty?: number;
};

type WellnessManualAddonConfig = {
    name: string;
    price?: number;
    qty?: number;
};

type WellnessSlotBlock = {
    start_time: string;
    end_time: string;
    slot_date: string;
    count: number;
};

type WellnessSelection = {
    includedService: string;
    services: Record<string, number>;
    manualAddons: Record<string, number>;
    selectedSlot: string | null;
    selectedSlotDate: string | null;
    holdExpiresAt: number | null;
    serviceMode: 'mobile' | 'inhouse';
    contactPhone?: string;
};

const props = defineProps<{
    ticketId: number;
    ticketName: string;
    wellnessConfig: {
        includeService?: 'yes' | 'no';
        preset?: string | null;
        services?: WellnessServiceConfig[];
        manualAddons?: WellnessManualAddonConfig[];
        booking?: WellnessBookingConfig;
    } | null;
    slotBlocks: WellnessSlotBlock[];
    currency: string;
    formatPrice?: (value: number) => string;
    hasTicketInCart: boolean;
}>();

const emit = defineEmits<{
    updateServiceMode: [mode: 'mobile' | 'inhouse'];
    updateContactPhone: [phone: string];
    updateIncludedService: [service: string];
    selectSlot: [slot: string, date: string];
    releaseSlot: [];
    incService: [name: string];
    decService: [name: string];
    incManualAddon: [name: string];
    decManualAddon: [name: string];
}>();

const selection = defineModel<WellnessSelection>('selection', { required: true });
const holdTimer = ref(0);

const formatAmount = (value: number) => {
    if (props.formatPrice) return props.formatPrice(Number(value || 0));
    return `${props.currency}${Number(value || 0).toFixed(2)}`;
};

const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

const startHoldTimer = () => {
    if ((window as any).wellnessTimer) clearInterval((window as any).wellnessTimer);

    const updateTimer = () => {
        if (!selection.value.holdExpiresAt) {
            holdTimer.value = 0;
            clearInterval((window as any).wellnessTimer);
            return;
        }

        const diff = Math.max(0, Math.floor((selection.value.holdExpiresAt - Date.now()) / 1000));
        holdTimer.value = diff;

        if (diff <= 0) {
            emit('releaseSlot');
            clearInterval((window as any).wellnessTimer);
        }
    };

    updateTimer();
    (window as any).wellnessTimer = setInterval(updateTimer, 1000);
};

const includedServices = computed(() => {
    const services = Array.isArray(props.wellnessConfig?.services) ? props.wellnessConfig?.services : [];
    return services.filter(service => service && service.name && (service.mode || 'included') !== 'addon');
});

const addonServices = computed(() => {
    const services = Array.isArray(props.wellnessConfig?.services) ? props.wellnessConfig?.services : [];
    return services.filter(service => service && service.name && (service.mode || 'included') === 'addon');
});

const manualAddons = computed(() => {
    const addons = Array.isArray(props.wellnessConfig?.manualAddons) ? props.wellnessConfig?.manualAddons : [];
    return addons.filter(addon => addon && addon.name);
});

const slotRange = computed(() => {
    const booking = props.wellnessConfig?.booking;
    if (!booking?.slotDate && !booking?.slotStart && !booking?.slotEnd) return '';
    return `${booking.slotDate || ''}${booking.slotStart ? ` ${booking.slotStart}` : ''}${booking.slotEnd ? ` - ${booking.slotEnd}` : ''}`.trim();
});

const selectedSlot = computed(() => selection.value.selectedSlot || null);
const holdTime = computed(() => formatTime(holdTimer.value));
const mobileFee = computed(() => Number(props.wellnessConfig?.booking?.mobileFee || 0));

const serviceQty = (name: string) => Number(selection.value.services?.[name] || 0);
const manualAddonQty = (name: string) => Number(selection.value.manualAddons?.[name] || 0);

const handleSelectSlot = (block: WellnessSlotBlock) => {
    const slotKey = `${String(block.start_time).substring(0, 5)}-${String(block.end_time).substring(0, 5)}`;
    emit('selectSlot', slotKey, block.slot_date);
    startHoldTimer();
};

const handleReleaseSlot = () => {
    emit('releaseSlot');
    if ((window as any).wellnessTimer) clearInterval((window as any).wellnessTimer);
};

const handleIncService = (service: WellnessServiceConfig) => {
    const maxQty = Number(service.qty || 0);
    if (maxQty > 0 && serviceQty(service.name) >= maxQty) return;
    emit('incService', service.name);
};

const handleIncManualAddon = (addon: WellnessManualAddonConfig) => {
    const maxQty = Number(addon.qty || 0);
    if (maxQty > 0 && manualAddonQty(addon.name) >= maxQty) return;
    emit('incManualAddon', addon.name);
};
</script>

<template>
    <div
        v-if="wellnessConfig?.includeService === 'yes' && hasTicketInCart"
        class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-4 space-y-5"
    >
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <h3 class="text-base font-black text-slate-900">Wellness Services</h3>
            <span class="pill bg-white">Ticket: {{ ticketName }}</span>
        </div>

        <section v-if="wellnessConfig?.booking" class="space-y-4">
            <h4 class="font-black text-slate-900">Booking</h4>

            <div class="flex items-center gap-2 flex-wrap text-sm">
                <span class="font-black text-slate-900">Mode:</span>
                <button
                    type="button"
                    class="mode-btn"
                    :class="{ active: selection.serviceMode === 'inhouse' }"
                    @click="emit('updateServiceMode', 'inhouse')"
                >
                    In-house
                </button>
                <button
                    type="button"
                    class="mode-btn"
                    :class="{ active: selection.serviceMode === 'mobile' }"
                    @click="emit('updateServiceMode', 'mobile')"
                >
                    Mobile
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-2 text-sm text-slate-900">
                <p v-if="Number(wellnessConfig.booking.duration || 0) > 0">
                    <span class="font-black">Duration:</span> {{ Number(wellnessConfig.booking.duration || 0) }} min
                </p>
                <p v-if="Number(wellnessConfig.booking.buffer || 0) > 0">
                    <span class="font-black">Buffer:</span> {{ Number(wellnessConfig.booking.buffer || 0) }} min
                </p>
                <p v-if="Number(wellnessConfig.booking.maxPerSlot || 0) > 0">
                    <span class="font-black">Max/Slot:</span> {{ Number(wellnessConfig.booking.maxPerSlot || 0) }}
                </p>
                <p v-if="slotRange">
                    <span class="font-black">Slots:</span> {{ slotRange }}
                </p>
                <p v-if="selection.serviceMode === 'mobile' && mobileFee > 0">
                    <span class="font-black">Mobile Fee:</span> {{ formatAmount(mobileFee) }}
                </p>
            </div>

            <div v-if="selection.serviceMode === 'mobile'" class="space-y-1">
                <label class="text-sm font-black text-slate-900 block">Mobile contact phone</label>
                <input
                    type="tel"
                    class="w-full rounded-xl border border-emerald-200 bg-white px-3 py-2 text-sm font-semibold outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                    :value="selection.contactPhone"
                    placeholder="Enter a phone number"
                    @input="(event) => emit('updateContactPhone', (event.target as HTMLInputElement).value)"
                />
            </div>

            <div v-if="wellnessConfig.booking.policy" class="text-sm text-slate-700">
                <p class="font-black text-slate-900">Policy</p>
                <p>{{ wellnessConfig.booking.policy }}</p>
            </div>

            <div v-if="slotBlocks.length > 0" class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <p class="font-black text-slate-900">Select a time slot</p>
                    <p class="text-xs font-semibold text-slate-400">{{ selectedSlot ? selectedSlot : 'No slot selected.' }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <button
                        v-for="block in slotBlocks"
                        :key="`${block.start_time}-${block.end_time}`"
                        type="button"
                        :disabled="Number(block.count || 0) <= 0"
                        class="slot-btn"
                        :class="{ active: selectedSlot === `${String(block.start_time).substring(0, 5)}-${String(block.end_time).substring(0, 5)}` }"
                        @click="handleSelectSlot(block)"
                    >
                        {{ String(block.start_time).substring(0, 5) }}-{{ String(block.end_time).substring(0, 5) }}
                    </button>
                </div>

                <div v-if="selectedSlot" class="flex items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2">
                    <div>
                        <p class="text-sm font-black text-amber-900">Hold active: {{ selectedSlot }}</p>
                        <p class="text-xs font-semibold text-amber-700">Complete payment before the hold expires.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-sm font-black text-amber-900">{{ holdTime }}</span>
                        <button type="button" class="rounded-lg border border-rose-200 bg-white px-2 py-1 text-xs font-black text-rose-600" @click="handleReleaseSlot">
                            Release
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="includedServices.length > 0" class="space-y-3">
            <div>
                <h4 class="font-black text-slate-900">Included Services</h4>
                <p class="text-xs font-semibold text-slate-400">Included with your ticket.</p>
            </div>
            <label
                v-for="service in includedServices"
                :key="`included-${ticketId}-${service.name}`"
                class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm cursor-pointer"
            >
                <input
                    type="radio"
                    class="accent-emerald-600"
                    :name="`wellness-included-${ticketId}`"
                    :value="service.name"
                    :checked="selection.includedService === service.name"
                    @change="emit('updateIncludedService', service.name)"
                />
                <span class="font-black text-slate-900">{{ service.name }}</span>
                <span class="pill">Included</span>
                <span v-if="Number(service.duration || 0) > 0" class="ml-auto text-sm font-semibold text-slate-400">
                    {{ Number(service.duration || 0) }} min
                </span>
            </label>
        </section>

        <section v-if="addonServices.length > 0" class="space-y-3">
            <h4 class="font-black text-slate-900">Add-on Services</h4>
            <div v-for="service in addonServices" :key="`addon-${ticketId}-${service.name}`" class="addon-row">
                <div>
                    <p class="font-black text-slate-900">{{ service.name }}</p>
                    <p class="text-xs font-semibold text-slate-400">
                        {{ Number(service.duration || 0) > 0 ? `${Number(service.duration || 0)} min` : 'Add-on' }}
                    </p>
                </div>
                <p class="font-black text-slate-900">{{ formatAmount(Number(service.price || 0)) }}</p>
                <div class="qty">
                    <button type="button" @click="emit('decService', service.name)">-</button>
                    <span>{{ serviceQty(service.name) }}</span>
                    <button type="button" @click="handleIncService(service)">+</button>
                </div>
            </div>
        </section>

        <section v-if="manualAddons.length > 0" class="space-y-3">
            <h4 class="font-black text-slate-900">Manual Add-ons</h4>
            <div v-for="addon in manualAddons" :key="`manual-${ticketId}-${addon.name}`" class="addon-row">
                <div>
                    <p class="font-black text-slate-900">{{ addon.name }}</p>
                    <p v-if="Number(addon.qty || 0) > 0" class="text-xs font-semibold text-slate-400">Available: {{ Number(addon.qty || 0) }}</p>
                </div>
                <p class="font-black text-slate-900">{{ formatAmount(Number(addon.price || 0)) }}</p>
                <div class="qty">
                    <button type="button" @click="emit('decManualAddon', addon.name)">-</button>
                    <span>{{ manualAddonQty(addon.name) }}</span>
                    <button type="button" @click="handleIncManualAddon(addon)">+</button>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.pill {
    border-radius: 9999px;
    border: 1px solid rgb(226 232 240);
    background-color: rgb(248 250 252);
    color: rgb(71 85 105);
    padding: 0.125rem 0.625rem;
    font-size: 0.75rem;
    font-weight: 800;
}

.mode-btn {
    border-radius: 0.75rem;
    border: 1px solid rgb(226 232 240);
    background: white;
    color: rgb(71 85 105);
    padding: 0.45rem 0.85rem;
    font-size: 0.875rem;
    font-weight: 900;
    transition: all 0.2s;
}

.mode-btn.active {
    border-color: rgb(59 130 246);
    color: rgb(37 99 235);
    box-shadow: 0 0 0 2px rgb(219 234 254);
}

.slot-btn {
    min-height: 2.4rem;
    border-radius: 0.75rem;
    border: 1px solid rgb(226 232 240);
    background: transparent;
    color: rgb(15 23 42);
    font-size: 0.875rem;
    font-weight: 900;
    transition: all 0.2s;
}

.slot-btn:hover:not(:disabled),
.slot-btn.active {
    border-color: rgb(59 130 246);
    background: white;
    color: rgb(37 99 235);
}

.slot-btn:disabled {
    cursor: not-allowed;
    opacity: 0.45;
}

.addon-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto auto;
    gap: 0.75rem;
    align-items: center;
    border-radius: 0.75rem;
    border: 1px solid rgb(226 232 240);
    background: white;
    padding: 0.75rem;
}

.qty {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    border-radius: 0.5rem;
    border: 1px solid rgb(226 232 240);
    background-color: rgb(248 250 252);
    padding: 0 0.25rem;
}

.qty button {
    height: 1.75rem;
    width: 1.75rem;
    font-weight: 900;
    color: rgb(100 116 139);
}

.qty span {
    width: 1.25rem;
    text-align: center;
    font-size: 0.875rem;
    font-weight: 900;
    color: rgb(15 23 42);
}
</style>
