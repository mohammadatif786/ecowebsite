<script setup lang="ts">
import axios from 'axios';
import { toast } from 'vue-sonner';


const props = defineProps({
    ticketId: {
        type: [Number, String],
        required: true
    }
})

const removeTicket = async () => {
    try {
        // Strip any prefixes like 'TT-' if present before sending to backend
        const cleanId = String(props.ticketId).replace(/[^\d]/g, '');

        console.log('Deleting ticket with ID:', cleanId);
        const response = await axios.delete(route("organizer.ticket.destroy", cleanId));
        console.log('Delete response:', response.data);
        toast.success(response.data.message);
        emit('close')
        emit('deleted', props.ticketId)
    } catch (error: any) {
        console.error('Delete error:', error);
        toast.error(error.response?.data?.message || 'Failed to delete ticket');
    }
}

const emit = defineEmits(['close', 'deleted'])

const handleBackdropClick = (event: MouseEvent) => {
    if (event.target === event.currentTarget) {
        emit('close')
    }
}
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

<template>
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="handleBackdropClick">
        <div class="bg-white rounded-[20px] w-full max-w-md max-h-[90vh] overflow-hidden shadow-2xl transform transition-transform flex flex-col" @click.stop>
            <div class="flex justify-between items-center p-4 sticky top-0 bg-white/90 backdrop-blur-sm z-10 py-2 border-b border-slate-100">
                <div class="text-xl font-extrabold text-red-600">Delete Ticket Warning</div>
                <button @click="emit('close')"
                    class="text-slate-500 hover:text-slate-800 transition bg-slate-100 hover:bg-slate-200 p-2 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="modal-scroll overflow-y-auto p-5 flex-1">
                <div class="text-center">
                    <div class="pulse mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-red-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z" />
                        </svg>
                    </div>
                    <h2 class="text-4xl font-extrabold text-red-600 mb-4 pulse">WARNING</h2>
                    <p class="text-gray-700 text-sm">
                        This action is <strong>irreversible</strong>.<br>
                        Deleting this ticket may disrupt users who have already purchased it,<br>
                        corrupt sales reports, and negatively impact inventory tracking.
                        <br><br>
                        Proceed only if you are absolutely sure.
                    </p>
                </div>
                <div class="flex flex-col gap-3 w-full mt-6">
                    <button class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg"
                        @click="removeTicket">
                        Yes, I Understand the Risk
                    </button>
                    <button @click="emit('close')"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-lg">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>
