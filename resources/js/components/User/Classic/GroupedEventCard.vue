<script setup lang="ts">
import { LinkupEvent } from '@/client/models/LinkupEvent';
import { TicketSale } from '@/client/models/TicketSale';
import { Link, router } from '@inertiajs/vue3';
import moment from 'moment';
import { computed, ref } from 'vue';

export type GroupedEventCardProps = {
    eventGroup: {
        event_id: number;
        tickets: TicketSale[];
        totalTickets: number;
        totalAmount: number;
        event: LinkupEvent;
        latestTicket: TicketSale;
    };
    allowCancel?: boolean;
}

const { eventGroup, allowCancel } = defineProps<GroupedEventCardProps>();

const showDetails = ref(false);

const truncatedDescription = computed(() => {
    const text = eventGroup.event.description
        ? eventGroup.event.description.replace(/<[^>]+>/g, '')
        : '';
    return text.length > 34 ? text.substring(0, 34) + '...' : text;
});
const formattedEventDate = computed(() => {
    const details = eventGroup.event.event_details;

    if (!details || !details.event_type) return '';

    if (details.event_type === 'single') {
        const date = moment(details.single_event_date).format('DD MMM YYYY');
        const start = moment(details.start_time).format('hh:mm A');
        const end = moment(details.end_time).format('hh:mm A');
        return `${date} • ${start} - ${end}`;
    }

    if (details.event_type === 'recurring') {
        const start = moment(details.recurr_start_date).format('DD MMM YYYY');
        const end = moment(details.recurr_end_date).format('DD MMM YYYY');
        return `${start} → ${end}`;
    }

    return '';
});

// Group tickets by stripe_id (purchase transaction)
const groupedByPurchase = computed(() => {
    const groups: Record<string, {
        stripe_id: string | null;
        tickets: TicketSale[];
        totalTickets: number;
        totalAmount: number;
        mobileFeeTotal: number;
        latestTicket: TicketSale;
    }> = {};

    eventGroup.tickets.forEach((ticket: any) => {
        const stripeId = ticket.stripe_id || `free-${ticket.id}`;

        if (!groups[stripeId]) {
            groups[stripeId] = {
                stripe_id: ticket.stripe_id || null,
                tickets: [],
                totalTickets: 0,
                totalAmount: 0,
                mobileFeeTotal: 0,
                latestTicket: ticket
            };
        }

        groups[stripeId].tickets.push(ticket);
        groups[stripeId].totalTickets += parseInt(ticket.no_of_tickets || 0);

        // Extract mobile fee from fee_breakdown
        const breakdown = typeof ticket.fee_breakdown === 'string' ? JSON.parse(ticket.fee_breakdown) : (ticket.fee_breakdown || {});
        const mobileFee = parseFloat(breakdown.mobile_fee_amount || 0);
        groups[stripeId].mobileFeeTotal += mobileFee;

        // Calculate amount paid for this ticket
        let paidAmount = 0;
        if (ticket.stripe_price) {
            paidAmount = parseFloat(ticket.stripe_price);
        } else {
            const subtotal = parseFloat(ticket.sub_total || 0);
            const drinks = parseFloat(ticket.drinks_total || 0);
            const tables = parseFloat(ticket.tables_total || 0);
            const discount = parseFloat(ticket.coupan_amount || 0);
            const fee = parseFloat(ticket.fee || 0);
            const tax = parseFloat(ticket.tax || 0);

            paidAmount = subtotal + drinks + tables - discount + fee + tax + mobileFee;
        }
        groups[stripeId].totalAmount += paidAmount;

        // Keep the latest ticket for display
        if (new Date(ticket.created_at) > new Date(groups[stripeId].latestTicket.created_at)) {
            groups[stripeId].latestTicket = ticket;
        }
    });

    return Object.values(groups);
});

const cancelBooking = (ticketSale:any) => {
    router.post(route('frontend.bookings.cancel', ticketSale), {
        preserveScroll: true,
        preserveState: false,
    });
}

const toggleDetails = () => {
    showDetails.value = !showDetails.value;
};
</script>

<template>
    <div class="bg-white rounded-lg shadow-md overflow-hidden w-full max-w-sm">
        <!-- Event Image -->
        <div class="relative">
            <img :src="eventGroup.event.image_url || 'https://community.softr.io/uploads/db9110/original/2X/7/74e6e7e382d0ff5d7773ca9a87e6f6f8817a68a6.jpeg'"
                alt="Event Image" class="w-full h-32 object-cover">
            <div class="absolute bottom-2 left-2 bg-white/80 text-gray-800 text-xs font-semibold px-2 py-1 rounded-md shadow">
                {{ formattedEventDate }}
            </div>
            <!-- Total Tickets Badge -->
            <div class="absolute top-2 right-2 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow">
                {{ eventGroup.totalTickets }} Tickets
            </div>
        </div>

        <!-- Event Details -->
        <div class="p-4 flex flex-col gap-2">
            <h2 class="text-sm font-bold text-gray-900">{{ eventGroup.event.title }}</h2>
            <p class="text-gray-500 text-xs leading-snug line-clamp-2">{{ truncatedDescription }}</p>

            <!-- Summary Info -->
            <div class="bg-gray-50 rounded-lg p-3 mt-2 space-y-1">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-600">Total Purchases:</span>
                    <span class="font-semibold text-gray-900">{{ groupedByPurchase.length }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-600">Total Tickets:</span>
                    <span class="font-semibold text-gray-900">{{ eventGroup.totalTickets }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-600">Total Paid:</span>
                    <span class="font-semibold text-green-600">${{ eventGroup.totalAmount.toFixed(2) }}</span>
                </div>
            </div>

            <!-- Toggle Details Button -->
            <button @click="toggleDetails"
                class="w-full bg-gray-100 text-gray-700 text-xs px-3 py-2 rounded-lg hover:bg-gray-200 transition mt-2 flex items-center justify-center gap-2">
                <span>{{ showDetails ? 'Hide Details' : 'Show All Purchases' }}</span>
                <svg :class="{ 'rotate-180': showDetails }" class="w-4 h-4 transition-transform" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <!-- Detailed Purchase List -->
            <div v-if="showDetails" class="mt-3 space-y-2 max-h-64 overflow-y-auto">
                <div v-for="purchase in groupedByPurchase" :key="purchase.stripe_id || purchase.latestTicket.id"
                    class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-gray-900">
                                {{ purchase.tickets.length > 1 ? `${purchase.tickets.length} Tickets` : purchase.latestTicket.ticket_name }}
                            </p>
                            <p class="text-xs text-gray-500">{{ moment(purchase.latestTicket.created_at).format('DD MMM YYYY, h:mm A') }}</p>
                            <p v-if="purchase.stripe_id" class="text-xs text-gray-400 mt-1">Order #{{ purchase.stripe_id.slice(-8) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-green-600">${{ purchase.totalAmount.toFixed(2) }}</p>
                            <p class="text-xs text-gray-400">Total Paid</p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-xs text-gray-600 mb-2">
                        <span>Total Quantity: {{ purchase.totalTickets }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full"
                            :class="{
                                'bg-green-100 text-green-700': purchase.latestTicket.ticket_status === 'confirmed',
                                'bg-yellow-100 text-yellow-700': purchase.latestTicket.ticket_status === 'pending',
                                'bg-red-100 text-red-700': purchase.latestTicket.ticket_status === 'cancelled'
                            }">
                            {{ purchase.latestTicket.ticket_status }}
                        </span>
                    </div>

                    <!-- Individual Tickets in Purchase -->
                    <div v-if="purchase.tickets.length > 1" class="text-xs text-gray-600 space-y-1 mb-2 border-t border-gray-200 pt-2">
                        <p class="font-medium text-gray-700 mb-1">Tickets in this purchase:</p>
                        <div v-for="(ticket, idx) in purchase.tickets" :key="ticket.id" class="ml-2">
                            <span class="font-medium">{{ ticket.ticket_name }}</span> - {{ ticket.no_of_tickets }} × {{ ticket.ticket_type }}
                        </div>
                    </div>

                    <!-- Addons Summary (from all tickets in purchase) -->
                    <div class="text-xs text-gray-600 space-y-1 mb-2">
                        <div v-if="purchase.tickets.some(t => t.drink_addons && t.drink_addons.length > 0)">
                            <span class="font-medium">Drinks:</span>
                            <template v-for="ticket in purchase.tickets" :key="'drinks-' + ticket.id">
                                <span v-for="(drink, idx) in ticket.drink_addons" :key="idx" class="ml-1">
                                    {{ drink.name }} ({{ drink.quantity }}),
                                </span>
                            </template>
                        </div>
                        <div v-if="purchase.tickets.some(t => t.table_addons && t.table_addons.length > 0)">
                            <span class="font-medium">Tables:</span>
                            <template v-for="ticket in purchase.tickets" :key="'tables-' + ticket.id">
                                <span v-for="(table, idx) in ticket.table_addons" :key="idx" class="ml-1">
                                    {{ table.section }} ({{ table.quantity }}),
                                </span>
                            </template>
                        </div>
                        <div v-if="purchase.mobileFeeTotal > 0">
                            <span class="font-medium text-blue-600">Mobile Fee:</span>
                            <span class="ml-1">${{ purchase.mobileFeeTotal.toFixed(2) }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-1">
                        <Link :href="route('frontend.bookings.eticket', purchase.latestTicket.ticket_qrcode)" preserve-scroll preserve-state>
                            <button class="w-full bg-blue-500 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-blue-600 transition">
                                View E-Ticket{{ purchase.tickets.length > 1 ? 's' : '' }}
                            </button>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div v-if="!showDetails" class="flex justify-between gap-2 mt-3">
                <Link :href="route('frontend.bookings.eticket', eventGroup.latestTicket.ticket_qrcode)" as-child preserve-scroll>
                    <button class="flex-1 bg-blue-500 text-white text-xs px-3 py-1.5 rounded-full hover:bg-blue-600 transition">
                        View Latest Ticket
                    </button>
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.rotate-180 {
    transform: rotate(180deg);
}
</style>
