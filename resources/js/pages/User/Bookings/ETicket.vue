<script setup lang="ts">
import { User } from '@/client';
import { LinkupEvent } from '@/client/models/LinkupEvent';
import { TicketDetail as Ticket } from '@/client/models/Ticket';
import { TicketSale } from '@/client/models/TicketSale';
import { Button } from '@/components/front/ui/button';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import QrcodeVue from 'qrcode.vue';
import moment from 'moment';
import { ref, computed } from 'vue';
import axios from 'axios';

const { ticketSales, app_url, fees_summary } = defineProps<{
    ticketSales: Array<TicketSale & {
        event: LinkupEvent;
        ticket: Ticket;
        user: User;
    }>;
    app_url: string;
    fees_summary?: {
        service_fee_total: number;
        processing_fee_total: number;
        drink_fees_total: number;
        bottle_fees_total: number;
        vip_fees_total: number;
        mobile_fee_total: number;
        spa_fees_total?: number;
        cookout_fees_total?: number;
        coupon_amount: number;
        subtotal?: number;
        total?: number;
        tax_total?: number;
        drink_fee_pct?: number;
        bottle_fee_pct?: number;
        vip_fee_pct?: number;
    }
}>();

const localTicketSales = ref([...ticketSales]);
const firstTicket = computed(() => localTicketSales.value[0]);
const currencySymbol = computed(() => firstTicket.value?.event?.currency_symbol || '$');

const isWellnessConfig = (config: any): config is { includeService?: string } => {
    return config && typeof config === 'object' && 'includeService' in config;
};

const isWellnessTicket = (row: any): boolean => {
    const wellnessConfig = row?.ticket?.wellness ?? (row?.ticket?.extraSetting?.wellness ?? null);
    if (isWellnessConfig(wellnessConfig)) {
        return (wellnessConfig?.includeService ?? '') === 'yes';
    }
    if (typeof row?.ticket_name === 'string') {
        const lower = row.ticket_name.toLowerCase();
        return lower.includes('wellness');
    }
    return false;
};

const isCookoutConfig = (config: any): config is { includeFood?: string } => {
    return config && typeof config === 'object' && 'includeFood' in config;
};

const isCookoutTicket = (row: any): boolean => {
    // Check if ticket has cookout data (cookout_total, cookout_addons, or cookout_included_protein)
    if (Number(row?.cookout_total || 0) > 0) return true;
    if (Array.isArray(row?.cookout_addons) && row.cookout_addons.length > 0) return true;
    if (row?.cookout_included_protein) return true;
    // Fallback to check ticket_name
    if (typeof row?.ticket_name === 'string') {
        const lower = row.ticket_name.toLowerCase();
        return lower.includes('cookout');
    }
    return false;
};

const buildScanUrl = (ticketSale: any): string => {
    const rawCode = (ticketSale as any)?.ticket_qrcode_id || (ticketSale as any)?.ticket_qrcode || '';
    if (!rawCode) return '';
    return `${app_url}/organizer/scanner/scan?code=${encodeURIComponent(String(rawCode))}`;
};

// ... rest of your code

const totalServiceFee = computed(() => {
    const backend = fees_summary?.service_fee_total;
    if (backend !== undefined) return Number(backend);
    return localTicketSales.value.reduce((sum, t: any) => sum + parseFloat(t.fee ?? 0), 0);
});
const totalProcessingFee = computed(() => {
    const backend = fees_summary?.processing_fee_total;
    if (backend !== undefined) return Number(backend);
    return localTicketSales.value.reduce((sum, t: any) => sum + parseFloat(t.tax ?? 0), 0);
});
const mobileFeeTotal = computed(() => {
    const backend = fees_summary?.mobile_fee_total;
    if (backend !== undefined && Number(backend) > 0) return Number(backend);
    return localTicketSales.value.reduce((sum: number, row: any) => sum + Number(computeRowMobileFee(row) || 0), 0);
});

const totalVipFees = computed(() => {
    const backend = fees_summary?.vip_fees_total;
    if (backend !== undefined) return Number(backend);
    const pct = Number(fees_summary?.vip_fee_pct ?? 0) / 100;
    if (pct <= 0) return 0;
    return localTicketSales.value.reduce((sum, row: any) => sum + computeRowVipFee(row), 0);
});
const totalDiscount = computed(() => {
    const backend = fees_summary?.coupon_amount;
    if (backend !== undefined) return Number(backend);
    return localTicketSales.value.reduce((sum, t: any) => sum + parseFloat(t.coupan_amount ?? 0), 0);
});

const ticketsSubtotal = computed(() => localTicketSales.value.reduce((sum: number, t: any) => sum + Number(t?.sub_total || 0), 0));
const tablesTotal = computed(() => localTicketSales.value.reduce((sum: number, t: any) => sum + Number(t?.tables_total || 0), 0));
const cookoutTotal = computed(() => localTicketSales.value.reduce((sum: number, t: any) => sum + Number(t?.cookout_total || 0), 0));
const wellnessTotal = computed(() => localTicketSales.value.reduce((sum: number, t: any) => sum + Number(t?.wellness_total || 0), 0));

const servicesSubtotal = computed(() => ticketsSubtotal.value + tablesTotal.value + cookoutTotal.value + wellnessTotal.value);

const prettyCookoutCategory = (cat?: string): string => {
    switch (cat) {
        case 'cookout_protein': return 'Cookout Protein';
        case 'cookout_extra': return 'Cookout Extra';
        case 'cookout_drink': return 'Cookout Drink';
        case 'cookout_included_plate': return 'Included Plate';
        default: return cat ? cat : '';
    }
};

const prettyWellnessCategory = (cat?: string): string => {
    switch (cat) {
        case 'wellness_service': return 'Wellness Service';
        case 'wellness_manual': return 'Wellness Add-on';
        case 'wellness_included_service': return 'Included Service';
        case 'wellness_slot': return 'Wellness Slot';
        default: return cat ? cat : '';
    }
};

const formatWellnessAddonName = (name: string) => {
    if (!name) return '';
    // If it contains an ISO date like (2026-03-11T00:00:00.000000Z), format it
    const isoDateMatch = name.match(/\((\d{4}-\d{2}-\d{2}T[^\)]+)\)/);
    if (isoDateMatch) {
        const isoDate = isoDateMatch[1];
        const formattedDate = moment(isoDate).format('MMM DD, YYYY');
        return name.replace(isoDateMatch[0], `(${formattedDate})`);
    }
    return name;
};

const baseDrinkSplit = computed(() => {
    let drinksBase = 0;
    let bottlesBase = 0;
    localTicketSales.value.forEach((row: any) => {
        const list = Array.isArray(row?.drink_addons) ? row.drink_addons : [];
        list.forEach((d: any) => {
            const amt = Number(d?.total_price || 0);
            if ((d?.category || '') === 'bottles') bottlesBase += amt; else drinksBase += amt;
        });
    });
    return { drinksBase, bottlesBase };
});
const drinkFeesTotal = computed(() => {
    const backend = fees_summary?.drink_fees_total;
    if (backend !== undefined) return Number(backend);
    return localTicketSales.value.reduce((sum: number, row: any) => sum + Number(computeRowDrinkFees(row)), 0);
});
const bottleFeesTotal = computed(() => {
    const backend = fees_summary?.bottle_fees_total;
    if (backend !== undefined) return Number(backend);
    return localTicketSales.value.reduce((sum: number, row: any) => sum + Number(computeRowBottleFees(row)), 0);
});
const eventTaxTotal = computed(() => {
    const backend = fees_summary?.tax_total;
    if (backend !== undefined) return Number(backend);
    const found: any = Array.isArray(localTicketSales.value) ? localTicketSales.value.find((t: any) => Number(t?.event_tax || 0) > 0) : null;
    return Number(found?.event_tax || 0);
});

const totalTicketsCount = computed(() => {
    return activeTickets.value.reduce((sum, ticket) => sum + parseInt(String(ticket.no_of_tickets ?? '0'), 10), 0);
});

const grandTotal = computed(() => {
    const calculated = (
        (ticketsSubtotal.value + tablesTotal.value)
        - totalDiscount.value
        + totalServiceFee.value
        + totalProcessingFee.value
        + totalVipFees.value
        + drinkFeesTotal.value
        + bottleFeesTotal.value
        + mobileFeeTotal.value
        + eventTaxTotal.value
        + baseDrinkSplit.value.drinksBase
        + baseDrinkSplit.value.bottlesBase
        + cookoutTotal.value
        + wellnessTotal.value
    );

    const backendTotal = (fees_summary?.total !== undefined) ? Number(fees_summary.total) : 0;
    return (fees_summary?.total !== undefined && Math.abs(backendTotal - calculated) <= 0.01)
        ? backendTotal
        : calculated;
});

const overallSubtotal = computed(() => {
    if (fees_summary?.subtotal !== undefined) return Number(fees_summary.subtotal);
    return (ticketsSubtotal.value + tablesTotal.value + cookoutTotal.value + wellnessTotal.value + baseDrinkSplit.value.drinksBase + baseDrinkSplit.value.bottlesBase);
});

// ... rest of your code

const serviceFeeFor = (t: any) => {
    const v = parseFloat(t?.fee ?? 0);
    return v > 0 ? v : totalServiceFee.value;
};

const getTicketUnit = (row: any): number => {
    const qty = Number(row?.no_of_tickets || 0);
    const sub = Number(row?.sub_total || 0);
    return qty > 0 ? (sub / qty) : 0;
};
const shouldShowTable = (table: any, row: any): boolean => {
    const totalPrice = Number(table?.total_price || 0);
    if (totalPrice <= 0) return false;
    const tableUnit = Number(table?.unit_price || 0);
    const ticketUnit = getTicketUnit(row);
    return Math.abs(tableUnit - ticketUnit) > 1e-6;
};
const filterTables = (row: any): any[] => {
    const list = Array.isArray(row?.table_addons) ? row.table_addons : [];
    return list.filter((tb: any) => shouldShowTable(tb, row));
};
const sectionDrinks = (row: any, section: any): any[] => {
    const list = Array.isArray(row?.drink_addons) ? row.drink_addons : [];
    const key = (section || '-');
    return list.filter((d: any) => (d.section || '-') === key);
};
const hasAnyDrinks = (row: any): boolean => Array.isArray(row?.drink_addons) && row.drink_addons.length > 0;
const unassignedDrinks = (row: any): any[] => {
    const list = Array.isArray(row?.drink_addons) ? row.drink_addons : [];
    return list.filter((d: any) => !d?.section || d.section === '-');
};

const isRowFree = (row: any) => {
    const baseIsZero = Number(displayBasePrice(row)) === 0;
    const noPackage = !row?.package_data;
    const noDrinks = !Array.isArray(row?.drink_addons) || row.drink_addons.length === 0;
    const noTables = !Array.isArray(row?.table_addons) || row.table_addons.length === 0;
    return baseIsZero && noPackage && noDrinks && noTables;
};

const sumTableAddons = (row: any) => {
    if (!Array.isArray(row?.table_addons)) return 0;
    return row.table_addons.reduce((s: number, t: any) => s + Number(t?.total_price || 0), 0);
};
const sumDrinkAddons = (row: any) => {
    if (!Array.isArray(row?.drink_addons)) return 0;
    return row.drink_addons.reduce((s: number, d: any) => s + Number(d?.total_price || 0), 0);
};

const rowDrinkSplit = (row: any) => {
    let drinksBase = 0;
    let bottlesBase = 0;
    const list = Array.isArray(row?.drink_addons) ? row.drink_addons : [];
    for (const d of list) {
        const amt = Number(d?.total_price || 0);
        if ((d?.category || '') === 'bottles') {
            bottlesBase += amt;
        } else {
            drinksBase += amt;
        }
    }
    return { drinksBase, bottlesBase };
};

const rowServicesSubtotal = (row: any) => {
    return Number(displayBasePrice(row) || 0)
        + sumTableAddons(row)
        + Number(row?.cookout_total || 0)
        + Number(row?.wellness_total || 0);
};

const rowOrderSubtotal = (row: any) => {
    const split = rowDrinkSplit(row);
    return rowServicesSubtotal(row) + split.drinksBase + split.bottlesBase;
};

const computeRowDrinkFees = (row: any) => {
    const pct = Number((fees_summary as any)?.drink_fee_pct ?? 0) / 100;
    if (pct <= 0) return 0;
    if (!Array.isArray(row?.drink_addons)) return 0;
    let total = 0;
    for (const d of row.drink_addons) {
        const cat = d?.category;
        if (["mixDrinks", "wines", "beers", "waters", "softDrinks"].includes(cat)) {
            total += Number(d?.total_price || 0) * pct;
        }
    }
    return total;
};
const computeRowBottleFees = (row: any) => {
    const pct = Number((fees_summary as any)?.bottle_fee_pct ?? 0) / 100;
    if (pct <= 0) return 0;
    if (!Array.isArray(row?.drink_addons)) return 0;
    let total = 0;
    for (const d of row.drink_addons) {
        if ((d?.category) === 'bottles') {
            total += Number(d?.total_price || 0) * pct;
        }
    }
    return total;
};
const computeRowVipFee = (row: any) => {
    const pct = Number((fees_summary as any)?.vip_fee_pct ?? 0) / 100;
    if (pct <= 0) return 0;

    const pkg = row?.ticket;
    if (pkg.has_table !== 'yes') return 0;

    const tablesSum = sumTableAddons(row);
    const tablesTotal = Number(row?.tables_total || 0);
    const tableBase = tablesSum > 0 ? tablesSum : (tablesTotal > 0 ? tablesTotal : 0);
    const base = tableBase > 0 ? tableBase : Number(row?.sub_total || 0);
    return base * pct;
};

const displayTotalPaid = (row: any) => {
    const toNum = (v: any) => Number(v || 0);

    if (isRowFree(row)) {
        return 0;
    }

    const sub = toNum(row?.sub_total);
    const base = toNum(displayBasePrice(row));
    const drinks = sumDrinkAddons(row) || toNum(row?.drinks_total);
    const tableSum = sub > 0 ? sumTableAddons(row) : 0;
    const cookout = toNum(row?.cookout_total);
    const wellness = toNum(row?.wellness_total);
    const discount = toNum(row?.coupan_amount);
    const rowDrinkFees = computeRowDrinkFees(row);
    const rowBottleFees = computeRowBottleFees(row);
    const rowVipFee = computeRowVipFee(row);
    const rowMobileFee = computeRowMobileFee(row);
    const sf = serviceFeeFor(row);
    const pf = processingFeeFor(row);
    const tax = toNum(row.event_tax ?? 0);
    return Math.max(0, tax + base + tableSum + drinks + cookout + wellness + rowDrinkFees + rowBottleFees + rowVipFee + rowMobileFee + sf + pf - discount);
};
const computeRowMobileFee = (row: any) => {
    const breakdown = typeof row.fee_breakdown === 'string' ? JSON.parse(row.fee_breakdown) : (row.fee_breakdown || {});
    const fromBreakdown = Number(breakdown.mobile_fee_amount || 0);
    if (fromBreakdown > 0) return fromBreakdown;

    const fromColumn = Number(row?.mobile_fee || 0);
    if (fromColumn > 0) return fromColumn;

    const wellnessAddons = Array.isArray(row?.wellness_addons) ? row.wellness_addons : [];
    const hasMobileMode = wellnessAddons.some((a: any) => {
        const cat = String(a?.category || '').toLowerCase();
        const name = String(a?.name || '').toLowerCase();
        return cat === 'wellness_mode' && name.includes('mobile');
    });
    if (!hasMobileMode) return 0;

    const mobileFee = Number(row?.ticket?.wellness?.booking?.mobileFee
        ?? row?.ticket?.extraSetting?.wellness?.booking?.mobileFee
        ?? 0);
    if (mobileFee <= 0) return 0;

    const qty = Number(row?.no_of_tickets || 1);
    return mobileFee * qty;
};
const processingFeeFor = (t: any) => {
    const v = parseFloat(t?.tax ?? 0);
    return v > 0 ? v : totalProcessingFee.value;
};

const displayBasePrice = (row: any) => Number(row?.sub_total || 0);

const eventStartDateTime = computed(() => {
    const details = firstTicket.value.event?.event_details;
    if (!details) return 'N/A';

    if (details.event_type === 'single') {
        const date = details.single_event_date;
        const time = details.single_start_time;

        if (date) {
            const dateMoment = moment(date);

            if (time) {
                const timeWithSeconds = time.length === 5 ? `${time}:00` : time;
                const dateTimeStr = `${dateMoment.format('YYYY-MM-DD')} ${timeWithSeconds}`;
                return moment(dateTimeStr).format('dddd, Do MMM YYYY, h:mm A');
            }
            return dateMoment.format('dddd, Do MMM YYYY');
        }
    } else if (details.event_type === 'recurring') {
        const date = details.recurr_start_date;
        if (date) {
            return moment(date).format('dddd, Do MMM YYYY');
        }
    }
    return 'N/A';
});

const eventEndDateTime = computed(() => {
    const details = firstTicket.value.event?.event_details;
    if (!details) return 'N/A';

    if (details.event_type === 'single') {
        const date = details.single_event_date;
        const time = details.single_end_time;

        if (date) {
            const dateMoment = moment(date);

            if (time) {
                const timeWithSeconds = time.length === 5 ? `${time}:00` : time;
                const dateTimeStr = `${dateMoment.format('YYYY-MM-DD')} ${timeWithSeconds}`;
                return moment(dateTimeStr).format('dddd, Do MMM YYYY, h:mm A');
            }
            return dateMoment.format('dddd, Do MMM YYYY');
        }
    } else if (details.event_type === 'recurring') {
        const date = details.recurr_end_date;
        if (date) {
            return moment(date).format('dddd, Do MMM YYYY');
        }
    }
    return 'N/A';
});

const printArea = ref<HTMLElement | null>(null);
function printWindow() {
    const printContents = printArea.value?.innerHTML;
    const newWindow = window.open('', '', 'height=500, width=800');
    newWindow?.document.write(`
    <html>
      <head>
        <title>Print E-Tickets</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <style>
          @media print {
            .page-break {
              page-break-after: always;
              page-break-inside: avoid;
            }
          }
        </style>
      </head>
      <body class="p-8">
        ${printContents}
      </body>
    </html>
  `);
    newWindow?.document.close();
    newWindow?.focus();
    newWindow?.print();
}
const ticketStatus = ref('');
const isLoading = ref(false);

const activeTickets = computed(() => {
    return localTicketSales.value.filter(ticket => ticket.ticket_status !== 'cancelled');
});

const cancelTicket = async (ticketId: number) => {
    if (!confirm('Are you sure you want to cancel this ticket? This action cannot be undone.')) {
        return;
    }

    isLoading.value = true;

    await axios.post(`/bookings/${ticketId}/cancel`);

    const ticketIndex = localTicketSales.value.findIndex(ticket => ticket.id === ticketId);
    if (ticketIndex !== -1) {
        localTicketSales.value[ticketIndex].ticket_status = 'cancelled';
    }
    alert('Ticket cancelled successfully');

};

const isCheckedIn = (ticketSale: any): string => {
    if (ticketSale.ticket_status == 'confirmed') {
        if (Array.isArray(ticketSale.checkins) && ticketSale.checkins.length > 0) {
            ticketStatus.value = "Checked In";
        } else {
            ticketStatus.value = "";
        }
    } else if (ticketSale.ticket_status == 'cancelled') {
        ticketStatus.value = "cancelled";
    }

    return ticketStatus.value;
};
const isTicketScanned = (ticketSale: any): boolean => {
    return Array.isArray(ticketSale.checkins) && ticketSale.checkins.length > 0;
};

</script>
<template>
    <!-- E-Ticket Card -->
    <AuthenticatedLayout>
        <div class="print-area">
            <!-- SCREEN VIEW: Summary of all tickets -->
            <div
                class="screen-only w-full max-w-2xl mx-auto rounded-2xl bg-white p-8 shadow-xl border border-gray-200 mb-6">
                <!-- Header -->
                <div class="grid grid-cols-2 md:grid-cols-2 gap-6 py-6">
                    <!-- Left: Logo -->
                    <div class="flex justify-center md:justify-start">
                        <img :src="'/storage/events/logo_for_e_tickets.png'" alt="E-Tickets Logo"
                            class="w-40 h-auto object-contain rounded-lg" />
                    </div>
                    <div class="text-center mb-8 ml-[-100%] mt-[17%]">
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-wide">
                            🎟 E-Tickets
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ activeTickets.length }} ticket{{ activeTickets.length > 1 ? 's' : '' }} in this purchase
                        </p>
                    </div>

                </div>
                <!-- Event Title -->
                <div class="mb-6 text-center">
                    <h2 class="text-2xl font-bold text-gray-800">{{ firstTicket.event.title }}</h2>
                </div>

                <!-- Event Details -->
                <div class="grid grid-cols-2 gap-6 mb-6 text-sm">
                    <div>
                        <p class="text-gray-500 font-medium">Start Date & Time</p>
                        <p class="text-gray-800 font-semibold">
                            {{ eventStartDateTime }}
                        </p>
                        <p class="text-gray-500 font-medium mt-3">End Date & Time</p>
                        <p class="text-gray-800 font-semibold">
                            {{ eventEndDateTime }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500 font-medium">Event Name</p>
                        <p class="text-gray-800 font-semibold">
                            {{ firstTicket.event.title }}
                        </p>
                        <p class="text-gray-500 font-medium mt-3">Location</p>
                        <p class="text-gray-800 font-semibold">
                            {{ firstTicket.event.venue }}, {{ firstTicket.event.city }},
                            {{ firstTicket.event.state }}, {{ firstTicket.event.country }}
                        </p>
                    </div>
                </div>

                <hr class="my-6 border-dashed border-gray-300" />

                <!-- User Details -->
                <div class="mb-6 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Full Name</span>
                        <span class="text-gray-800 font-semibold">{{ firstTicket.user.name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Phone</span>
                        <span class="text-gray-800 font-semibold">{{ firstTicket.user.phone_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Email</span>
                        <span class="text-gray-800 font-semibold">{{ firstTicket.user.email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Total Tickets in Purchase</span>
                        <span class="text-gray-800 font-semibold">{{ totalTicketsCount }}</span>
                    </div>
                </div>

                <hr class="my-6 border-dashed border-gray-300" />

                <!-- List all tickets in this purchase -->
                <div class="mb-6">
                    <p class="text-sm font-semibold text-gray-700 mb-3">🎫 Tickets in This Purchase</p>
                    <div class="space-y-3">
                        <div v-for="(ticket, index) in activeTickets" :key="ticket.id"
                            class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="text-sm font-semibold text-gray-800">{{ ticket.ticket_name }}</p>
                                        <!-- Cancel/Scanned buttons after ticket name -->
                                        <button
                                            v-if="ticket.ticket_status !== 'cancelled' && !isTicketScanned(ticket) && !isLoading"
                                            @click="cancelTicket(ticket.id)"
                                            class="px-2 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition-colors"
                                            :disabled="isLoading">
                                            Cancel
                                        </button>
                                        <span v-if="ticket.ticket_status === 'cancelled'"
                                            class="px-2 py-1 text-xs font-medium text-gray-600 bg-gray-200 rounded">
                                            Cancelled
                                        </span>
                                        <span v-if="isTicketScanned(ticket)"
                                            class="px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded">
                                            Scanned
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500">{{ ticket.no_of_tickets }} × {{ ticket.ticket_type
                                        }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <!-- Show base ticket price for this record (no fees) -->
                                    <p class="text-sm font-bold text-green-600">
                                        <template
                                            v-if="Number(displayBasePrice(ticket)) === 0 && (!ticket.package_data) && (!ticket.drink_addons || ticket.drink_addons.length === 0) && (!ticket.table_addons || ticket.table_addons.length === 0)">Free</template>
                                        <template v-else>{{ currencySymbol }}{{ Number(displayBasePrice(ticket)).toFixed(2) }}</template>
                                    </p>
                                </div>
                            </div>

                            <!-- Tables with section-scoped drinks (screen view) -->
                            <div v-if="Array.isArray(ticket?.table_addons) && ticket.table_addons.length > 0"
                                class="mt-2 space-y-1">
                                <p class="text-xs font-medium text-gray-600">🪑 Tables & Drinks:</p>
                                <div class="text-xs text-gray-500 ml-2 space-y-1">
                                    <div v-for="(table, tIdx) in ticket.table_addons" :key="'table-' + tIdx">
                                        <div class="font-medium flex justify-between">
                                            <span>
                                                <template
                                                    v-if="parseInt(String(table.capacity || 0)) > 0 && table.section && table.section !== '-'">
                                                    Table for {{ parseInt(String(table.capacity || 0)) }} — {{
                                                        table.section }} ({{ table.quantity }})
                                                </template>
                                                <template v-else-if="table.section && table.section !== '-'">
                                                    Table — {{ table.section }} ({{ String(table.quantity) }})
                                                </template>
                                                <template v-else>
                                                    Table ({{ String(table.quantity) }})
                                                </template> <br>

                                                <template v-if="(table as any)?.table_seating?.length > 0">
                                                    <span v-for="seat in (table as any).table_seating"
                                                        :key="(seat as any).id">
                                                        Seat: {{ (seat as any).name }},
                                                    </span>
                                                </template>
                                            </span>
                                            <span class="text-gray-700">{{ currencySymbol }}{{ Number(table.unit_price || 0).toFixed(2)
                                            }}</span>
                                        </div>
                                        <div class="ml-3" v-if="sectionDrinks(ticket, table.section).length > 0">
                                            <span v-for="(drink, dIdx) in sectionDrinks(ticket, table.section)"
                                                :key="'drink-sec-' + tIdx + '-' + dIdx">
                                                {{ drink.name }} ({{ drink.quantity }})<span
                                                    v-if="dIdx < sectionDrinks(ticket, table.section).length - 1">,
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Drinks not tied to any section (screen view) -->
                            <div v-if="unassignedDrinks(ticket).length > 0" class="mt-2">
                                <p class="text-xs font-medium text-gray-600">🍹 Drinks (Unassigned):</p>
                                <div class="text-xs text-gray-500 ml-2">
                                    <span v-for="(drink, dIdx) in unassignedDrinks(ticket)" :key="'drink-free-' + dIdx">
                                        {{ drink.name }} ({{ drink.quantity }}) 
                                        <span v-if="drink.total_price <= 0">Complementary</span>
                                        <span v-else>{{ currencySymbol }}{{ Number(drink.total_price).toFixed(2) }}</span>
                                        <span v-if="dIdx < unassignedDrinks(ticket).length - 1">, </span>
                                    </span>
                                </div>
                            </div>

                            <!-- Cookout Included Plate -->
                            <div v-if="ticket?.cookout_included_protein" class="ml-4 mt-1 space-y-1">
                                <div class="text-xs text-gray-600 flex justify-between">
                                    <span>{{ ticket.cookout_included_protein }} (Included Plate) × 1 — </span>
                                    <span>{{ currencySymbol }}0.00</span>
                                </div>
                            </div>

                            <!-- Cookout Add-ons -->
                            <div v-if="ticket?.cookout_addons && Array.isArray(ticket.cookout_addons) && ticket.cookout_addons.length > 0" class="ml-4 mt-1 space-y-1">
                                <div v-for="(addon, index) in ticket.cookout_addons" :key="'cookout-addon-' + index" class="text-xs text-gray-600 flex justify-between">
                                    <span>{{ addon?.name }} ({{ prettyCookoutCategory(addon?.category) }}) x{{ addon?.quantity }} — </span>
                                    <span>{{ currencySymbol }}{{ Number(addon?.total_price || 0).toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- Cookout Included Sides -->
                            <div v-if="ticket?.cookout_included_sides && Array.isArray(ticket.cookout_included_sides) && ticket.cookout_included_sides.length > 0" class="ml-4 mt-2">
                                <div class="text-xs font-semibold text-gray-700">Included Sides (Grab & Go)</div>
                                <div class="text-[11px] text-gray-500">
                                    {{ ticket.cookout_included_sides.join(', ') }}
                                </div>
                            </div>

                            <!-- Wellness Add-ons -->
                            <div v-if="ticket?.wellness_addons && Array.isArray(ticket.wellness_addons) && ticket.wellness_addons.length > 0" class="ml-4 mt-2 space-y-1">
                                <div v-for="(addon, index) in ticket.wellness_addons" :key="'wellness-addon-' + index" class="text-xs text-gray-600 flex justify-between">
                                    <span>{{ formatWellnessAddonName(addon?.name) }} ({{ prettyWellnessCategory(addon?.category) }}) x{{ addon?.quantity }} — </span>
                                    <span>{{ currencySymbol }}{{ Number(addon?.total_price || 0).toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- Drinks Package -->
                            <div v-if="ticket.package_data" class="mt-3 space-y-2">
                                <h2 class="text-sm font-semibold text-gray-700">
                                    {{ ticket.package_data.name }}
                                </h2>

                                <!-- Bottles -->
                                <div v-if="ticket.package_data.bottles && ticket.package_data.bottles.length > 0">
                                    <p class="text-xs font-medium text-gray-600">Bottles:</p>
                                    <ul class="ml-3 text-xs text-gray-500 list-disc">
                                        <li v-for="(bottle, idx) in ticket.package_data.bottles" :key="'bottle-' + idx">
                                            {{ bottle.name }} ({{ bottle.qty }})
                                        </li>
                                    </ul>
                                </div>

                                <!-- Chasers -->
                                <div v-if="ticket.package_data.chasers && ticket.package_data.chasers.length > 0">
                                    <p class="text-xs font-medium text-gray-600">Chasers:</p>
                                    <ul class="ml-3 text-xs text-gray-500 list-disc">
                                        <li v-for="(chaser, idx) in ticket.package_data.chasers" :key="'chaser-' + idx">
                                            {{ chaser.name }} ({{ chaser.qty }})
                                        </li>
                                    </ul>
                                </div>

                                <!-- Waters -->
                                <div v-if="ticket.package_data.waters && ticket.package_data.waters.length > 0">
                                    <p class="text-xs font-medium text-gray-600">Waters:</p>
                                    <ul class="ml-3 text-xs text-gray-500 list-disc">
                                        <li v-for="(water, idx) in ticket.package_data.waters" :key="'water-' + idx">
                                            {{ water.name }} ({{ water.qty }})
                                        </li>
                                    </ul>
                                </div>

                                <!-- Table Seating -->
                                <div
                                    v-if="ticket?.package_data?.table_seating && ticket?.package_data?.table_seating.length > 0">
                                    <p class="text-xs font-medium text-gray-600">Table Seating:</p>
                                    <ul class="ml-3 text-xs text-gray-500 list-disc">
                                        <li v-for="(seat, idx) in ticket.package_data.table_seating" :key="idx">
                                            Seat: {{ seat.name }},
                                        </li>
                                    </ul>
                                </div>
                                <!-- Notes -->
                                <div v-if="ticket.package_data.notes" class="text-xs text-gray-500">
                                    <p class="text-xs font-medium text-gray-600">Notes:</p>
                                    <p class="ml-3">{{ ticket.package_data.notes }}</p>
                                </div>
                            </div>

                            <!-- Compact Fee Breakdown (screen list) -->
                            <div class="mt-2 text-xs space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Subtotal (Ticket)</span>
                                    <span class="text-gray-800 font-semibold">
                                        <template
                                            v-if="Number(displayBasePrice(ticket)) === 0 && (!ticket.package_data) && (!ticket.drink_addons || ticket.drink_addons.length === 0) && (!ticket.table_addons || ticket.table_addons.length === 0)">Free</template>
                                        <template v-else>{{ currencySymbol }}{{ Number(displayBasePrice(ticket)).toFixed(2) }}</template>
                                    </span>
                                </div>
                                <div v-if="Number(ticket.drinks_total) > 0" class="flex justify-between">
                                    <span class="text-gray-500">Drinks Total</span>
                                    <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ Number(ticket.drinks_total).toFixed(2)
                                    }}</span>
                                </div>
                                <div v-if="Number(ticket.tables_total) > 0" class="flex justify-between">
                                    <span class="text-gray-500">Tables Total</span>
                                    <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ Number(ticket.tables_total).toFixed(2)
                                    }}</span>
                                </div>
                                <div v-if="Number(ticket.cookout_total) > 0" class="flex justify-between">
                                    <span class="text-gray-500">Cookout Total</span>
                                    <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ Number(ticket.cookout_total).toFixed(2)
                                    }}</span>
                                </div>
                                <div v-if="Number(ticket.wellness_total) > 0" class="flex justify-between">
                                    <span class="text-gray-500">Wellness Total</span>
                                    <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ Number(ticket.wellness_total).toFixed(2)
                                    }}</span>
                                </div>
                                <div v-if="Number(ticket.coupan_amount) > 0"
                                    class="flex justify-between text-green-600">
                                    <span class="font-medium">Discount</span>
                                    <span class="font-semibold">{{ currencySymbol }}-{{ Number(ticket.coupan_amount).toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-6 border-dashed border-gray-300" />

                <!-- Total Summary -->
                <div class="mb-6 space-y-3 text-sm">
                    <div v-if="servicesSubtotal > 0" class="flex justify-between">
                        <span class="text-gray-500 font-medium">Services Subtotal</span>
                        <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ servicesSubtotal.toFixed(2) }}</span>
                    </div>
                    <div v-if="baseDrinkSplit.drinksBase > 0" class="flex justify-between">
                        <span class="text-gray-500 font-medium">Drinks Subtotal</span>
                        <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ baseDrinkSplit.drinksBase.toFixed(2) }}</span>
                    </div>
                    <div v-if="baseDrinkSplit.bottlesBase > 0" class="flex justify-between">
                        <span class="text-gray-500 font-medium">Bottles Subtotal</span>
                        <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ baseDrinkSplit.bottlesBase.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Subtotal</span>
                        <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ overallSubtotal.toFixed(2) }}</span>
                    </div>
                    <div v-if="totalDiscount > 0" class="flex justify-between text-green-600">
                        <span class="font-medium">Discount</span>
                        <span class="font-semibold">{{ currencySymbol }}-{{ totalDiscount.toFixed(2) }}</span>
                    </div>
                    <div v-if="(totalServiceFee + totalProcessingFee) > 0" class="flex justify-between">
                        <span class="text-gray-500 font-medium">Service &amp; Processing Fee</span>
                        <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ (totalServiceFee + totalProcessingFee).toFixed(2)
                        }}</span>
                    </div>
                    <div v-if="totalVipFees > 0" class="flex justify-between">
                        <span class="text-gray-500 font-medium">Table Processing Fees</span>
                        <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ totalVipFees.toFixed(2) }}</span>
                    </div>
                    <div v-if="drinkFeesTotal > 0" class="flex justify-between">
                        <span class="text-gray-500 font-medium">Drink Processing Fees</span>
                        <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ drinkFeesTotal.toFixed(2) }}</span>
                    </div>
                    <div v-if="bottleFeesTotal > 0" class="flex justify-between">
                        <span class="text-gray-500 font-medium">Bottle Processing Fees</span>
                        <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ bottleFeesTotal.toFixed(2) }}</span>
                    </div>
                    <div v-if="mobileFeeTotal > 0" class="flex justify-between">
                        <span class="text-gray-500 font-medium">Mobile Fee</span>
                        <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ mobileFeeTotal.toFixed(2) }}</span>
                    </div>
                    <div v-if="eventTaxTotal > 0" class="flex justify-between">
                        <span class="text-gray-500 font-medium">Tax</span>
                        <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ eventTaxTotal.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-3">
                        <span class="text-gray-700 font-semibold text-lg">Total Paid for All Tickets</span>
                        <span class="text-2xl font-bold text-gray-900">{{ currencySymbol }}{{ grandTotal.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Status</span>
                        <span class="font-semibold text-green-600">{{ firstTicket.ticket_status }}</span>
                    </div>
                </div>

                <hr class="my-6 border-dashed border-gray-300" />

                <!-- Info Note -->
                <div class="text-center bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm text-blue-800">When you download/print, each ticket will be displayed separately
                        with its own QR code.</p>
                </div>
            </div>

            <!-- PRINT VIEW: Each ticket on separate page -->
            <div ref="printArea" class="print-only">
                <div v-for="(ticketSale, index) in localTicketSales" :key="ticketSale.id"
                    :class="{ 'page-break': index < localTicketSales.length - 1 }">
                    <div class="w-full max-w-2xl mx-auto rounded-2xl bg-white p-8 shadow-xl border border-gray-200  relative overflow-hidden"
                        :class="{ 'opacity-80 grayscale': isCheckedIn(ticketSale) }">
                        <!-- Header -->
                        <div class="grid grid-cols-2 md:grid-cols-2 gap-6 py-6">
                            <!-- Left: Logo -->
                            <div class="flex justify-start items-center">
                                <img :src="'/storage/events/logo_for_e_tickets.png'" alt="E-Tickets Logo"
                                    class="w-40 h-auto object-contain rounded-lg" />
                            </div>
                            <div class="flex flex-col justify-center items-center text-center"
                                style="margin-left: -100%; margin-top: 17%;">
                                <h1 class="text-3xl font-extrabold text-gray-900 tracking-wide">🎟 E-Ticket</h1>
                                <p class="text-sm text-gray-500 mt-1">Please present this ticket at the event</p>
                                <p class="text-xs text-gray-400 mt-1">Ticket {{ index + 1 }} of {{ activeTickets.length
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- CHECKED IN WATERMARK -->
                        <div v-if="isCheckedIn(ticketSale)" class="absolute inset-0 pointer-events-none"
                            style="z-index:9999;">
                            <div v-for="n in 1" :key="n" class="absolute" :style="{
                                top: `${n * 35}%`,
                                left: `${n * 10}%`,
                                opacity: 0.1,
                                transform: 'rotate(-30deg)',
                                fontSize: '6rem',
                                fontWeight: '800',
                                color: 'red'
                            }">
                                {{ ticketStatus }}
                            </div>
                        </div>

                        <!-- Event Title -->
                        <div class="mb-6 text-center">
                            <h2 class="text-2xl font-bold text-gray-800">{{ ticketSale.ticket_name }}</h2>
                        </div>

                        <!-- Event Details -->
                        <div class="grid grid-cols-2 gap-6 mb-6 text-sm">
                            <div>
                                <p class="text-gray-500 font-medium">Start Date & Time</p>
                                <p class="text-gray-800 font-semibold">
                                    {{ eventStartDateTime }}
                                </p>
                                <p class="text-gray-500 font-medium mt-3">End Date & Time</p>
                                <p class="text-gray-800 font-semibold">
                                    {{ eventEndDateTime }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-500 font-medium">Event Name</p>
                                <p class="text-gray-800 font-semibold">
                                    {{ ticketSale.event.title }}
                                </p>
                                <p class="text-gray-500 font-medium mt-3">Location</p>
                                <p class="text-gray-800 font-semibold">
                                    {{ ticketSale.event.venue }}, {{ ticketSale.event.city }},
                                    {{ ticketSale.event.state }}, {{ ticketSale.event.country }}
                                </p>
                            </div>
                        </div>

                        <hr class="my-6 border-dashed border-gray-300" />

                        <!-- User Details -->
                        <div class="mb-6 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Full Name</span>
                                <span class="text-gray-800 font-semibold">{{ ticketSale.user.name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Phone</span>
                                <span class="text-gray-800 font-semibold">{{ ticketSale.user.phone_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Email</span>
                                <span class="text-gray-800 font-semibold">{{ ticketSale.user.email }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Seats</span>
                                <span class="text-gray-800 font-semibold">
                                    {{ ticketSale.no_of_tickets }} × {{ ticketSale.ticket_type }}
                                </span>
                            </div>
                        </div>

                        <hr class="my-6 border-dashed border-gray-300" />

                        <!-- Drinks Package -->
                        <div v-if="ticketSale.package_data" class="mt-3 space-y-2">
                            <h2 class="text-sm font-semibold text-gray-700">
                                {{ ticketSale.package_data.name }}
                            </h2>

                            <!-- Bottles -->
                            <div v-if="ticketSale.package_data.bottles && ticketSale.package_data.bottles.length > 0">
                                <p class="text-xs font-medium text-gray-600">Bottles:</p>
                                <ul class="ml-3 text-xs text-gray-500 list-disc">
                                    <li v-for="(bottle, idx) in ticketSale.package_data.bottles" :key="'bottle-' + idx">
                                        {{ bottle.name }} ({{ bottle.qty }})
                                    </li>
                                </ul>
                            </div>

                            <!-- Chasers -->
                            <div v-if="ticketSale.package_data.chasers && ticketSale.package_data.chasers.length > 0">
                                <p class="text-xs font-medium text-gray-600">Chasers:</p>
                                <ul class="ml-3 text-xs text-gray-500 list-disc">
                                    <li v-for="(chaser, idx) in ticketSale.package_data.chasers" :key="'chaser-' + idx">
                                        {{ chaser.name }} ({{ chaser.qty }})
                                    </li>
                                </ul>
                            </div>

                            <!-- Waters -->
                            <div v-if="ticketSale.package_data.waters && ticketSale.package_data.waters.length > 0">
                                <p class="text-xs font-medium text-gray-600">Waters:</p>
                                <ul class="ml-3 text-xs text-gray-500 list-disc">
                                    <li v-for="(water, idx) in ticketSale.package_data.waters" :key="'water-' + idx">
                                        {{ water.name }} ({{ water.qty }})
                                    </li>
                                </ul>
                            </div>

                            <!-- Table Seating -->
                            <div
                                v-if="ticketSale?.package_data?.table_seating && ticketSale?.package_data?.table_seating.length > 0">
                                <p class="text-xs font-medium text-gray-600">Table Seating:</p>
                                <ul class="ml-3 text-xs text-gray-500 list-disc">
                                    <li v-for="(seat, idx) in ticketSale.package_data.table_seating" :key="idx">
                                        Seat: {{ seat.name }},
                                    </li>
                                </ul>
                            </div>

                            <!-- Notes -->
                            <div v-if="ticketSale.package_data.notes" class="text-xs text-gray-500">
                                <p class="text-xs font-medium text-gray-600">Notes:</p>
                                <p class="ml-3">{{ ticketSale.package_data.notes }}</p>
                            </div>
                        </div>
                        <hr class="my-6 border-dashed border-gray-300" />

                        <!-- Cookout Included Plate (print view) -->
                        <div v-if="ticketSale?.cookout_included_protein" class="mb-6">
                            <p class="text-sm font-semibold text-gray-700 mb-2">🍖 Cookout Included Plate</p>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-medium">{{ ticketSale.cookout_included_protein }} (Included Plate) × 1</span>
                                    <span class="text-gray-800 font-semibold">{{ currencySymbol }}0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Cookout Add-ons (print view) -->
                        <div v-if="ticketSale?.cookout_addons && Array.isArray(ticketSale.cookout_addons) && ticketSale.cookout_addons.length > 0" class="mb-6">
                            <p class="text-sm font-semibold text-gray-700 mb-2">🍽️ Cookout Add-ons</p>
                            <div class="space-y-2 text-sm">
                                <div v-for="(addon, index) in ticketSale.cookout_addons" :key="'cookout-addon-print-' + index" class="flex justify-between">
                                    <span class="text-gray-500 font-medium">{{ addon?.name }} ({{ prettyCookoutCategory(addon?.category) }}) × {{ addon?.quantity }}</span>
                                    <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ Number(addon?.total_price || 0).toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Cookout Included Sides (print view) -->
                        <div v-if="ticketSale?.cookout_included_sides && Array.isArray(ticketSale.cookout_included_sides) && ticketSale.cookout_included_sides.length > 0" class="mb-6">
                            <p class="text-sm font-semibold text-gray-700 mb-2">🥗 Included Sides (Grab & Go)</p>
                            <div class="text-sm text-gray-600">
                                {{ ticketSale.cookout_included_sides.join(', ') }}
                            </div>
                        </div>

                        <!-- Wellness Add-ons (print view) -->
                        <div v-if="ticketSale?.wellness_addons && Array.isArray(ticketSale.wellness_addons) && ticketSale.wellness_addons.length > 0" class="mb-6">
                            <p class="text-sm font-semibold text-gray-700 mb-2">💆 Wellness Services</p>
                            <div class="space-y-2 text-sm">
                                <div v-for="(addon, index) in ticketSale.wellness_addons" :key="'wellness-addon-print-' + index" class="flex justify-between">
                                    <span class="text-gray-500 font-medium">{{ formatWellnessAddonName(addon?.name) }} ({{ prettyWellnessCategory(addon?.category) }}) × {{ addon?.quantity }}</span>
                                    <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ Number(addon?.total_price || 0).toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <hr v-if="ticketSale.cookout_included_protein || (ticketSale?.cookout_addons && ticketSale.cookout_addons.length > 0) || (ticketSale?.cookout_included_sides && ticketSale.cookout_included_sides.length > 0) || (ticketSale?.wellness_addons && ticketSale.wellness_addons.length > 0)"
                            class="my-6 border-dashed border-gray-300" />

                        <!-- Tables with section-scoped drinks (print view) -->
                        <div v-if="(filterTables(ticketSale).length > 0) || hasAnyDrinks(ticketSale)" class="mb-6">
                            <p class="text-sm font-semibold text-gray-700 mb-2">🪑 Table Reservations</p>
                            <div class="space-y-2 text-sm">
                                <div v-for="(table, tIdx) in ticketSale.table_addons" :key="'table-' + tIdx"
                                    class="space-y-1">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 font-medium">
                                            <template
                                                v-if="parseInt(String(table.capacity || 0)) > 0 && table.section && table.section !== '-'">
                                                Table for {{ parseInt(String(table.capacity || 0)) }} — {{ table.section
                                                }} × {{ String(table.quantity) }}
                                            </template>
                                            <template v-else-if="table.section && table.section !== '-'">
                                                Table — {{ table.section }} × {{ String(table.quantity) }}
                                            </template>
                                            <template v-else>
                                                Table × {{ String(table.quantity) }}
                                            </template><br>

                                            <template v-if="(table as any)?.table_seating?.length > 0">
                                                <span v-for="seat in (table as any).table_seating"
                                                    :key="(seat as any).id">
                                                    Seat: {{ (seat as any).name }},
                                                </span>
                                            </template>
                                        </span>
                                        <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ Number(table.unit_price ||
                                            0).toFixed(2)
                                        }}</span>
                                    </div>
                                    <!-- Drinks under this table section -->
                                    <div v-if="sectionDrinks(ticketSale, table.section).length > 0" class="ml-4">
                                        <div v-for="(drink, dIdx) in sectionDrinks(ticketSale, table.section)"
                                            :key="'drink-sec-' + tIdx + '-' + dIdx" class="flex justify-between">
                                            <span class="text-gray-500 font-medium">{{ drink.name }} × {{ drink.quantity
                                            }}</span>
                                            <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ Number(drink.total_price).toFixed(2)
                                            }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Unassigned drinks (print view) -->
                        <div v-if="unassignedDrinks(ticketSale).length > 0" class="mb-6">
                            <p class="text-sm font-semibold text-gray-700 mb-2">🍹 Drink Addons</p>
                            <div class="space-y-2 text-sm">
                                <div v-for="(drink, dIdx) in unassignedDrinks(ticketSale)" :key="'drink-free-' + dIdx"
                                    class="flex justify-between">
                                    <span class="text-gray-500 font-medium">{{ drink.name }} × {{ drink.quantity
                                    }}</span>
                                    <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ Number(drink.total_price).toFixed(2)
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <hr v-if="ticketSale.table_addons && ticketSale.table_addons.length > 0"
                            class="my-6 border-dashed border-gray-300" />

                        <!-- Payment Details -->
                        <div class="mb-6 space-y-3 text-sm">
                            <div v-if="rowServicesSubtotal(ticketSale) > 0" class="flex justify-between">
                                <span class="text-gray-500 font-medium">Services Subtotal</span>
                                <span class="text-gray-800 font-semibold">
                                    {{ currencySymbol }}{{ rowServicesSubtotal(ticketSale).toFixed(2) }}
                                </span>
                            </div>
                            <div v-if="rowDrinkSplit(ticketSale).drinksBase > 0" class="flex justify-between">
                                <span class="text-gray-500 font-medium">Drinks Subtotal</span>
                                <span class="text-gray-800 font-semibold">
                                    {{ currencySymbol }}{{ rowDrinkSplit(ticketSale).drinksBase.toFixed(2) }}
                                </span>
                            </div>
                            <div v-if="rowDrinkSplit(ticketSale).bottlesBase > 0" class="flex justify-between">
                                <span class="text-gray-500 font-medium">Bottles Subtotal</span>
                                <span class="text-gray-800 font-semibold">
                                    {{ currencySymbol }}{{ rowDrinkSplit(ticketSale).bottlesBase.toFixed(2) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Subtotal</span>
                                <span class="text-gray-800 font-semibold">
                                    <template v-if="rowOrderSubtotal(ticketSale) === 0">Free</template>
                                    <template v-else>{{ currencySymbol }}{{ rowOrderSubtotal(ticketSale).toFixed(2) }}</template>
                                </span>
                            </div>
                            <!-- Discount per ticket -->
                            <div v-if="Number(ticketSale.coupan_amount) > 0"
                                class="flex justify-between text-green-600">
                                <span class="font-medium">Discount</span>
                                <span class="font-semibold">{{ currencySymbol }}-{{ Number(ticketSale.coupan_amount).toFixed(2) }}</span>
                            </div>
                            <hr class="border-gray-300" />
                            <!-- Fees (Service and Processing only) -->
                            <div v-if="!ticketSale.package_data">
                                <div v-if="(serviceFeeFor(ticketSale) + processingFeeFor(ticketSale)) > 0 && !isRowFree(ticketSale)"
                                    class="flex justify-between">
                                    <span class="text-gray-500 font-medium">Service &amp; Processing Fee</span>
                                    <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ (serviceFeeFor(ticketSale) +
                                        processingFeeFor(ticketSale)).toFixed(2) }}</span>
                                </div>
                            </div>
                            <!-- Table Package Fee (separate) -->
                            <div v-if="computeRowVipFee(ticketSale) > 0" class="flex justify-between">
                                <span class="text-gray-500 font-medium">Table Processing Fees</span>
                                <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ computeRowVipFee(ticketSale).toFixed(2)
                                }}</span>
                            </div>
                            <!-- Mobile Fee -->
                            <div v-if="computeRowMobileFee(ticketSale) > 0" class="flex justify-between">
                                <span class="text-gray-500 font-medium">Mobile Fee</span>
                                <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ computeRowMobileFee(ticketSale).toFixed(2)
                                }}</span>
                            </div>
                            <!-- Drink Fees (separate) -->
                            <div v-if="computeRowDrinkFees(ticketSale) > 0" class="flex justify-between">
                                <span class="text-gray-500 font-medium">Drink Processing Fees</span>
                                <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ computeRowDrinkFees(ticketSale).toFixed(2)
                                }}</span>
                            </div>
                            <!-- Mobile Fee (print view) -->
                            <div v-if="Number(ticketSale.mobile_fee) > 0" class="flex justify-between">
                                <span class="text-gray-500 font-medium">Mobile Fee</span>
                                <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ Number(ticketSale.mobile_fee).toFixed(2) }}</span>
                            </div>
                            <!-- Single cart-level tax displayed for each ticket -->
                            <div v-if="eventTaxTotal > 0" class="flex justify-between">
                                <span class="text-gray-500 font-medium">Tax</span>
                                <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ eventTaxTotal.toFixed(2) }}</span>
                            </div>
                            <!-- Bottle Fees -->
                            <div v-if="computeRowBottleFees(ticketSale) > 0" class="flex justify-between">
                                <span class="text-gray-500 font-medium">Bottle Processing Fees</span>
                                <span class="text-gray-800 font-semibold">{{ currencySymbol }}{{ computeRowBottleFees(ticketSale).toFixed(2) }}</span>
                            </div>
                            <hr class="border-gray-300" />
                            <div class="flex justify-between">
                                <span class="text-gray-700 font-semibold">Total Paid</span>
                                <span class="text-lg font-bold text-gray-900">
                                    <template v-if="Number(displayTotalPaid(ticketSale)) === 0">Free</template>
                                    <template v-else>{{ currencySymbol }}{{ Number(displayTotalPaid(ticketSale)).toFixed(2) }}</template>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Status</span>
                                <span class="font-semibold" :class="{
                                    'text-green-600': ticketSale.ticket_status === 'confirmed',
                                    'text-red-600': ticketSale.ticket_status === 'cancelled',
                                    'text-blue-600': ticketSale.ticket_status === 'pending'
                                }">{{ ticketSale.ticket_status }}</span>
                            </div>
                        </div>

                        <hr class="my-6 border-dashed border-gray-300" />

                        <!-- QR Code (URL-based, generated on frontend) -->
                        <div class="flex justify-center w-full">
                            <div class="text-center mt-4 relative inline-block">
                                <!-- Show QR code for active tickets only -->
                                <QrcodeVue v-if="buildScanUrl(ticketSale) && ticketSale.ticket_status !== 'cancelled'" :value="buildScanUrl(ticketSale)" :size="160"
                                    level="H" render-as="svg" class="mx-auto" />
                                <img v-if="buildScanUrl(ticketSale) && ticketSale.ticket_status !== 'cancelled'"
                                    :src="`${app_url}/storage/events/logo_for_e_tickets.png`" alt="Logo"
                                    class="absolute inset-0 m-auto"
                                    style="width: 65px; height: 65px; border-radius: 8px; padding: 4px;" />
                                
                                <!-- Show cancelled status for cancelled tickets -->
                                <div v-if="ticketSale.ticket_status === 'cancelled'" class="w-40 h-40 border-4 border-red-500 rounded-lg flex items-center justify-center bg-red-50">
                                    <div class="text-center">
                                        <div class="text-red-600 text-6xl mb-2">❌</div>
                                        <div class="text-red-600 font-bold text-sm">CANCELLED</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Download Button -->
            <div class="text-center">
                <Button @click="printWindow"
                    class="mb-4 mx-auto block w-full max-w-2xl rounded-lg bg-indigo-600 py-3 text-white font-semibold tracking-wide shadow-md hover:bg-indigo-700 transition">
                    Download E-Ticket{{ localTicketSales.length > 1 ? 's' : '' }}
                </Button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Screen only - visible on screen, hidden when printing */
.screen-only {
    display: block;
}

/* Print only - hidden on screen, visible when printing */
.print-only {
    display: none;
}

@media print {

    /* Hide screen view when printing */
    .screen-only {
        display: none !important;
    }

    /* Show print view when printing */
    .print-only {
        display: block !important;
    }

    /* Page break support */
    .page-break {
        page-break-after: always;
        page-break-inside: avoid;
    }

    /* Hide navigation and other UI elements */
    body *:not(.print-area):not(.print-area *) {
        display: none !important;
    }

    .print-area {
        display: block !important;
    }
}
</style>
