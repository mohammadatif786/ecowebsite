<template>
    <AppLayout>
        <div class="min-h-screen text-gray-900">
            <div class="mb-4 flex justify-center w-full">
                <SearchEvent @search="val => searchQuery = val" />
            </div>



            <div id="app">

                <div v-if="props.indexData.events && props.indexData.events.length" class="mb-4 space-y-3">


                    <EventList :events="filteredEvents" :eventTickets="eventTickets"
                        @select-event="selectedEventId = $event" @add-ticket="addNewTicket" @edit-ticket="openEditModal"
                        @delete-ticket="openDeleteModal" />
                </div>

                <div class="text-center my-4">
                    <button v-if="props.indexData.events && props.indexData.events.length > 0"
                        class="bg-indigo-600 text-white font-semibold py-2 px-4 rounded-full hover:bg-indigo-800 transition duration-150 mr-3"
                        @click="addNewTicket()">
                        + Add New Ticket
                    </button>
                    <button v-if="checkOrganizerCategories" @click=openIndividualDrinkModal
                        class="bg-transparent border-2 border-indigo-600 text-indigo-600 font-semibold py-2 px-4 rounded-full hover:bg-indigo-600 hover:text-white transition duration-150">
                        Individual Drinks
                    </button>
                    <button v-if="checkOrganizerCategories"
                        class="mx-3 bg-transparent border-2 border-indigo-600 text-indigo-600 font-semibold py-2 px-4 rounded-full hover:bg-indigo-600 hover:text-white transition duration-150"
                        @click="openDrinkPackageModal">
                        Drink Package
                    </button>
                </div>

                <div id="tickets">
                    <!-- Tickets are now managed via modals -->
                </div>
            </div>

            <!-- Modals -->

            <OrganizerSettings :drinks="drinksState" @close-drink-addon-modal="closeIndividualDrinkModal"
                @drinks-updated="handleDrinksUpdated" />

            <!-- Drink Package Modal -->
            <dialog id="dlg-drink-package"
                class="fixed inset-0 m-auto rounded-xl w-[min(96vw,800px)] max-h-[90vh] shadow-2xl backdrop:bg-black/55 p-0 overflow-hidden">
                <div
                    class="flex justify-between items-center p-4 border-b border-gray-200 bg-gradient-to-r from-indigo-600 to-purple-600">
                    <div class="text-xl font-extrabold text-white">🥂 Drink Package Builder</div>
                    <button @click="closeDrinkPackageModal"
                        class="bg-white/20 hover:bg-white/30 text-white font-semibold py-2 px-4 rounded-full transition duration-150">
                        ✕ Close
                    </button>
                </div>
                <div class="p-0 overflow-y-auto max-h-[calc(90vh-80px)]">
                    <DrinkPackage :packages="packages" @package-created="onPackageCreated"
                        @closeDrinkAddonModal="closeDrinkPackageModal" @package-updated="onPackageUpdated"
                        @package-deleted="onPackageDeleted" />
                </div>
            </dialog>

            <TicketFormModal v-if="showTicketModal"
                :key="editingTicket ? (editingTicket.id || 'new-' + Date.now()) : 'none'" :ticket="editingTicket"
                :category="category" :package="packages" :drink="drinksState" :eventTitle="eventTitleForModal"
                :events="props.indexData.events || []" :isEventLocked="isEventLocked" @close="showTicketModal = false"
                @saved="handleTicketSaved" @deleted="handleTicketDeleted" />

            <DeleteWarningModal v-if="showDeleteModal" :ticketId="ticketIdToDelete" @close="showDeleteModal = false"
                @deleted="handleTicketDeleted" />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import './style.css'

import EventList from './Components/EventList.vue';
import SearchEvent from './Components/SearchEvent.vue';
import TicketFormModal from './Components/TicketFormModal.vue';
import DeleteWarningModal from './Components/DeleteWarningModal.vue';
import { computed, ref, watch } from 'vue'
import { TicketDetail, DEFAULT_TICKET } from '@/client/models/Ticket';
import { toast } from 'vue-sonner';
import { router } from '@inertiajs/vue3';
import DrinkPackage from './Components/DrinkPackage.vue';
import OrganizerSettings from './Components/Organizer.vue';

const props = defineProps({
    indexData: {
        type: Object,
        default: () => ({})
    }
})

const searchQuery = ref('')
const selectedEventId = ref<number>(0)
const packages = ref(props.indexData.packages || [])
const drinksState = ref<Record<string, any[]>>(props.indexData.drinks || {})
const category = props.indexData.organizer_categories || [];
const eventTickets = ref<{ [key: number]: any[] }>({})

const showTicketModal = ref(false)
const showDeleteModal = ref(false)
const ticketIdToDelete = ref<number | null>(null)
const editingTicket = ref<any>(null)
const eventTitleForModal = ref('')
const isEventLocked = ref(false)

const syncData = (data: any) => {
    if (!data) return
    packages.value = data.packages || []
    drinksState.value = data.drinks || {}
    category.value = data.organizer_categories || []

    const mapping: { [key: number]: any[] } = {}
    if (data.events) {
        data.events.forEach((event: any) => {
            mapping[event.id] = event.tickets || []
        })
    }
    eventTickets.value = mapping
}

// Initial sync
syncData(props.indexData)

// Sync local refs with props when data is reloaded (e.g. after router.reload())
watch(() => props.indexData, (newData) => {
    syncData(newData)
}, { deep: true })

const addNewTicket = (eventId: any = null) => {
    // Determine the event ID: use provided id, or currently selected, or fallback to first event
    let id = (typeof eventId === 'number') ? eventId : selectedEventId.value;

    if (!id && props.indexData.events && props.indexData.events.length > 0) {
        id = props.indexData.events[0].id;
    }

    if (!id) {
        toast.error("Please select an event first");
        return;
    }

    const event = props.indexData.events.find((e: any) => e.id === id);
    if (!event) {
        toast.error("Event not found");
        return;
    }

    eventTitleForModal.value = event.title || '';
    isEventLocked.value = (typeof eventId === 'number');

    const newTicket: any = JSON.parse(JSON.stringify(DEFAULT_TICKET));
    newTicket.event_id = id;
    newTicket.type = event.category || '';

    editingTicket.value = newTicket;
    showTicketModal.value = true;
}
const openDrinkPackageModal = () => {
    const dialog = document.getElementById('dlg-drink-package');
    dialog?.showModal();
}

const closeDrinkPackageModal = () => {
    const dialog = document.getElementById('dlg-drink-package');
    dialog?.close();
}


const openIndividualDrinkModal = () => {
    const dialogDrinks = document.getElementById('dlg-individual-drinks');
    dialogDrinks?.showModal();
}
const openEditModal = (ticket: any, eventTitle: string) => {
    if (!ticket) return;

    // Ensure we have a fresh deep copy to avoid reactivity issues during modal switch
    const ticketCopy = JSON.parse(JSON.stringify(ticket));

    editingTicket.value = ticketCopy;
    eventTitleForModal.value = eventTitle;
    isEventLocked.value = true;
    showTicketModal.value = true;
}
const closeIndividualDrinkModal = () => {
    const dialogDrinks = document.getElementById('dlg-individual-drinks');
    dialogDrinks?.close();
}

const handleDrinksUpdated = (nextDrinks: Record<string, any[]>) => {
    drinksState.value = nextDrinks || {};
}
const handleTicketSaved = (savedTicket: any) => {
    showTicketModal.value = false;
    router.reload();
}
const onPackageCreated = (pkg: any) => {
    packages.value.push(pkg);
}

const onPackageUpdated = (pkg: any) => {
    const index = packages.value.findIndex((p: any) => p.id === pkg.id);
    if (index !== -1) {
        packages.value[index] = pkg;
    }
}
const openDeleteModal = (ticketId: number) => {
    ticketIdToDelete.value = ticketId;
    showDeleteModal.value = true;
}
const onPackageDeleted = (pkg: any) => {
    packages.value = packages.value.filter((p: any) => p.id !== pkg);
}
const handleTicketDeleted = (ticketId: number) => {
    showDeleteModal.value = false;
    showTicketModal.value = false;
    router.reload();
}
const checkOrganizerCategories = computed(() => {

    const excluded = ['Cookouts/Food', 'Cookouts', 'Wellness and Spa']

    return category.some((cat: string) => !excluded.includes(cat))
})
const filteredEvents = computed(() => {
    const events = props.indexData?.events || [];
    return events.filter((ev: any) =>
        ev.title?.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

</script>

<style>
@layer utilities {
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
}
</style>