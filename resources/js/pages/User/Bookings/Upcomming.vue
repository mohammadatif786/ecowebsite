<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { TicketSale } from "@/client/models/TicketSale";
import GroupedEventCard, { GroupedEventCardProps } from "@/components/front/GroupedEventCard.vue";
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";

const { ticketSales } = defineProps<{
    ticketSales: TicketSale[];
}>();

// Group tickets by event
const groupedTickets = computed(() => {
    const groups: Record<number, {
        event_id: number;
        tickets: TicketSale[];
        totalTickets: number;
        totalAmount: number;
        event: any;
        latestTicket: TicketSale;
    }> = {};

    ticketSales.forEach((ticket: any) => {
        const eventId = ticket.link_up_event_id;


        if (!groups[eventId]) {
            groups[eventId] = {
                event_id: eventId,
                tickets: [],
                totalTickets: 0,
                totalAmount: 0,
                event: ticket.event,
                latestTicket: ticket
            };
        }

        groups[eventId].tickets.push(ticket);
        groups[eventId].totalTickets += parseInt(ticket.no_of_tickets || 0);

        // Use stripe_price directly (now includes all fees and matches Stripe total)
        let paidAmount = 0;
        if (ticket.stripe_price) {
            paidAmount = parseFloat(ticket.stripe_price);
        } else {
            // Fallback calculation for older records without stripe_price
            const subtotal = parseFloat(ticket.sub_total || 0);
            const drinks = parseFloat(ticket.drinks_total || 0);
            const tables = parseFloat(ticket.tables_total || 0);
            const discount = parseFloat(ticket.coupan_amount || 0);
            const fee = parseFloat(ticket.fee || 0);
            const tax = parseFloat(ticket.tax || 0);
            
            // Extract mobile fee from fee_breakdown
            const breakdown = typeof ticket.fee_breakdown === 'string' ? JSON.parse(ticket.fee_breakdown) : (ticket.fee_breakdown || {});
            const mobileFee = parseFloat(breakdown.mobile_fee_amount || 0);
            
            paidAmount = subtotal + drinks + tables - discount + fee + tax + mobileFee;
        }
        groups[eventId].totalAmount += paidAmount;

        // Keep the latest ticket for display
        if (new Date(ticket.created_at) > new Date(groups[eventId].latestTicket.created_at)) {
            groups[eventId].latestTicket = ticket;
        }
    });

    return Object.values(groups);
});

// Launcher & App visibility
const launcherVisible = ref(true);
const appVisible = ref(false);

// Initialize app
const initApp = () => {
    appVisible.value = true;
    launcherVisible.value = false;
    console.log("App initialized!");
};

// Autorun on page load
onMounted(() => {
    const autorun = new URLSearchParams(location.search).get("autorun") === "1";
    if (autorun) initApp();
});
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Upcoming Bookings" />
        <!-- Grouped Event Cards -->
        <div class="bg-gray-100 p-6 mb-8 rounded-2xl">
            <h1 class="text-3xl font-bold mb-6 text-black">Ticket Record's</h1>
            <!-- Conditional Rendering -->
            <div v-if="groupedTickets.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <GroupedEventCard v-for="group in groupedTickets" :key="group.event_id"
                    :event-group="group" :allow-cancel="true" />
            </div>

            <div v-else class="text-center text-gray-500 py-10 text-lg font-medium">
                No Ticket Record found
            </div>
        </div>
    </AuthenticatedLayout>
</template>
