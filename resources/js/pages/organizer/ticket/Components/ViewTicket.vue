<template>
    <dialog id="dlg-ticket"
        class="fixed inset-0 m-auto rounded-xl w-[min(96vw,700px)] shadow-2xl backdrop:bg-black/55 p-0">
        <div class="flex justify-between items-center p-3 border-b border-gray-200">
            <div class="text-lg font-extrabold text-indigo-600">Ticket Details</div>
            <button
                class="bg-red-600 text-white font-semibold py-1 px-3 rounded-full hover:bg-red-700 transition duration-150"
                @click="closeDlg()">Close</button>
        </div>
        <div class="p-3">
            <div class="p-4 space-y-2" v-if="ticket">
                <p><strong>Type:</strong> {{ ticket.type || '—' }}</p>
                <p v-if="ticket.name"><strong>Ticket Name:</strong> {{ ticket.name }}</p>
                <p><strong>Description:</strong> {{ ticket.description || '—' }}</p>
                <p><strong>Includes Table:</strong> {{ ticket.hasTable === 'yes' ? 'Yes' : 'No' }}</p>
                <div v-if="ticket.hasTable === 'yes'" class="pl-4 space-y-1">
                    <p><strong>Table Price:</strong> ${{ Number(ticket.tablePrice || 0).toFixed(2) }}</p>
                    <p><strong>Capacity:</strong> {{ ticket.tableCapacity || 0 }}</p>
                    <div v-if="ticket.selectedPackage">
                        <p><strong>Drink Package:</strong> {{ ticket.selectedPackage.name }}</p>
                    </div>
                </div>
                <p><strong>Quantity:</strong> {{ ticket.quantity }}</p>
                <p><strong>Per Attendee:</strong> {{ ticket.ticketsPerAttendee }}</p>
                <p>
                    <strong>Sale:</strong>
                    {{ ticket.saleStart || '—' }} → {{ ticket.saleEnd || '—' }}
                </p>
                <p><strong>Status:</strong> {{ ticket.status }}</p>

                <div class="mt-3">
                    <p class="font-semibold"><strong>Drink Add-Ons:</strong></p>
                    <div v-if="ticket.drinkAddons && ticket.drinkAddons.enabled" class="pl-4 space-y-1">
                        <template v-for="(list, cat) in ticket.drinkAddons.items" :key="cat">
                            <div v-if="Array.isArray(list) && list.length">
                                <p class="text-gray-600 text-sm">{{ cat }}:</p>
                                <ul class="ml-4 list-disc">
                                    <li v-for="(d, idx) in list" :key="idx">
                                        {{ d.name }} × {{ d.qty }} — ${{ Number(d.cost || 0).toFixed(2) }}
                                    </li>
                                </ul>
                            </div>
                        </template>
                        <p class="text-sm text-gray-700 mt-2" v-if="ticket.drinkAddons.freeOne?.enabled">
                            <em>Complimentary drink applied (category: {{ ticket.drinkAddons.freeOne.category }})</em>
                        </p>
                        <p class="mt-1"><strong>Total:</strong> ${{ Number(ticket.drinkAddons.total || 0).toFixed(2) }}
                        </p>
                    </div>
                    <div v-else class="pl-4 text-gray-500 text-sm">No drink add-ons</div>
                </div>
            </div>
            <div v-else class="p-4 text-gray-500">No ticket selected.</div>

        </div>
    </dialog>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
const props = defineProps<{ ticket: any | null, event: any }>();
const emit = defineEmits(['close']);
const closeDlg = () => {
    emit('close');
};
</script>

<style>
dialog::backdrop {
    background: rgba(0, 0, 0, 0.55);
}
</style>