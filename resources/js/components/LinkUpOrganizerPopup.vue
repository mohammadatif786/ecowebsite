<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
            <!-- Header -->
            <div class="flex items-center justify-between border-b pb-3">
                <h2 class="text-xl font-bold text-[#379ce8]">Welcome to Link Up Event Organizer Page</h2>
                <button @click="$emit('update:modelValue', false)" class="text-gray-500 hover:text-black">✖</button>
            </div>

            <!-- Body -->
            <div class="mt-4 space-y-3 text-gray-700">
                <p>You are leaving the main Link Up page and entering the Event Organizer space. Here, you'll be able to:</p>

                <ul class="grid grid-cols-2 gap-3 text-sm">
                    <li class="flex items-center space-x-2">
                        <span>📅</span>
                        <span>Create and manage events</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <span>👤</span>
                        <span>Build your organizer profile</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <span>🎟️</span>
                        <span>Set up ticketing and payouts</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <span>📢</span>
                        <span>Promote events and share on social media</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <span>📊</span>
                        <span>Track reports and reviews</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <span>📋</span>
                        <span>Access the organizer dashboard</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <span>📷</span>
                        <span>Create scanners for event check-in</span>
                    </li>
                </ul>
            </div>

            <!-- Footer -->
            <div class="mt-6 flex justify-end gap-3">
                <button @click="$emit('cancel', false)" class="rounded-lg bg-yellow-400 px-4 py-2 font-semibold hover:bg-yellow-500">
                    Cancel / Stay Here
                </button>
                <button @click="$emit('continue')" class="rounded-lg bg-[#379ce8] px-4 py-2 font-semibold text-white hover:bg-[#2d7fc1]">
                    Continue to Organizer Page
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps({
    modelValue: { type: Boolean, default: false },
});
defineEmits(['update:modelValue', 'continue', 'cancel']);
</script>
