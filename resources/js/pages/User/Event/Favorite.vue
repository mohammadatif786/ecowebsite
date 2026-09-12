<script setup lang="ts">
import { LinkupEvent } from "@/client/models/LinkupEvent";
import EventCard from "@/components/front/EventCard.vue";
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import { usePage } from "@inertiajs/vue3";

const { events } = usePage<{
    events: LinkupEvent[];
}>().props;
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Favorite Events" />

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <EventCard v-for="event in events" :event="event" :key="event.id" />
        </div>
    </AuthenticatedLayout>
</template>
