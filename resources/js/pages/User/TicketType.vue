<template>
    <AuthenticatedLayout>
        <div class="bg-gray-100 sm:px-9 lg:px-2 xl:px-9 py-12 rounded-2xl">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800"> Ticket Types </h2>
            <div class="flex flex-wrap justify-center xl:flex-nowrap gap-2">
                <div class="max-w-sm  bg-[#E6F4FA] rounded-lg border-[3px] border-blue-400 py-2 space-y-2">
                    <h2 class="text-blue-800  text-lg px-4">Early Bird General Tickets $15.00</h2>
                    <div class="flex items-center justify-between py-2 text-white bg-blue-400">
                        <div class="text-sm font-semibold px-4"> $15.00 <span class="text-xs font-normal">+ $3.46 Fees +
                                $0.00 Taxes</span>
                        </div>
                        <div class="flex items-center space-x-2 px-4">
                            <button @click="couter++"
                                class="w-6 h-6 rounded-full bg-blue-200 text-blue-700 text-lg leading-none flex items-center justify-center">−</button>
                            <span class="font-medium">0</span>
                            <button
                                class="w-6 h-6 rounded-full bg-blue-200 text-blue-700 text-lg leading-none flex items-center justify-center">+</button>
                        </div>
                    </div>
                    <p class="text-sm text-gray-700 px-4">Sales end on Jan 7, 2023</p>
                    <p class="text-xs text-gray-500 px-4">This RSVP guarantees entrance to The Shrine before 11pm.</p>
                </div>
                <div class="max-w-sm bg-[#E6F4FA] rounded-lg border-[3px] border-blue-400 py-2 space-y-2">
                    <h2 class="text-blue-800 text-lg px-4">Early Bird General Tickets $15.00</h2>
                    <div class="flex items-center text-white  justify-between py-2 bg-primary">
                        <div class="text-sm font-semibold px-4"> $15.00 <span class="text-xs  font-normal">+ $3.46 Fees
                                + $0.00 Taxes</span>
                        </div>
                        <div class="flex items-center space-x-2 px-4">
                            <button @click="couter++"
                                class="w-6 h-6 rounded-full bg-blue-200 text-blue-700 text-lg leading-none flex items-center justify-center">−</button>
                            <span class="font-medium">0</span>
                            <button
                                class="w-6 h-6 rounded-full bg-blue-200 text-blue-700 text-lg leading-none flex items-center justify-center">+</button>
                        </div>
                    </div>
                    <p class="text-sm text-gray-700 px-4">This Ticket is For General Addmission only</p>
                </div>
            </div>
            <div class="flex flex-wrap justify-center xl:flex-nowrap gap-2 mt-2">
                <div class="max-w-sm  bg-[#E6F4FA] rounded-lg border-[3px] border-blue-400 py-2 space-y-2">
                    <h2 class="text-blue-800 text-lg px-4">Early Bird General Tickets $15.00</h2>
                    <div class="flex items-center text-white justify-between py-2 bg-blue-400">
                        <div class="text-sm font-semibold px-4"> $15.00 <span class="text-xs font-normal">+ $3.46 Fees +
                                $0.00 Taxes</span>
                        </div>
                        <div class="flex items-center space-x-2 px-4">
                            <button @click="couter++"
                                class="w-6 h-6 rounded-full bg-blue-200 text-blue-700 text-lg leading-none flex items-center justify-center">−</button>
                            <span class="font-medium">0</span>
                            <button
                                class="w-6 h-6 rounded-full bg-blue-200 text-blue-700 text-lg leading-none flex items-center justify-center">+</button>
                        </div>
                    </div>
                    <p class="text-sm text-gray-700 px-4">Sales end on Jan 7, 2023</p>
                    <p class="text-xs text-gray-500 px-4">This RSVP guarantees entrance to The Shrine before 11pm.</p>
                </div>
                <div class="max-w-sm bg-[#E6F4FA] rounded-lg border-[3px] border-blue-400 py-2 space-y-2">
                    <h2 class="text-blue-800  text-lg px-4">Early Bird General Tickets $15.00</h2>
                    <div class="flex items-center text-white justify-between py-2 bg-primary">
                        <div class="text-sm font-semibold px-4"> $15.00 <span class="text-xs font-normal">+ $3.46 Fees +
                                $0.00 Taxes</span>
                        </div>
                        <div class="flex items-center px-4 space-x-2 ">
                            <button @click="couter++"
                                class="w-6 h-6 rounded-full bg-blue-200 text-blue-700 text-lg leading-none flex items-center justify-center">−</button>
                            <span class="font-medium">0</span>
                            <button
                                class="w-6 h-6 rounded-full bg-blue-200 text-blue-700 text-lg leading-none flex items-center justify-center">+</button>
                        </div>
                    </div>
                    <p class="text-sm text-gray-700 px-4">This is a VIP Seating Ticket</p>
                </div>
            </div>
            <div class="flex flex-row justify-end px-4 py-4 text-gray-500">
                <span>Total : $250.00</span>
            </div>
            <div class="px-4 mt-4 flex gap-2 justify-center lg:justify-between">
                <div>
                    <button
                        class="cursor-pointer bg-primary px-1 sm:px-2 text-xs sm:text-sm md:text-base rounded text-white">Event
                        Coupon <i class="ri-play-fill border rounded-full text-xs ms-1"></i></button>
                    <button
                        class="cursor-pointer bg-blue-500 px-1 sm:px-2 text-xs sm:text-sm md:text-base rounded text-white ms-1">Pay
                        From Wallet<i class="ri-play-fill border rounded-full text-xs ms-1"></i></button>
                </div>
                <div>
                    <button
                        class="cursor-pointer bg-primary px-1 sm:px-2 text-xs sm:text-sm md:text-base rounded text-white">Checkout</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
    import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
</script>
