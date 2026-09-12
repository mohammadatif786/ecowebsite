<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between mb-6 no-print">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Print Tickets</h1>
                    <p class="text-sm text-gray-500">
                        {{ event?.title }} • {{ attendees.length }} ticket sale(s)
                    </p>
                </div>
                <div class="flex gap-2">
                    <button type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                        @click="goBack">
                        Back
                    </button>
                    <button type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                        @click="printPage">
                        Print
                    </button>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6 print-page">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ event?.title }}</h2>
                        <p class="text-xs text-gray-500">
                            Generated on {{ generatedAt }}
                        </p>
                    </div>
                    <div class="text-right text-xs text-gray-500">
                        <p>Event ID: {{ event?.id }}</p>
                        <p>Total tickets: {{ attendees.length }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="attendee in attendees" :key="attendee.id"
                        class="border border-gray-200 rounded-lg p-4 relative overflow-hidden ticket-card">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-xs font-semibold text-gray-500">
                                Ref: {{ attendee.ticket_qrcode_id }}
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold tracking-wide uppercase"
                                :class="statusClass(attendee.ticket_status)">
                                {{ attendee.ticket_status }}
                            </span>
                        </div>

                        <div class="mb-2">
                            <div class="text-sm font-semibold text-gray-900 truncate">
                                {{ attendee.event?.title || 'Event' }}
                            </div>
                            <div class="text-[11px] text-gray-500">
                                Order: {{ formatDate(attendee.created_at) }}
                            </div>
                        </div>

                        <div class="space-y-1 text-[11px] text-gray-700">
                            <div class="flex justify-between">
                                <span class="font-medium">Attendee</span>
                                <span class="truncate max-w-[140px] text-right">
                                    {{ attendee.user?.name || 'N/A' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Email</span>
                                <span class="truncate max-w-[140px] text-right">
                                    {{ attendee.user?.email || 'N/A' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Tickets</span>
                                <span>{{ attendee.no_of_tickets }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Ticket Type</span>
                                <span>{{ attendee.ticket_type || 'General' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Payment</span>
                                <span>{{ attendee.payment_method || 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Total</span>
                                <span>${{ Number(attendee.total || 0).toFixed(2) }}</span>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between text-[10px] text-gray-500">
                            <span>
                                Checked in:
                                <strong>{{ attendee.ticket_status === 'checked_in' ? 'Yes' : 'No' }}</strong>
                            </span>
                            <div
                                class="w-12 h-12 border border-dashed border-gray-300 flex items-center justify-center text-[9px] text-gray-400">
                                <img :src="props.appURL + '/storage/' + attendee.web_qrcode" alt="QR Code">
                            </div>
                        </div>
                    </div>
                </div>

                <p class="mt-6 text-[10px] text-gray-400 text-center">
                    Present these tickets at the event entrance. Each card corresponds to a ticket sale record.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/organizer/AppLayout.vue';

const props = defineProps<{
    event: any;
    attendees: any[];
    appURL: string;
}>();

const generatedAt = computed(() => {
    return new Date().toLocaleString();
});

const printPage = () => {
    window.print();
};

const goBack = () => {
    router.visit(document.referrer || route('organizer.event.report.attendees', props.event.id));
};

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString([], {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};

const statusClass = (status: string) => {
    switch (status) {
        case 'paid':
        case 'confirmed':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'canceled':
        case 'failed':
            return 'bg-red-100 text-red-800';
        case 'checked_in':
            return 'bg-emerald-100 text-emerald-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>

<style scoped>
@media print {
    .no-print {
        display: none !important;
    }

    .print-page {
        box-shadow: none;
        border-radius: 0;
        padding: 0;
    }

    body {
        background: #ffffff !important;
    }
}

.ticket-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle at 0 0, rgba(59, 130, 246, 0.12), transparent 55%),
        radial-gradient(circle at 100% 100%, rgba(16, 185, 129, 0.12), transparent 55%);
    pointer-events: none;
}

.ticket-card>* {
    position: relative;
    z-index: 1;
}
</style>
