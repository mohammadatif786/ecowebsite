<template>
    <AuthenticatedLayout>
        <div class="bg-gray-100 p-6 mb-8 rounded-2xl">
            <h1 class="text-3xl font-bold mb-6">Upcoming</h1>
            <div class="flex flex-wrap gap-6 justify-start">
                <!-- Loop through events 6 times -->
                <div v-for="n in 6" :key="n" class="bg-white rounded-lg shadow-md overflow-hidden w-56 ">
                    <!-- Event Image -->
                    <div class="relative">
                        <img src="/assets/images/event.png" alt="Event Image" class="w-full h-32 object-cover">
                        <!-- Heart Icon -->
                        <button class="absolute top-2 right-2 bg-white rounded-full p-1">
                            <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                </path>
                            </svg>
                        </button>
                    </div>
                    <!-- Event Details -->
                    <div class="p-4">
                        <div class="flex justify-between items-center">
                            <span class="">$100</span>
                            <span class="text-gray-600">Feb 2024</span>
                        </div>
                        <h2 class="">Event Name</h2>
                        <p class="text-gray-600 py-1 text-sm truncate">Event description lorem ipsum dolor...</p>
                        <div class="flex justify-between  ">
                            <button
                                class="bg-yellow-400 text-[9px] text-white  px-3 rounded-full hover:bg-yellow-500">Cancel
                                Booking</button>
                            <button class="bg-blue-400 text-[9px] text-white px-3  rounded-full hover:bg-blue-500">View
                                E-Ticket</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-gray-100 p-6 my-8 rounded-2xl">
            <h1 class="text-3xl font-bold mb-6">Completed</h1>
            <div class="flex flex-wrap gap-6 justify-start">
                <!-- Loop through events 6 times -->
                <div v-for="n in 6" :key="n" class="bg-white rounded-lg shadow-md overflow-hidden w-56 ">
                    <!-- Event Image -->
                    <div class="relative">
                        <img src="/assets/images/event.png" alt="Event Image" class="w-full h-32 object-cover">
                        <!-- Heart Icon -->
                        <button class="absolute top-2 right-2 bg-white rounded-full p-1">
                            <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                </path>
                            </svg>
                        </button>
                    </div>
                    <!-- Event Details -->
                    <div class="p-4">
                        <div class="flex justify-between items-center">
                            <span class="">$100</span>
                            <span class="text-gray-600">Feb 2024</span>
                        </div>
                        <h2 class="">Event Name</h2>
                        <p class="text-gray-600 py-1 text-sm truncate">Event description lorem ipsum dolor...</p>
                        <div class="flex justify-between  ">
                            <button
                                class="bg-yellow-400 text-[9px] text-white  px-3 rounded-full hover:bg-yellow-500">Cancel
                                Booking</button>
                            <button class="bg-blue-400 text-[9px] text-white px-3  rounded-full hover:bg-blue-500">View
                                E-Ticket</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-gray-100 p-6 mb-8 rounded-2xl">
            <h1 class="text-3xl font-bold mb-6">Cancelled</h1>
            <div class="flex flex-wrap gap-6 justify-start">
                <!-- Loop through events 6 times -->
                <div v-for="n in 6" :key="n" class="bg-white rounded-lg shadow-md overflow-hidden w-56 ">
                    <!-- Event Image -->
                    <div class="relative">
                        <img src="/assets/images/event.png" alt="Event Image" class="w-full h-32 object-cover">
                        <!-- Heart Icon -->
                        <button class="absolute top-2 right-2 bg-white rounded-full p-1">
                            <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                </path>
                            </svg>
                        </button>
                    </div>
                    <!-- Event Details -->
                    <div class="p-4">
                        <div class="flex justify-between items-center">
                            <span class="">$100</span>
                            <span class="text-gray-600">Feb 2024</span>
                        </div>
                        <h2 class="">Event Name</h2>
                        <p class="text-gray-600 py-1 text-sm truncate">Event description lorem ipsum dolor...</p>
                        <div class="flex justify-between  ">
                            <button
                                class="bg-yellow-400 text-[9px] text-white  px-3 rounded-full hover:bg-yellow-500">Cancel
                                Booking</button>
                            <button class="bg-blue-400 text-[9px] text-white px-3  rounded-full hover:bg-blue-500">View
                                E-Ticket</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup lang="ts">
    import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

</script>
