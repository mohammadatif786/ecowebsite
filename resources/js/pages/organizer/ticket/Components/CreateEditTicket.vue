<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import TablePackage from './TablePackage.vue';
import DeleteWarningModal from './DeleteWarningModal.vue';
import DrinkAddOn from './DrinkAddOn.vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import Cookout from './Cookout.vue';
import Wellness from './Wellness.vue';

const emit = defineEmits(['ticket-deleted', 'ticket-updated']);

const props = defineProps({
    filteredTickets: {
        type: Object,
        default: () => []
    },
    category: {
        type: Array,
        default: () => []
    },
    newTickets: {
        type: Array,
        default: () => []
    },
    package: {
        type: Array,
        default: () => []
    },
    drink: {
        type: Object as () => Record<string, any[]>,
        default: () => []
    }
})
const selectedDrinks = ref<Array<{ name: string; price: number; quantity: number }>>([]);
const currentTicketId = ref<any>(null);
const currentDrinkTicketIndex = ref<number | null>(null);
const showDrinkModal = ref(false);
const openCloeRemoveModal = ref(false);

const allTickets = computed(() => {
    const existing = props.filteredTickets?.tickets || []
    return [...existing, ...props.newTickets]
});

watch(() => props.filteredTickets, () => { }, { deep: true });

const savingKeys = ref<Set<string>>(new Set());
const serverErrors = ref<Record<string, Record<string, string[]>>>({});

const getTicketSavingKey = (ticket: any, index: number) => {
    const id = ticket?.id;
    if (id !== null && id !== undefined && id !== '') return `id:${id}`;
    return `new:${index}`;
}

const extractSavedTicket = (data: any) => {
    if (!data) return null;
    if (data.ticket) return data.ticket;
    if (data.data) return data.data;
    if (data.result) return data.result;
    return data;
}

const createUpdateTicket = async (ticket: any, index: number) => {
    const key = getTicketSavingKey(ticket, index);
    if (savingKeys.value.has(key)) return;
    savingKeys.value.add(key);

    // Clear errors for this specific ticket
    if (serverErrors.value[key]) delete serverErrors.value[key];

    try {
        const response = await axios.post(route('organizer.ticket.store.update', ticket));
        toast.success(response.data.message);

        const saved = extractSavedTicket(response.data);
        if (saved && typeof saved === 'object') {
            Object.assign(ticket, saved);
        }

        emit('ticket-updated', ticket);
    } catch (error: any) {
        if (error.response?.status === 422) {
            serverErrors.value[key] = error.response.data.errors || {};
            toast.error("Please check the form for errors.");
        } else {
            toast.error(error.response?.data?.message || 'An error occurred while saving the ticket.');
        }
    } finally {
        savingKeys.value.delete(key);
    }
}

const handleTicketUpdate = (index: number, updatedTicket: any) => {
    const ticketToUpdate = allTickets.value[index]
    if (!ticketToUpdate) return

    Object.assign(ticketToUpdate, updatedTicket)
}

const removeTicketModal = (ticketId: number) => {
    currentTicketId.value = ticketId;
    openCloeRemoveModal.value = true
}

const handleTicketDeleted = (ticketId: number) => {
    emit('ticket-deleted', ticketId);
}

const openDrinkModal = (ticket: any, index: number) => {
    currentDrinkTicketIndex.value = index;
    showDrinkModal.value = true;
    setTimeout(() => {
        const modal = document.getElementById('dlg-ticket-drinks');
        if (modal) modal.classList.remove('hidden');
    }, 50);
}

const closeDrinkModal = () => {
    const modal = document.getElementById('dlg-ticket-drinks');
    if (modal) modal.classList.add('hidden');
    setTimeout(() => {
        showDrinkModal.value = false;
        currentDrinkTicketIndex.value = null;
    }, 50);
}

const handleSaveDrinks = (drinks: any) => {
    selectedDrinks.value = drinks;
    if (currentDrinkTicketIndex.value !== null) {
        const currentTicket = allTickets.value[currentDrinkTicketIndex.value];
        if (currentTicket) {
            currentTicket.drink_addons = JSON.parse(JSON.stringify(drinks));
            currentTicket.has_drink_addons = "yes";
        }
    }
    closeDrinkModal();
}

const flattenDrinks = (drinkAddons: any) => {
    if (!drinkAddons) return [];
    if (Array.isArray(drinkAddons)) return drinkAddons; // Legacy
    if (drinkAddons.items) {
        const arr: any[] = [];
        for (const cat in drinkAddons.items) {
            for (const item of drinkAddons.items[cat]) {
                arr.push({ category: cat, ...item });
            }
        }
        return arr;
    }
    return [];
}

const removeSelectedDrink = (ticket: any, drinkToRemove: any, index: number) => {
    if (Array.isArray(ticket.drink_addons)) {
        ticket.drink_addons = ticket.drink_addons.filter((_: any, i: number) => i !== index);
    } else if (ticket.drink_addons && ticket.drink_addons.items) {
        const cat = drinkToRemove.category;
        if (ticket.drink_addons.items[cat]) {
            const next = JSON.parse(JSON.stringify(ticket.drink_addons));
            next.items[cat] = (next.items[cat] || []).filter((d: any) => d.name !== drinkToRemove.name);
            ticket.drink_addons = next;
        }
    }
}

const formatDrinkPrice = (price: string | number, quantity: string | number): string => {
    const total = Number(price) * Number(quantity);
    return total.toFixed(2);
}

const formatDateTimeLocal = (dateString: string | null): string => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

watch(
    () => allTickets.value,
    (tickets) => {
        tickets.forEach((t: any) => {
            if (t.has_drink_addons === undefined) {
                const hasDrinks = (() => {
                    if (Array.isArray(t.drink_addons)) return t.drink_addons.length > 0;
                    return t.drink_addons?.enabled && Object.keys(t.drink_addons.items || {}).some(k => t.drink_addons.items[k]?.length > 0);
                })();
                t.has_drink_addons = hasDrinks ? "yes" : "no";
            }
        });
    },
    { immediate: true, deep: true }
);

// Ensure mutual exclusivity for Table and Drink Add-ons across all tickets
watch(
    () => allTickets.value,
    (tickets) => {
        tickets.forEach((t: any) => {
            if (t.has_table === 'yes' && t.has_drink_addons === 'yes') {
                // If both are 'yes' (which can happen on initial load or race condition),
                // we need to pick one. Assuming table takes priority if just changed to 'yes'
                // But better to check which one was recently changed.
                // For simplicity, if table is 'yes', set drink to 'no'.
                t.has_drink_addons = 'no';
            }
        });
    },
    { deep: true }
);

// We also need to handle the toggle logic manually since we can't easily track old values in a deep array watcher without extra state
const toggleTable = (ticket: any, value: string) => {
    ticket.has_table = value;
    if (value === 'yes') {
        ticket.has_drink_addons = 'no';
    }
}

const toggleDrinks = (ticket: any, value: string) => {
    ticket.has_drink_addons = value;
    if (value === 'yes') {
        ticket.has_table = 'no';
    }
}
</script>
<template>
    <div v-for="(ticket, index) in allTickets" :key="ticket.id"
        class="bg-white rounded-xl shadow-lg border border-gray-200 p-4 mb-4">
        <div class="flex justify-between items-center border-b border-gray-200 pb-2 mb-2">
            <h3 class="text-lg font-bold">
                <span class="text-indigo-600">{{ props.newTickets.length ? 'New Ticket' : 'Update Ticket' }}</span> <br>
            </h3>
            <div class="">
                <button
                    class="bg-indigo-600 text-white font-semibold py-1 px-3 rounded-full hover:bg-indigo-800 transition duration-150">
                    View Saved
                </button>
                <button @click="removeTicketModal(ticket.id)"
                    class="bg-red-600 text-white font-semibold py-1 px-3 rounded-full hover:bg-red-700 transition duration-150">
                    Remove
                </button>
                <div class="mt-3 text-end">
                    <span v-if="ticket.id"> Ticket - ID: {{ ticket.id }}</span>
                </div>
            </div>
        </div>

        <!-- Organizer Category -->
        <div class="mb-2">
            <label class="block font-bold text-sm mt-2">Organizer Category *</label>
            <select v-model="ticket.type"
                class="w-full p-2 border rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                :class="serverErrors[getTicketSavingKey(ticket, index)]?.type ? 'border-red-500' : 'border-gray-300'">
                <option disabled selected>Select Category</option>
                <option v-for="cat in props.category" :key="String(cat)" :value="cat">
                    {{ cat }}
                </option>
            </select>
            <p v-if="serverErrors[getTicketSavingKey(ticket, index)]?.type" class="text-xs text-red-500 font-bold mt-1 ml-1">{{ serverErrors[getTicketSavingKey(ticket, index)].type[0] }}</p>
            <div v-else class="text-xs text-gray-500 mt-1">Cookout / Wellness sections appear based on category.</div>
        </div>

        <!-- Event Selection -->
        <div>
            <label class="block font-bold text-sm mt-2">Event *</label>
            <select v-model="ticket.event_id"
                class="w-full p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option :value="props.filteredTickets.id" selected>
                    {{ props.filteredTickets.title }}
                </option>
            </select>
        </div>

        <!-- Ticket Type -->
        <div class="mt-3">
            <label class="font-bold text-sm mt-2">Ticket Type *</label>
            <select v-model="ticket.ticket_type"
                class="w-full p-2 border rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                :class="serverErrors[getTicketSavingKey(ticket, index)]?.ticket_type ? 'border-red-500' : 'border-gray-300'">
                <template v-if="ticket.type == 'Cookouts/Food' || ticket.type == 'Cookouts'">
                    <option value="cookout ticket food included">Cookout Ticket (Food Included)</option>
                    <option value="cookout ticket entry only">Cookout Ticket (Entry Only)</option>
                    <option value="vip plate package">VIP Plate Package</option>
                    <option value="kids plate">Kids Plate</option>
                    <option value="family bundle">Family Bundle</option>
                </template>
                <template v-else-if="ticket.type == 'Wellness and Spa'">
                    <option value="massage focus">Massage Focus</option>
                    <option value="facial or skincare">Facial / Skincare</option>
                    <option value="nails">Nails</option>
                    <option value="full spa day">Full Spa Day</option>
                    <option value="custom service">Custom Service</option>
                </template>
                <template v-else>
                    <optgroup label="General Admission & Timing">
                        <option value="general admission">General Admission</option>
                        <option value="early bird">Early Bird</option>
                        <option value="late entry">Late Entry</option>
                        <option value="last minute">Last Minute</option>
                    </optgroup>
                    <optgroup label="Premium & Perks">
                        <option value="vip">VIP</option>
                        <option value="early bird vip">Early Bird VIP</option>
                        <option value="very vip">VVIP (Very VIP)</option>
                        <option value="backstage meet greet">Backstage / Meet & Greet</option>
                        <option value="all access pass">All-Access Pass</option>
                        <option value="festival pass multi day">Festival Pass (Multi-Day)</option>
                        <option value="day pass">Day Pass</option>
                        <option value="weekend pass">Weekend Pass</option>
                    </optgroup>
                    <optgroup label="University-Themed Tickets">
                        <option value="Campus Kickoff Pass">Campus Kickoff Pass</option>
                        <option value="Freshman Fest Ticket">Freshman Fest Ticket</option>
                        <option value="student">Student Ticket</option>
                        <option value="Club Fair Entry">Club Fair Entry Ticket</option>
                        <option value="Dorm Social Pass">Dorm Social Pass Ticket</option>
                        <option value="Alumni Mixer Invite">Alumni Mixer Invite Ticket</option>
                        <option value="Exam De-Stress Ticket">Exam De-Stress Ticket</option>
                        <option value="Spring Fling Pass">Spring Fling Pass Ticket</option>
                        <option value="Faculty Talk Pass">Faculty Talk Pass Ticket</option>
                    </optgroup>
                    <optgroup label="Group & Discounts">
                        <option value="group family pack">Group / Family Pack</option>
                        <option value="couple">Couple Ticket</option>
                        <option value="student">Student Ticket</option>
                        <option value="youth">Youth Ticket</option>
                        <option value="senior">Senior Ticket</option>
                        <option value="child">Child Ticket</option>
                        <option value="member loyalty ticket">Member / Loyalty Ticket</option>
                        <option value="alumni ticket">Alumni Ticket</option>
                    </optgroup>
                    <optgroup label="Special Access">
                        <option value="press media">Press / Media</option>
                        <option value="staff crew">Staff / Crew</option>
                        <option value="speaker performer">Speaker / Performer</option>
                        <option value="sponsor partner">Sponsor / Partner</option>
                        <option value="complimentary">Complimentary (Comp)</option>
                        <option value="invite only">Invite-Only</option>
                        <option value="volunteer">Volunteer</option>
                    </optgroup>
                    <optgroup label="Reserved Seating & Tables">
                        <option value="table with bottles and seating">Table (with bottles, seating)</option>
                        <option value="reserved seating">Reserved Seating</option>
                        <option value="balcony seating">Balcony Seating</option>
                        <option value="front row seating">Front Row Seating</option>
                        <option value="box seating">Box Seating</option>
                        <option value="standing only">Standing Only</option>
                    </optgroup>
                    <optgroup label="Add-On / Bundle Tickets">
                        <option value="food drink bundle">Food + Drink Bundle</option>
                        <option value="merchandise bundle">Merchandise Bundle</option>
                        <option value="parking pass">Parking Pass</option>
                        <option value="camping pass for festivals">Camping Pass (for festivals)</option>
                        <option value="shuttle pass or transport pass">Shuttle / Transport Pass</option>
                    </optgroup>
                </template>
            </select>
            <p v-if="serverErrors[getTicketSavingKey(ticket, index)]?.ticket_type" class="text-xs text-red-500 font-bold mt-1 ml-1">{{ serverErrors[getTicketSavingKey(ticket, index)].ticket_type[0] }}</p>
            <div v-else class="text-xs text-gray-500 mt-1">Cookouts don’t use General
                Admission—guests
                pick a package.</div>
        </div>

        <label class="block font-bold text-sm mt-2">Ticket Name *</label>
        <input v-model="ticket.name"
            class="w-full p-2 border rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            :class="serverErrors[getTicketSavingKey(ticket, index)]?.name ? 'border-red-500' : 'border-gray-300'">
        <p v-if="serverErrors[getTicketSavingKey(ticket, index)]?.name" class="text-xs text-red-500 font-bold mt-1 ml-1">{{ serverErrors[getTicketSavingKey(ticket, index)].name[0] }}</p>

        <label class="block font-bold text-sm mt-2">Description</label>
        <textarea v-model="ticket.description"
            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 min-h-[82px] resize-y"></textarea>

        <div v-if="ticket && (!ticket.type.includes('Cookouts/Food') || !ticket.type.includes('Cookouts')) && !ticket.type.includes('Wellness and Spa') && ticket.has_drink_addons !== 'yes'">
            <label class="block font-bold text-sm mt-2">
                Does this ticket include a table?
            </label>
            <div class="flex gap-4 flex-wrap mt-1">
                <label class="flex items-center">
                    <input type="radio" value="no" class="mr-1" v-model="ticket.has_table">
                    No
                </label>
                <label class="flex items-center">
                    <input type="radio" value="yes" class="mr-1" v-model="ticket.has_table">
                    Yes
                </label>
            </div>
        </div>

        <!-- Table Package -->
        <TablePackage v-if="ticket && (!ticket?.type.includes('Cookouts/Food') || !ticket?.type.includes('Cookouts')) && !ticket?.type.includes('Wellness and Spa')
            && ticket.has_table === 'yes'" :package="package" :drink="drink" :ticket="ticket"
            @update:ticket="handleTicketUpdate(index, $event)" />

        <div>
            <div v-if="ticket.has_table === 'no' || ticket.has_table == undefined">
                <div>
                    <label class="block font-bold text-sm mt-2">Is this ticket free? *</label>
                </div>
                <div class="flex gap-4 flex-wrap mt-1">
                    <label class="flex items-center">
                        <input type="radio" value="no" class="mr-1" v-model="ticket.is_free">
                        No
                    </label>
                    <label class="flex items-center">
                        <input type="radio" value="yes" class="mr-1" v-model="ticket.is_free">
                        Yes
                    </label>
                </div>
            </div>

            <div v-if="ticket.is_free === 'no' && ticket.has_table === 'no'">

                <label class="block font-bold text-sm mt-2">Price</label>
                <input type="number" min="0" step="0.01" v-model="ticket.price"
                    class="w-full p-2 border rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    :class="serverErrors[getTicketSavingKey(ticket, index)]?.price ? 'border-red-500' : 'border-gray-300'" />
                <p v-if="serverErrors[getTicketSavingKey(ticket, index)]?.price" class="text-xs text-red-500 font-bold mt-1 ml-1">{{ serverErrors[getTicketSavingKey(ticket, index)].price[0] }}</p>

                <label class="block font-bold text-sm mt-2">Promo Price</label>
                <input type="number" min="0" step="0.01" v-model="ticket.promo_price"
                    class="w-full p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
            </div>

            <div v-if="ticket.has_table === 'no' || ticket.has_table === undefined">

                <label class="block font-bold text-sm mt-2">Quantity *</label>
                <input type="number" min="1" v-model="ticket.quantity"
                    class="w-full p-2 border rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    :class="serverErrors[getTicketSavingKey(ticket, index)]?.quantity ? 'border-red-500' : 'border-gray-300'" />
                <p v-if="serverErrors[getTicketSavingKey(ticket, index)]?.quantity" class="text-xs text-red-500 font-bold mt-1 ml-1">{{ serverErrors[getTicketSavingKey(ticket, index)].quantity[0] }}</p>
            </div>

            <div v-if="ticket.has_table === 'no' || ticket.has_table === undefined">

                <label class="block font-bold text-sm mt-2">Tickets per Attendee</label>
                <input type="number" min="1" v-model="ticket.tickets_per_attendee"
                    class="w-full p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
            </div>

            <label class="block font-bold text-sm mt-2">Sale Starts</label>
            <input type="datetime-local" :value="formatDateTimeLocal(ticket.sale_start)"
                @input="ticket.sale_start = ($event.target as HTMLInputElement).value"
                class="w-full p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />

            <label class="block font-bold text-sm mt-2">Sale Ends</label>
            <input type="datetime-local" :value="formatDateTimeLocal(ticket.sale_end)"
                @input="ticket.sale_end = ($event.target as HTMLInputElement).value"
                class="w-full p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />

            <label class="block font-bold text-sm mt-2">Status</label>
            <select v-model="ticket.status"
                class="w-full p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <!-- Cookout -->
            <div class="mt-4 sectionCard p-4" v-if="ticket && (ticket.type.includes('Cookouts/Food') || ticket.type.includes('Cookouts'))">
                <Cookout :ticket="ticket" />
            </div>

            <!-- Wellness -->
            <div class="mt-4 sectionCard p-4" v-if="ticket && ticket.type.includes('Wellness and Spa')">
                <Wellness :ticket="ticket" />
            </div>
        </div>

        <div v-if="ticket && ((!ticket.type.includes('Cookouts/Food') || !ticket.type.includes('Cookouts')) && !ticket.type.includes('Wellness and Spa'))
            && (ticket.has_table === 'no' || ticket.has_table === undefined)">
            <label class="block font-bold text-sm mt-2">Include drink add-ons?</label>
            <div class="flex gap-4 flex-wrap mt-1">
                <label class="flex items-center">
                    <input type="radio" class="mr-1" value="no" v-model="ticket.has_drink_addons"> No
                </label>
                <label class="flex items-center">
                    <input type="radio" class="mr-1" value="yes" v-model="ticket.has_drink_addons"> Yes</label>
            </div>
        </div>

        <div class="p-3 bg-indigo-50 border-l-4 border-indigo-600 rounded-lg mt-2"
            v-if="ticket.has_drink_addons === 'yes' && ticket.has_table == 'no'">
            <h4 class="text-base font-bold mb-1">Selected Drinks</h4>
            <div class="flex flex-col gap-2">
                <template v-if="flattenDrinks(ticket.drink_addons).length === 0">
                    <div class="text-sm text-gray-500">No drinks selected</div>
                </template>
                <template v-for="(drink, dIndex) in flattenDrinks(ticket.drink_addons)" :key="dIndex">
                    <div class="flex flex-col gap-1">
                        <div class="text-sm font-semibold text-gray-700">{{ drink.name }}</div>
                        <div class="flex gap-2 flex-wrap items-center">
                            <span class="min-w-[120px]">Qty: {{ drink.quantity || drink.qty }}</span>
                            <span class="font-bold text-green-600">${{ formatDrinkPrice(drink.price || drink.cost,
                                drink.quantity || drink.qty)
                            }}</span>
                            <button type="button" @click="removeSelectedDrink(ticket, drink, dIndex)"
                                class="text-red-600 hover:text-red-800">Remove</button>
                        </div>
                    </div>
                </template>
            </div>
            <div class="flex gap-2 flex-wrap mt-2">
                <button @click="openDrinkModal(ticket, index)"
                    class="bg-transparent border-2 border-indigo-600 text-indigo-600 font-semibold py-1 px-3 rounded-lg hover:bg-indigo-600 hover:text-white transition duration-150">
                    Edit Drinks
                </button>
            </div>
        </div>

        <div class="text-center mt-3">
            <button @click="createUpdateTicket(ticket, index)"
                :disabled="savingKeys.has(getTicketSavingKey(ticket, index))"
                class="bg-green-600 text-white font-semibold py-1 px-4 rounded-full hover:bg-green-700 transition duration-150"
                :class="{ 'opacity-50 cursor-not-allowed': savingKeys.has(getTicketSavingKey(ticket, index)) }">
                Save Ticket
            </button>
        </div>
    </div>

    <!-- Modals (Outside of the ticket loop to prevent duplicate IDs) -->
    <div v-if="openCloeRemoveModal"
        class="fixed top-0 left-0 w-full h-full flex items-center justify-center bg-black/30 z-50">
        <DeleteWarningModal @close="openCloeRemoveModal = false" @deleted="handleTicketDeleted"
            :ticketId="currentTicketId || 0" />
    </div>

    <DrinkAddOn v-if="showDrinkModal && currentDrinkTicketIndex !== null"
        :key="'drink-modal-' + currentDrinkTicketIndex" @close="closeDrinkModal" @save-drinks="handleSaveDrinks"
        :drinks="drink" :ticket="allTickets[currentDrinkTicketIndex]" />

</template>
