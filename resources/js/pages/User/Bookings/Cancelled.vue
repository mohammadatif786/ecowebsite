<script setup lang="ts">
import { TicketSale } from "@/client/models/TicketSale";
import BookedEventCard, { BookedEventCardProps } from "@/components/front/BookedEventCard.vue";
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";

const { ticketSales } = defineProps<{
    ticketSales: TicketSale[];
}>();
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Cancelled Bookings" />
        <div class="bg-gray-100 p-6 mb-8 rounded-2xl">
            <h1 class="text-3xl font-bold mb-6">Cancelled Ticket Record's</h1>
            <div v-if="ticketSales.length > 0" class="flex flex-wrap gap-6 justify-start">
                <BookedEventCard v-for="ticketSale in ticketSales" :key="ticketSale.id"
                    :ticket-sale="ticketSale as BookedEventCardProps['ticketSale']" />
            </div>
            <div v-else class="text-center text-gray-500 py-10 text-lg font-medium">
                No Cancelled Ticket Record found
            </div>
        </div>
    </AuthenticatedLayout>
</template>
