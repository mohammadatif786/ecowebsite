<script setup lang="ts">
import { LinkupEvent } from '@/client/models/LinkupEvent';
import { Ticket } from '@/client/models/Ticket';
import { TicketSale } from '@/client/models/TicketSale';
import { useForm, Link } from '@inertiajs/vue3';
import moment from 'moment';
import { computed } from 'vue';

export type BookedEventCardProps = {
    ticketSale: TicketSale & {
        event: LinkupEvent;
        ticket: Ticket;
    };
    allowCancel?: boolean
}

const { ticketSale, allowCancel } = defineProps<BookedEventCardProps>();

const truncatedDescription = computed(() => {
  const text = ticketSale.event.description
    ? ticketSale.event.description.replace(/<[^>]+>/g, '')
    : ''
  return text.length > 34 ? text.substring(0, 34) + '...' : text
})


const form = useForm({});

const cancelBooking = () => {
    form.post(route('frontend.bookings.cancel', ticketSale), {
        preserveScroll: true,
        preserveState: false,
    });
}

</script>
<template>
<div class="bg-white rounded-lg shadow-md overflow-hidden w-56 ">
    <!-- Event Image -->
    <div class="relative">
        <img :src="ticketSale.event.image_url || 'https://community.softr.io/uploads/db9110/original/2X/7/74e6e7e382d0ff5d7773ca9a87e6f6f8817a68a6.jpeg'" alt="Event Image" class="w-full h-32 object-cover">
         <div class="absolute bottom-2 left-2 bg-white/80 text-gray-800 text-xs font-semibold px-2 py-1 rounded-md shadow">
        {{ moment(ticketSale.event.start_time).format('DD MMM YYYY') }}
      </div>
        <!-- Heart Icon -->
        <!-- <button class="absolute top-2 right-2 bg-white rounded-full p-1">
            <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                </path>
            </svg>
        </button> -->
    </div>
    <!-- Event Details -->
    <div class="p-4 flex flex-col gap-2">
      <h2 class="text-sm font-bold text-gray-900">{{ ticketSale.ticket_name }}</h2>
      <p class="text-gray-700 text-xs font-medium">{{ ticketSale.event.title }}</p>
      <p class="text-gray-500 text-xs leading-snug line-clamp-2">{{ truncatedDescription }}</p>

      <!-- Price & Month -->
      <div class="flex justify-between items-center mt-2 text-sm font-medium">
        <span class="text-gray-600">status: {{ ticketSale.cancellation_request ? ticketSale.cancellation_request.status : "--" }}</span>
        <span class="text-green-500">${{ ticketSale.total }}</span>
      </div>

      <!-- Buttons -->
      <div class="flex justify-between gap-2 mt-3">
        <button
          v-if="allowCancel"
          @click="cancelBooking"
          class="flex-1 bg-red-500 text-white text-xs px-3 py-1.5 rounded-full hover:bg-red-600 transition"
        >
          Cancel
        </button>

        <Link :href="route('frontend.bookings.eticket', ticketSale.id)" as-child preserve-scroll preserve-state:data="{ clear_flash: true }">
          <button class="flex-1 bg-blue-500 text-white text-xs px-3 py-1.5 rounded-full hover:bg-blue-600 transition">
            View Ticket
          </button>
        </Link>
      </div>
    </div>
</div>
</template>
