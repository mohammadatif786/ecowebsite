<template>
  <AuthenticatedLayout>

    <Head title="Create Event" />
    <div class="bg-gray-100 flex justify-center items-center rounded-md">
      <!-- Create Event Form -->
      <EventForm :event="form"></EventForm>
    </div>
  </AuthenticatedLayout>
</template>
<script setup lang="ts">
  import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
  import { Head } from "@inertiajs/vue3";
  import EventForm from "./EventForm.vue";

  // Reactive form state
  const form = {
    id: "",
    title: "",
    description: "",
    category_id: "",
    type: "",
    venue: "",
    country: "",
    state: "",
    city: "",
    start_time: "",
    end_time: "",
    is_free: true,
    early_bird_price: null,
    vip_price: null,
    email: "",
    phone: "",
    image_object: null,
    featured_image: null,
    image_file: null,
  };
</script>
<style scoped></style>
