<template>
    <AuthenticatedLayout>

        <Head title="Create Event Ticket" />
        <div class=" bg-gray-100 flex justify-center items-center rounded-md">
            <TicketForm :events="events" :ticket="ticket" />
        </div>
    </AuthenticatedLayout>
</template>
<script setup lang="ts">
    import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
    import { Head, useForm, usePage } from '@inertiajs/vue3';
    import TicketForm from './TicketForm.vue';

    const { ticket, events } = defineProps<{
        ticket: any,
        events: any,
    }>()
    console.log("Ticket Data:", ticket);

</script>
<style scoped></style>
