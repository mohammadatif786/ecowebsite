<script setup lang="ts">
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import moment from "moment";
import { LinkupEvent } from "@/client/models/LinkupEvent";
import { Button } from "@/components/front/ui/button";
import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselPrevious,
  CarouselNext,
} from "@/components/front/ui/carousel";
import dayjs from 'dayjs';
import EventCard from "@/components/front/EventCard.vue";
import Card from "@/components/front/ui/card/Card.vue";
import CardContent from "@/components/front/ui/card/CardContent.vue";
import QrcodeVue from 'qrcode.vue';

const { ticketSale, user_data, event, otherEvents, app_url, organizer, sponsor, fees_summary, currency: eventCurrency } = usePage<{
  ticketSale: any;
  user_data: any;
  event: LinkupEvent;
  otherEvents: LinkupEvent[];
  app_url: string;
    sponsor: any;
    organizer: any;
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
    }
    currency?: string;
}>().props;

const currencySymbol = computed(() => {
  if (eventCurrency && typeof eventCurrency === 'string' && eventCurrency.length > 0) {
    return eventCurrency;
  }
  return event?.currency_symbol || '$';
});

const firstTicketSale = computed(() => {
  if (Array.isArray(ticketSale) && ticketSale.length > 0) {
    return ticketSale[0];
  }
  return null;
});

const feeBreakdown = computed(() => {
  const raw = firstTicketSale.value?.fee_breakdown;
  if (!raw) return {} as Record<string, number>;
  if (typeof raw === 'string') {
    try {
      return JSON.parse(raw) || {};
    } catch (error) {
      return {} as Record<string, number>;
    }
  }
  return raw || {};
});

// Display-only helper: ticket base amount for this row (tickets only)
// Do not mix in tables; tables are shown separately
const displayBasePrice = (row: any) => Number(row?.sub_total || 0);
// Pretty print drink categories
const prettyCategory = (cat?: string): string => {
  switch (cat) {
    case 'bottles': return 'Bottles';
    case 'mixDrinks': return 'Mix Drink';
    case 'waters': return 'Water';
    case 'wines': return 'Wine';
    case 'beers': return 'Beer';
    case 'softDrinks': return 'Soft Drink';
    default: return cat ? cat : '';
  }
};

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
        case 'wellness_mode': return 'Service Mode';
        default: return cat ? cat : '';
    }
};

const formatWellnessAddonName = (name: string) => {
    if (!name) return '';
    // If it contains an ISO date like (2026-03-11T00:00:00.000000Z), format it
    const isoDateMatch = name.match(/\((\d{4}-\d{2}-\d{2}T[^\)]+)\)/);
    if (isoDateMatch) {
        const isoDate = isoDateMatch[1];
        // moment is likely available if it's used elsewhere, but let's check
        return name.replace(isoDateMatch[0], `(${moment(isoDate).format('MMM DD, YYYY')})`);
    }
    return name;
};
// Helpers for table display rules
const getTicketUnit = (t: any): number => {
    const qty = Number(t?.no_of_tickets || 0);
    const sub = Number(t?.sub_total || 0);
    return qty > 0 ? (sub / qty) : 0;
};
const shouldShowTable = (table: any, ticket: any): boolean => {
    const totalPrice = parseFloat(table?.total_price || 0);
    if (totalPrice <= 0) return false; // hide zero-priced tables
    const tableUnit = parseFloat(table?.unit_price || 0);
    const ticketUnit = getTicketUnit(ticket);
    // hide when table unit equals ticket unit (avoid duplicate pricing)
    return Math.abs(tableUnit - ticketUnit) > 1e-6;
};
const filterTables = (ticket: any): any[] => {
    const list = Array.isArray(ticket?.table_addons) ? ticket.table_addons : [];
    return list.filter((tb: any) => shouldShowTable(tb, ticket));
};
const formattedStartTime = computed(() => {
    if (!event?.event_details) return "";

    const details = event.event_details;
    let date: string | undefined;
    let time: string | undefined;

    if (details.event_type === "single") {
        date = details.single_event_date;
        time = details.single_end_time;
    } else if (details.event_type === "recurring") {
        date = details.recurr_start_date;
        time = details.recurr_start_time;
    }

    if (!date || !time) return "";

    return `${dayjs(date).format("dddd, MMMM D, YYYY")} · Gates open`;
});

// Aggregated totals for success page presentation
const ticketsSubtotal = computed(() => {
    if (!Array.isArray(ticketSale)) return 0;
    return ticketSale.reduce((total: number, t: any) => total + parseFloat(t?.sub_total || 0), 0);
});
const tablesTotal = computed(() => {
    if (!Array.isArray(ticketSale)) return 0;
    return ticketSale.reduce((total: number, t: any) => total + parseFloat(t?.tables_total || 0), 0);
});
const drinksTotal = computed(() => {
    if (!Array.isArray(ticketSale)) return 0;
    return ticketSale.reduce((total: number, t: any) => total + parseFloat(t?.drinks_total || 0), 0);
});
const cookoutTotal = computed(() => {
    if (!Array.isArray(ticketSale)) return 0;
    return ticketSale.reduce((total: number, t: any) => total + parseFloat(t?.cookout_total || 0), 0);
});
const wellnessTotal = computed(() => {
    if (!Array.isArray(ticketSale)) return 0;
    return ticketSale.reduce((total: number, t: any) => total + parseFloat(t?.wellness_total || 0), 0);
});
// Base amounts by drink type from line-level addons
const baseDrinkSplit = computed(() => {
  let drinksBase = 0;
  let bottlesBase = 0;
  if (Array.isArray(ticketSale)) {
    ticketSale.forEach((t: any) => {
        const list = Array.isArray(t?.drink_addons) ? t.drink_addons : [];
        list.forEach((d: any) => {
        const amt = parseFloat(d?.total_price || 0);
        if ((d?.category || '') === 'bottles') bottlesBase += amt; else drinksBase += amt;
        });
    });
  }
  return { drinksBase, bottlesBase };
});
const drinkFeesTotal = computed(() => {
    const fromSummary = fees_summary?.drink_fees_total;
    const fromFirstTicket = firstTicketSale.value?.drink_fees || 0;
    return Number((fromSummary ?? fromFirstTicket) || 0);
});
const bottleFeesTotal = computed(() => Number((fees_summary?.bottle_fees_total) || 0));
const vipFeesTotal = computed(() => Number((fees_summary?.vip_fees_total) || 0));
const mobileFeeTotal = computed(() => {
    if (fees_summary?.mobile_fee_total !== undefined) return Number(fees_summary.mobile_fee_total);
    return Number((feeBreakdown.value as any)?.mobile_fee_amount || 0);
});
const spaFeesTotal = computed(() => {
    if (fees_summary?.spa_fees_total !== undefined) return Number(fees_summary.spa_fees_total);
    return Number((feeBreakdown.value as any)?.spa_platform_fee_amount || 0);
});
const cookoutFeesTotal = computed(() => {
  if (fees_summary?.cookout_fees_total !== undefined) return Number(fees_summary.cookout_fees_total);
  return Number((feeBreakdown.value as any)?.cookout_fee_amount || 0);
});

const isCookoutTicket = (ticket: any): boolean => {
  if (!ticket) return false;
  if (Number(ticket?.cookout_total || 0) > 0) return true;
  if (ticket?.cookout_included_protein) return true;
  if (Array.isArray(ticket?.cookout_addons) && ticket.cookout_addons.length > 0) return true;
  return false;
};

const rawConsolidatedFees = computed(() => {
  const svc = Number((fees_summary?.service_fee_total ?? firstTicketSale.value?.fee) || 0);
  const proc = Number((fees_summary?.processing_fee_total ?? firstTicketSale.value?.tax) || 0);
  return svc + proc;
});
const consolidatedFees = computed(() => rawConsolidatedFees.value);

const drinksTotalWithFees = computed(() => baseDrinkSplit.value.drinksBase + drinkFeesTotal.value);
const bottleTotalWithFees = computed(() => baseDrinkSplit.value.bottlesBase + bottleFeesTotal.value);
// Consolidated fees (exclude drink/bottle fees shown separately)
const servicesSubtotal = computed(() => ticketsSubtotal.value + tablesTotal.value + cookoutTotal.value + wellnessTotal.value);
const orderSubtotal = computed(() => servicesSubtotal.value + baseDrinkSplit.value.drinksBase + baseDrinkSplit.value.bottlesBase);

const discountAmount = computed(() => Number((fees_summary?.coupon_amount ?? firstTicketSale.value?.coupan_amount) || 0));

const totalPaid = computed(() => (
    orderSubtotal.value
    - discountAmount.value
    + consolidatedFees.value
    + vipFeesTotal.value
    + drinkFeesTotal.value
    + bottleFeesTotal.value
    + mobileFeeTotal.value
    + eventTaxTotal.value
).toFixed(2));

// Cart-level event tax: same stored on each record; display only once
const eventTaxTotal = computed(() => {
  const found = Array.isArray(ticketSale) ? ticketSale.find((t: any) => Number(t?.event_tax || 0) > 0) : null;
  return Number(found?.event_tax || 0);
});

// Helper to sum payable table totals for a ticket (used in package block)
const sumPayableTable = (ticket: any): number => {
  try {
    return filterTables(ticket).reduce((total: number, tb: any) => total + Number(tb?.total_price || 0), 0);
  } catch (e) {
    return 0;
  }
};

// Build URL that will be encoded inside the QR code
const buildScanUrl = (ticketSale: any): string => {
    const rawCode = (ticketSale as any)?.ticket_qrcode_id || (ticketSale as any)?.ticket_qrcode || '';
    if (!rawCode) return '';
    // Organizer web scanner page using existing code
    return `${app_url}/organizer/scanner/scan?code=${encodeURIComponent(String(rawCode))}`;
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto p-6 grid lg:grid-cols-3 gap-8">

            <!-- Event Poster & Info -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow p-6">
                <div
                    class="w-full h-80 bg-gray-200 rounded-xl flex items-center justify-center text-gray-400 text-lg mb-4">
                    <img class="rounded-xl w-full h-full"
                        :src="event?.image_url || './../../../../assets/images/bgimageBuyticket.png'" alt="" />
                </div>
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h1 class="text-2xl font-bold">
                            {{ event?.title }}
                        </h1>
                        <p class="text-sm text-gray-500 mt-1"> {{ formattedStartTime }}</p>
                        <p class="text-sm text-gray-500">Venue: {{ event?.country }} , {{ event?.state }} , {{
                            event?.city }}</p>
                    </div>
                </div>

                <!-- QR Code Viewer -->
                <div class="mt-8">
                    <h2 class="text-lg font-semibold mb-4">🎫 Your Ticket(s)</h2>
                    <div class="flex items-center justify-center gap-4">
                        <Carousel class="w-full max-w-xs">
                            <CarouselContent>
                                <CarouselItem v-for="ticket in ticketSale" :key="ticket.ticket_id">
                                    <div>
                                        <Card>
                                            <CardContent class="flex aspect-square items-center justify-center">
                                                <!-- QR Code (URL-based, generated on frontend) -->
                                                <div class="text-center mt-4 relative inline-block">
                                                    <QrcodeVue
                                                        v-if="buildScanUrl(ticket)"
                                                        :value="buildScanUrl(ticket)"
                                                        :size="160"
                                                        level="H"
                                                        render-as="svg"
                                                        class="mx-auto"
                                                    />
                                                    <img
                                                        v-if="buildScanUrl(ticket)"
                                                        :src="`${app_url}/storage/events/logo_for_e_tickets.png`"
                                                        alt="Logo"
                                                        class="absolute inset-0 m-auto"
                                                        style="width: 65px; height: 65px; border-radius: 8px; padding: 4px;"
                                                    />
                                                </div>
                                            </CardContent>
                                        </Card>
                                    </div>
                                </CarouselItem>
                            </CarouselContent>
                            <CarouselPrevious v-if="ticketSale.length > 1" />
                            <CarouselNext v-if="ticketSale.length > 1" />
                        </Carousel>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Order Summary -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-lg font-semibold mb-3">🧾 Order Summary</h2>
                    <div class="text-sm space-y-2">
                        <!-- Tickets -->
                        <div v-for="ticket in ticketSale" :key="ticket?.ticket_id">
                            <div class="flex justify-between font-medium">
                                <span>{{ ticket?.no_of_tickets }} × {{ ticket?.ticket_name }}</span>
                                <span>{{ currencySymbol }}{{ Number(displayBasePrice(ticket)).toFixed(2) }}</span>
                            </div>

                            <!-- Cookout Included Plate -->
                            <div v-if="ticket?.cookout_included_protein" class="ml-4 mt-1 space-y-1">
                                <div class="text-xs text-gray-600 flex justify-between">
                                    <span>{{ ticket.cookout_included_protein }} (Included Plate) × 1 — </span>
                                    <span>$0.00</span>
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
                                <div class="text-xs font-semibold text-gray-700">Included Sides (Grab &amp; Go)</div>
                                <div class="text-[11px] text-gray-500">
                                    {{ ticket.cookout_included_sides.join(', ') }}
                                </div>
                            </div>

                            <!-- Wellness Add-ons -->
                            <div v-if="ticket?.wellness_addons && Array.isArray(ticket.wellness_addons) && ticket.wellness_addons.length > 0" class="ml-4 mt-1 space-y-1">
                                <div v-for="(addon, index) in ticket.wellness_addons" :key="'wellness-addon-' + index" class="text-xs text-gray-600 flex justify-between">
                                    <span>{{ formatWellnessAddonName(addon?.name) }} ({{ prettyWellnessCategory(addon?.category) }}) x{{ addon?.quantity }} — </span>
                                    <span>{{ currencySymbol }}{{ Number(addon?.total_price || 0).toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- Tables with section-scoped drinks -->
                            <div v-if="ticket?.table_addons && Array.isArray(ticket.table_addons) && ticket.table_addons.length > 0" class="ml-4 mt-1 space-y-2">
                                <div v-for="(table, index) in ticket.table_addons" :key="'table-' + index" class="space-y-1">
                                    <div class="flex justify-between text-xs text-gray-600">
                                        <span>
                                            <template v-if="parseInt(table.capacity || 0) > 0 && table.section && table.section !== '-'">
                                                Table for {{ parseInt(table.capacity || 0) }} — {{ table.section }} × {{ table.quantity }}
                                            </template>
                                            <template v-else-if="table.section && table.section !== '-'">
                                                Table — {{ table.section }} × {{ table.quantity }}
                                            </template>
                                            <template v-else>
                                                Table × {{ table.quantity }}
                                            </template>
                                        </span>
                                        <span>{{ currencySymbol }}{{ Number(table?.total_price || 0).toFixed(2) }}</span>
                                    </div>
                                    <div class="text-[11px] text-gray-500">
                                        <template v-if="table?.table_seating?.length > 0">
                                            <div v-for="seat in table.table_seating" :key="seat.id">Seat: {{ seat.name }}</div>
                                        </template>
                                    </div>
                                    <!-- Drinks under this table section -->
                                    <div v-if="ticket?.drink_addons && Array.isArray(ticket.drink_addons) && ticket.drink_addons.length > 0" class="ml-4 space-y-1">
                                        <div v-for="(drink, di) in ticket.drink_addons.filter((d: any) => (d.section || '-') === (table.section || '-'))" :key="'drink-sec-' + index + '-' + di" class="text-xs text-gray-600 flex justify-between">
                                            <span>{{ drink.name }} ({{ prettyCategory(drink.category) }}) x{{ drink.quantity }} — </span>
                                            <span>{{ currencySymbol }}{{ Number(drink.total_price || 0).toFixed(2) }}</span>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Drinks not tied to any section (shown once, not duplicated) -->
                            <div v-if="ticket?.drink_addons && Array.isArray(ticket.drink_addons) && ticket.drink_addons.length > 0" class="ml-4 mt-1 space-y-1">
                                <div v-for="(drink, index) in ticket.drink_addons.filter((d: any) => !d.section || d.section === '-')" :key="'drink-free-' + index" class="text-xs text-gray-600 flex justify-between">
                                    <span>{{ drink.name }} ({{ prettyCategory(drink.category) }}) x{{ drink.quantity }} — </span>
                                    <span>{{ currencySymbol }}{{ Number(drink.total_price || 0).toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- Package Data -->
                            <div v-if="ticket?.package_data" class="ml-4 mt-2 p-3 bg-purple-50 rounded-lg border border-purple-200">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-sm font-semibold text-purple-700">📦 {{ ticket.package_data.name }}</span>
                                </div>
                                <div class="space-y-1 text-xs text-gray-700">
                                    <div v-if="sumPayableTable(ticket) > 0" class="font-medium">Table {{ currencySymbol }}{{ sumPayableTable(ticket).toFixed(2) }}</div>
                                    <!-- Bottles -->
                                    <div v-if="ticket.package_data.bottles && ticket.package_data.bottles.length > 0">
                                        <span class="font-semibold text-purple-600">🍹 Bottles:</span>
                                        <span class="ml-1">
                                            <span v-for="(bottle, idx) in ticket.package_data.bottles" :key="'bottle-' + idx">
                                                {{ bottle.qty }}× {{ bottle.name }}<span v-if="idx < ticket.package_data.bottles.length - 1">, </span>
                                            </span>
                                        </span>
                                    </div>
                                    <!-- Chasers -->
                                    <div v-if="ticket.package_data.chasers && ticket.package_data.chasers.length > 0">
                                        <span class="font-semibold text-purple-600">🥤 Chasers:</span>
                                        <span class="ml-1">
                                            <span v-for="(chaser, idx) in ticket.package_data.chasers" :key="'chaser-' + idx">
                                                {{ chaser.qty }}× {{ chaser.name }}<span v-if="idx < ticket.package_data.chasers.length - 1">, </span>
                                            </span>
                                        </span>
                                    </div>
                                    <!-- Waters -->
                                    <div v-if="ticket.package_data.waters && ticket.package_data.waters.length > 0">
                                        <span class="font-semibold text-purple-600">💧 Waters:</span>
                                        <span class="ml-1">
                                            <span v-for="(water, idx) in ticket.package_data.waters" :key="'water-' + idx">
                                                {{ water.qty }}× {{ water.name }}<span v-if="idx < ticket.package_data.waters.length - 1">, </span>
                                            </span>
                                        </span>
                                    </div>
                                    
                                    <!-- table seating -->
                                    <div v-if="ticket?.package_data?.table_seating && ticket.package_data?.table_seating?.length > 0">
                                        <span class="font-semibold text-purple-600">Table Seating:</span>
                                        <div class="ml-1">
                                            <div v-for="(seat, idx) in ticket.package_data.table_seating" :key="seat.id">
                                                Seat: {{ seat.name }}
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Notes -->
                                    <div v-if="ticket.package_data.notes" class="mt-2 pt-2 border-t border-purple-200">
                                        <span class="font-semibold text-purple-600">📝 Notes:</span>
                                        <span class="ml-1 italic">{{ ticket.package_data.notes }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-2">

                        <div v-if="servicesSubtotal > 0" class="flex justify-between">
                            <span>Services Subtotal:</span>
                            <span>{{ currencySymbol }}{{ servicesSubtotal.toFixed(2) }}</span>
                        </div>

                        <div v-if="baseDrinkSplit.drinksBase > 0" class="flex justify-between">
                            <span>Drinks Subtotal:</span>
                            <span>{{ currencySymbol }}{{ baseDrinkSplit.drinksBase.toFixed(2) }}</span>
                        </div>

                        <div v-if="baseDrinkSplit.bottlesBase > 0" class="flex justify-between">
                            <span>Bottles Subtotal:</span>
                            <span>{{ currencySymbol }}{{ baseDrinkSplit.bottlesBase.toFixed(2) }}</span>
                        </div>

                        <!-- Subtotal (Tickets + Tables) -->
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>{{ currencySymbol }}{{ orderSubtotal.toFixed(2) }}</span>
                        </div>

                        <!-- Coupon Discount -->
                        <div v-if="(fees_summary?.coupon_amount || ticketSale[0]?.coupan_amount) > 0" class="flex justify-between text-green-600">
                            <span>Discount:</span>
                            <span>-{{ currencySymbol }}{{ discountAmount.toFixed(2) }}</span>
                        </div>

                        <hr class="my-2">

                        <!-- Fees (Service and Processing only) -->
                        <div v-if="consolidatedFees > 0" class="flex justify-between">
                            <span>Service &amp; Processing Fee:</span>
                            <span>{{ currencySymbol }}{{ consolidatedFees.toFixed(2) }}</span>
                        </div>

                        <!-- Table Package Fee (separate) -->
                        <div v-if="vipFeesTotal > 0" class="flex justify-between">
                            <span>Table Processing Fees:</span>
                            <span>{{ currencySymbol }}{{ vipFeesTotal.toFixed(2) }}</span>
                        </div>

                        <!-- Drink Fees (separate) -->
                        <div v-if="drinkFeesTotal > 0" class="flex justify-between">
                            <span>Drink Processing Fees:</span>
                            <span>{{ currencySymbol }}{{ drinkFeesTotal.toFixed(2) }}</span>
                        </div>

                        <!-- Bottle Fees (separate) -->
                        <div v-if="bottleFeesTotal > 0" class="flex justify-between">
                            <span>Bottle Processing Fees:</span>
                            <span>{{ currencySymbol }}{{ bottleFeesTotal.toFixed(2) }}</span>
                        </div>

                        <!-- Mobile Fee (separate) -->
                        <div v-if="mobileFeeTotal > 0" class="flex justify-between">
                            <span>Mobile Fee:</span>
                            <span>{{ currencySymbol }}{{ mobileFeeTotal.toFixed(2) }}</span>
                        </div>

                        <!-- Event Tax (cart-level) -->
                        <div v-if="eventTaxTotal > 0" class="flex justify-between">
                            <span>Tax:</span>
                            <span>{{ currencySymbol }}{{ eventTaxTotal.toFixed(2) }}</span>
                        </div>

                        <hr class="my-2 border-t-2">

                        <!-- Total -->
                        <div class="flex justify-between font-bold text-base">
                            <span>Total:</span>
                            <span>{{ currencySymbol }}{{ totalPaid }}</span>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-lg font-semibold mb-3">📇 Contact Information</h2>
                    <p class="text-sm mb-1">Name: <strong>{{ event?.organizer?.organizer_name }}</strong></p>
                    <p class="text-sm mb-1">Email: <a href="mailto:`${{ event?.organizer?.contacts?.email }}`"
                            class="text-blue-600">{{ event?.organizer?.contacts?.email }}</a></p>
                    <p class="text-sm">Delivery Method: <strong>Eticket</strong></p>
                </div>

                <!-- Sponsors -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-lg font-semibold mb-3">🤝 Sponsors</h2>
                    <div v-if="sponsor && sponsor.length > 0" class="space-y-4">
                        <div v-for="sp in sponsor" :key="sp.id" class="flex flex-col items-center">
                            <div class="w-full h-24 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                                <img v-if="sp?.image_url" :src="sp.image_url" :alt="sp.name + ' Logo'"
                                    class="w-full h-full object-contain" />
                                <span v-else class="text-gray-400 italic text-sm">No logo</span>
                            </div>
                            <div class="mt-2 text-center">
                                <p class="font-medium text-sm">{{ sp?.name }}</p>
                                <p v-if="sp?.description" class="text-xs text-gray-500 mt-1">{{ sp?.description }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center text-gray-400 italic text-sm py-4">
                        No sponsors for this event
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="flex justify-center mt-8 gap-4">
            <div class="w-full mt-16 bg-white p-10 rounded-lg">
                <h1 class="text-black font-bold text-3xl">More events from organizer</h1>
                <Carousel class="mt-5">
                    <CarouselPrevious class="absolute left-[calc(100%-100px)] top-[-40px] -translate-y-1/2 z-10" />
                    <CarouselNext class="absolute -right-[-20px] top-[-40px] -translate-y-1/2 z-10" />
                    <CarouselContent>
                        <CarouselItem v-for="event in otherEvents" :key="event.id"
                            class="basis-full sm:basis-1/2 md:basis-1/2 lg:basis-1/2 xl:basis-1/4">
                            <EventCard :event="event" />
                        </CarouselItem>
                    </CarouselContent>
                </Carousel>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
