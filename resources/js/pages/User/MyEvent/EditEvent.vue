<template>
    <AuthenticatedLayout>

        <Head title="Edit Event" />
        <div class=" bg-gray-100 flex justify-center items-center rounded-md">
            <!-- Create Event Form -->
            <EventForm :event="form"></EventForm>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import EventForm from './EventForm.vue';


// Reactive form state
const form = usePage().props.event;
form.image_file = null;

</script>
<style scoped></style>
