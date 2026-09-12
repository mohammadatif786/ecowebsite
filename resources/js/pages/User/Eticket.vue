<template>
    <!-- E-Ticket Card -->
    <AuthenticatedLayout>
        <div class="bg-gray-100 rounded-lg shadow-md p-5 w-full">
            <h1 class="text-2xl font-bold mb-3">E-Ticket</h1>
            <!-- Event Details -->
            <div class="mb-3">
                <p class="text-[18px]">Event</p>
                <p class="text-gray-600 ">eventname</p>
            </div>
            <!-- Date and Hour -->
            <hr class="text-gray-300">
            <div class="mb-3">
                <p class="text-[18px]">Date and Hour</p>
                <p class="text-gray-600 ">date hour</p>
            </div>
            <hr class="text-gray-300">
            <!-- Event Location -->
            <div class="mb-3">
                <p class="text-[18px]">Event Location</p>
                <p class="text-gray-600 ">laocation</p>
            </div>
            <hr class="text-gray-300">
            <!-- Personal Details -->
            <p class="text-[18px]">Singapoor</p>
            <div class="mb-3 flex justify-between">
                <p class="text-gray-600 ">full name</p>
                <p class="text-gray-600 ">thisisnnamed</p>
            </div>
            <div class="mb-3 flex justify-between">
                <p class="text-gray-600 ">phone</p>
                <p class="text-gray-600 ">867857889</p>
            </div>
            <div class="mb-3 flex justify-between">
                <p class="text-gray-600 ">email</p>
                <p class="text-gray-600 ">email@gmail.com</p>
            </div>
            <div class="mb-3 flex justify-between">
                <p class="text-gray-600 ">Seats</p>
                <p class="text-gray-600 ">1x Early Bird</p>
            </div>
            <div class="mb-3 flex justify-between">
                <p class="text-gray-600 ">Tax</p>
                <p class="text-gray-600 ">$0</p>
            </div>
            <div class="mb-3 flex justify-between">
                <p class="text-gray-600 ">status</p>
                <p class="text-gray-600 ">paid</p>
            </div>
            <!-- Download Button -->
            <button class="bg-primary text-black font-semibold py-2 px-4 rounded w-full  uppercase "> Download E-Ticket
            </button>
        </div>
    </AuthenticatedLayout>
</template>
<script setup lang="ts">
    import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
</script>
