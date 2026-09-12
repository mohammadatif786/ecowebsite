<script setup lang="js">
import { ref, watch, computed, nextTick } from 'vue'
import { Ticket, X, Pencil, Trash2, Plus } from 'lucide-vue-next'

const props = defineProps({
    events: {
        type: Array,
        default: () => []
    },
    eventTickets: {
        type: Object,
        default: () => ({})
    }
})
const selectedEventId = ref(null)
const showModal = ref(false)
const selectedEvent = ref(null)

const emit = defineEmits(['select-event', 'add-ticket', 'edit-ticket', 'delete-ticket'])

watch(selectedEventId, (newId) => {
    emit('select-event', newId)
})

const getTicketCount = (eventId) => {
    return props.eventTickets[eventId]?.length || 0
}

const openModal = (event) => {
    selectedEvent.value = event
    selectedEventId.value = event.id
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    selectedEvent.value = null
}

const addTicketToEvent = () => {
    if (selectedEvent.value) {
        const eventId = selectedEvent.value.id;
        closeModal();
        nextTick(() => {
            emit('add-ticket', eventId);
        });
    }
}

const editTicket = (ticket) => {
    if (!ticket) {
        console.error('editTicket called with null ticket');
        return;
    }
    const title = selectedEvent.value?.title;

    // Close the current EventList modal first
    closeModal();

    // Use nextTick to ensure the first modal is unmounted before parent opens the next one
    nextTick(() => {
        emit('edit-ticket', ticket, title);
    });
}

const deleteTicket = (ticketId) => {
    emit('delete-ticket', ticketId)
    // Optional: stay in modal and let Index refresh the list
}

const formatPrice = (price) => {
    if (!price || price === 0) return 'Free'
    return `$${Number(price).toFixed(2)}`
}
</script>
<template>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-5">
        <button v-for="ev in props.events" :key="ev.id" type="button"
            class="card overflow-hidden text-left hover:shadow-xl hover:-translate-y-0.5 transition-all group"
            @click="openModal(ev)">
            <div class="relative h-40 overflow-hidden bg-slate-100">
                <img v-if="ev.image_url || (ev.gallery_urls && ev.gallery_urls.length)"
                    :src="ev.image_url || ev.gallery_urls[0]"
                    class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"/>
                <div v-else class="h-full w-full flex items-center justify-center text-slate-300">
                    <Ticket class="w-10 h-10" />
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
                <span
                    class="absolute top-3 right-3 rounded-full bg-white/95 backdrop-blur px-3 py-1 text-[11px] font-black shrink-0"
                    :style="{ color: getTicketCount(ev.id) ? '#4f46e5' : '#94a3b8' }">
                    {{ getTicketCount(ev.id) }} ticket{{ getTicketCount(ev.id) === 1 ? '' : 's' }}
                </span>
                <p class="absolute bottom-3 left-3 right-3 text-white font-black text-base leading-tight truncate drop-shadow">{{ ev.title }}</p>
            </div>
            <div class="p-3.5 flex items-center gap-2 text-slate-500">
                <Ticket class="w-4 h-4 shrink-0" />
                <span class="text-[12px] font-bold">Tap to manage tickets</span>
            </div>
        </button>
    </div>

    <!-- Modal -->
    <dialog v-if="showModal && selectedEvent"
        class="fixed inset-0 m-auto rounded-[20px] w-full max-w-lg max-h-[88vh] overflow-y-auto shadow-2xl backdrop:bg-black/55 p-0 z-50"
        :open="showModal"
        @click.self="closeModal">
        <div class="card p-1">
            <div class="flex items-center justify-between mb-1">
                <h3 class="font-black text-lg">{{ selectedEvent.title }}</h3>
                <button @click="closeModal" class="h-8 w-8 rounded-lg hover:bg-slate-100 grid place-items-center">
                    <X class="w-4 h-4" />
                </button>
            </div>
            <p class="text-[11px] text-slate-400 mb-3">Tickets available for this event</p>

            <div class="space-y-2 mb-4">
                <div v-if="props.eventTickets && props.eventTickets[selectedEvent.id] && props.eventTickets[selectedEvent.id].length > 0"
                    v-for="ticket in props.eventTickets[selectedEvent.id]"
                    :key="ticket.id"
                    class="flex items-center justify-between rounded-xl border border-slate-100 p-3">
                    <div class="min-w-0">
                        <p class="font-bold text-sm truncate">{{ ticket.name }}</p>
                        <p class="text-[11px] text-slate-400">{{ formatPrice(ticket.price) }} · {{ ticket.quantity || ticket.qty || 0 }} available</p>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button @click="editTicket(ticket)" class="h-8 w-8 rounded-lg hover:bg-slate-100 grid place-items-center transition">
                            <Pencil class="w-4 h-4" />
                        </button>
                        <button @click="deleteTicket(ticket.id)" class="h-8 w-8 rounded-lg hover:bg-rose-50 text-rose-600 grid place-items-center transition">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
                <p v-else class="text-slate-400 text-sm text-center py-6">No tickets added yet.</p>
            </div>

            <button @click="addTicketToEvent" class="btn btn-primary w-full py-2.5 font-black bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                <Plus class="w-4 h-4 inline mr-1" /> Add Ticket to Event
            </button>
        </div>
    </dialog>
</template>
