<template>
    <AppLayout>
        <div class="flex items-center justify-center min-h-screen bg-gray-50">
            <div class="max-w-lg w-full bg-white shadow-lg rounded-2xl p-8 text-center">
                <!-- Icon -->
                <div class="flex justify-center mb-4">
                    <svg class="w-16 h-16 text-red-500" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464
                            0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <!-- Title -->
                <h1 class="text-2xl font-bold text-gray-800 mb-3">
                    Complete Your Organizer Profile
                </h1>

                <!-- Description -->
                <p class="text-gray-600 mb-6">
                    You need to complete your organizer profile before accessing these features.
                </p>

            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/organizer/AppLayout.vue';
</script>
